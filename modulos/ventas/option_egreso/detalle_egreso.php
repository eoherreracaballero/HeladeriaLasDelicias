<?php
session_start();

// Cargar encabezado
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Validar ID de egreso
$idEgreso = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idEgreso <= 0) {
    die("ID de egreso inválido.");
}

// Consulta cabecera del egreso con cliente Y CMV (Costo de Mercancía Vendida)
$sqlEgreso = "SELECT ep.Id_Egreso, ep.Tipo_Egreso, ep.Fecha_Egreso, 
                     ep.Subtotal_Egreso, ep.IVA_Egreso, ep.Total_Egreso, 
                     ep.Costo_Mercancia_Vendida, c.Nombre_Cliente
             FROM egreso_producto ep
             JOIN cliente c ON ep.Id_cliente = c.Id_cliente
             WHERE ep.Id_Egreso = ?";
$stmtEgreso = $conexion->prepare($sqlEgreso);
$stmtEgreso->bind_param("i", $idEgreso);
$stmtEgreso->execute();
$resultEgreso = $stmtEgreso->get_result();
$egreso = $resultEgreso->fetch_assoc();
$stmtEgreso->close();

if (!$egreso) {
    die("Egreso no encontrado.");
}

// Cálculo de Utilidad Bruta Total
$cmvTotal = floatval($egreso['Costo_Mercancia_Vendida'] ?? 0);
$subtotalVenta = floatval($egreso['Subtotal_Egreso'] ?? 0);
$utilidadBrutaTotal = $subtotalVenta - $cmvTotal;


// Consulta detalle del egreso con producto, bodega y COSTO UNITARIO
$sqlDetalle = "SELECT d.ID_Producto, d.Cantidad, d.PVP, d.Costo_Unitario, p.Nombre_Producto, b.Nombre_Bodega
             FROM detalle_egreso d
             JOIN producto p ON d.ID_Producto = p.ID_Producto
             JOIN bodega b ON d.ID_Bodega = b.Id_Bodega
             WHERE d.Id_Egreso = ?";
$stmtDetalle = $conexion->prepare($sqlDetalle);
$stmtDetalle->bind_param("i", $idEgreso);
$stmtDetalle->execute();
$resultDetalle = $stmtDetalle->get_result();
$stmtDetalle->close();
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
<div class="container-fluid py-4">

    <!-- Cabecera del Egreso -->
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Detalle de Egreso #<?= $idEgreso ?></h5>
            <a href="../facturacion.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Tipo de Egreso:</strong> <?= htmlspecialchars($egreso['Tipo_Egreso']) ?></div>
                <div class="col-md-4"><strong>Cliente:</strong> <?= htmlspecialchars($egreso['Nombre_Cliente']) ?></div>
                <div class="col-md-4"><strong>Fecha:</strong> <?= htmlspecialchars($egreso['Fecha_Egreso']) ?></div>
            </div>

            <hr>

            <!-- Resumen Financiero -->
            <div class="row g-3">
                <!-- Totales de Venta -->
                <div class="col-md-6 text-start border-end">
                    <h6 class="fw-bold text-danger">Totales de Venta (Ingresos)</h6>
                    <p><span class="fw-bold">Subtotal:</span> <span class="badge bg-secondary">$<?= number_format($subtotalVenta, 2) ?></span></p>
                    <p><span class="fw-bold">IVA (19%):</span> <span class="badge bg-warning text-dark">$<?= number_format($egreso['IVA_Egreso'], 2) ?></span></p>
                    <p><span class="fw-bold h5">Total Venta:</span> <span class="badge bg-danger h5">$<?= number_format($egreso['Total_Egreso'], 2) ?></span></p>
                </div>

                <!-- Totales de Costo y Utilidad -->
                <div class="col-md-6 text-start">
                    <h6 class="fw-bold text-success">Totales Contables (Costos)</h6>
                    <p><span class="fw-bold">Costo Merc. Vendida (CMV):</span> <span class="badge bg-dark">$<?= number_format($cmvTotal, 2) ?></span></p>
                    <p><span class="fw-bold h5">Utilidad Bruta:</span> <span class="badge bg-success h5">$<?= number_format($utilidadBrutaTotal, 2) ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Productos -->
    <div class="card shadow-lg border-0 rounded-3 mt-4">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="fas fa-boxes me-2"></i> Productos Egresados (Detalle por Ítem)</h6>
        </div>
        <div class="card-body">
            <?php if ($resultDetalle->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-danger">
                        <tr>
                            <th>Producto</th>
                            <th>Bodega</th>
                            <th>Cantidad</th>
                            <th>PVP Venta</th>
                            <th class="bg-info text-white">Costo Unitario (CPP)</th>
                            <th>Total Venta</th>
                            <th class="bg-success text-white">Utilidad Bruta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($fila = $resultDetalle->fetch_assoc()): 
                            $costoTotalItem = $fila['Cantidad'] * $fila['Costo_Unitario'];
                            $ventaTotalItem = $fila['Cantidad'] * $fila['PVP'];
                            $utilidadItem = $ventaTotalItem - $costoTotalItem;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['Nombre_Producto']) ?></td>
                            <td><?= htmlspecialchars($fila['Nombre_Bodega']) ?></td>
                            <td><?= number_format($fila['Cantidad'], 2) ?></td>
                            <td>$<?= number_format($fila['PVP'], 2) ?></td>
                            <td class="bg-light">$<?= number_format($fila['Costo_Unitario'], 2) ?></td>
                            <td class="fw-bold text-danger">$<?= number_format($ventaTotalItem, 2) ?></td>
                            <td class="fw-bold text-success">$<?= number_format($utilidadItem, 2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-circle"></i> No se encontraron detalles para este egreso.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<?php
$conexion->close();
?>