<?php
session_start();

// Cargar encabezado
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");


$idIngreso = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idIngreso <= 0) {
    die("ID de ingreso inválido.");
}

// Consulta ingreso con proveedor y totales
$sqlIngreso = "SELECT i.ID_Ingreso, i.Tipo_Ingreso, i.Fecha_Ingreso, i.Subtotal, i.Iva, i.Total, p.Nombre_Proveedor
               FROM ingreso_producto i
               JOIN proveedor p ON i.ID_Proveedor = p.ID_Proveedor
               WHERE i.ID_Ingreso = ?";
$stmtIngreso = $conexion->prepare($sqlIngreso);
$stmtIngreso->bind_param("i", $idIngreso);
$stmtIngreso->execute(); 
$resultIngreso = $stmtIngreso->get_result();
$ingreso = $resultIngreso->fetch_assoc();
$stmtIngreso->close();

if (!$ingreso) {
    die("Ingreso no encontrado.");
}

// Consulta detalle ingreso con producto y bodega
$sqlDetalle = "SELECT d.Cantidad, d.Costo_Unitario, p.Nombre_Producto, b.Nombre_Bodega
               FROM detalle_ingreso d
               JOIN producto p ON d.ID_Producto = p.ID_Producto
               JOIN bodega b ON d.ID_Bodega = b.ID_Bodega
               WHERE d.ID_Ingreso = ?";
$stmtDetalle = $conexion->prepare($sqlDetalle);
$stmtDetalle->bind_param("i", $idIngreso);
$stmtDetalle->execute();
$resultDetalle = $stmtDetalle->get_result();
$stmtDetalle->close();

?>

<!-- Boton de regreso al menu anterior -->

<main class="p-4 flex-grow-1 fade-in" id="contenido">
<div class="container-fluid py-4">
    <!-- Card Encabezado -->
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Detalle de Ingreso #<?= $idIngreso ?></h5>
            <a href="../anulaciones.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Volver Anulaciones
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Tipo de Ingreso:</strong> <?= htmlspecialchars($ingreso['Tipo_Ingreso'] ?? '') ?></div>
                <div class="col-md-4"><strong>Proveedor:</strong> <?= htmlspecialchars($ingreso['Nombre_Proveedor'] ?? '') ?></div>
                <div class="col-md-4"><strong>Fecha:</strong> <?= htmlspecialchars($ingreso['Fecha_Ingreso'] ?? '') ?></div>
            </div>

            <hr>

            <div class="row g-3 text-end">
                <div class="col-md-4 offset-md-8">
                    <p><span class="fw-bold">Subtotal:</span> <span class="badge bg-secondary">$<?= number_format($ingreso['Subtotal'] ?? 0, 2) ?></span></p>
                    <p><span class="fw-bold">IVA (19%):</span> <span class="badge bg-warning text-dark">$<?= number_format($ingreso['Iva'] ?? 0, 2) ?></span></p>
                    <p><span class="fw-bold">Total:</span> <span class="badge bg-success">$<?= number_format($ingreso['Total'] ?? 0, 2) ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="card shadow-lg border-0 rounded-3 mt-4">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="fas fa-boxes me-2"></i> Productos Ingresados</h6>
        </div>
        <div class="card-body">
            <?php if ($resultDetalle->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>Producto</th>
                            <th>Bodega</th>
                            <th>Cantidad</th>
                            <th>Costo Unitario</th>
                            <th>Costo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultDetalle->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['Nombre_Producto']) ?></td>
                            <td><?= htmlspecialchars($fila['Nombre_Bodega']) ?></td>
                            <td><?= number_format($fila['Cantidad'], 2) ?></td>
                            <td>$<?= number_format($fila['Costo_Unitario'], 2) ?></td>
                            <td class="fw-bold text-success">$<?= number_format($fila['Cantidad'] * $fila['Costo_Unitario'], 2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-circle"></i> No se encontraron detalles para este ingreso.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conexion->close();
?>
