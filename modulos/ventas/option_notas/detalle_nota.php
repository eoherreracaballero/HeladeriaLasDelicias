<?php
// RUTA DE CONEXIÓN: Tres niveles atrás para llegar a /app/db
require_once __DIR__ . "/../../../public/html/encabezado.php";
include(__DIR__ . "/../../../app/db/conexion.php");

$notaId = (int) ($_GET['id'] ?? 0);

if ($notaId <= 0) {
    // Redirección relativa: consulta_notas.php está en el mismo directorio
    header("Location: consulta_notas.php?error=" . urlencode("ID de nota inválido."));
    exit();
}

// 1. Obtener Cabecera
$sqlCabecera = "SELECT 
                    ep.Num_Documento, ep.Fecha_Egreso, ep.Subtotal_Egreso, ep.IVA_Egreso, ep.Total_Egreso, ep.Tipo_Egreso, ep.Motivo,
                    ep.Factura_Referencia_Id,
                    c.Nombre_Cliente
                FROM egreso_producto ep
                JOIN cliente c ON ep.Id_cliente = c.Id_cliente
                WHERE ep.ID_Egreso = ? 
                AND ep.Tipo_Egreso IN ('Nota de Crédito', 'Nota de Débito')";

$stmtCabecera = $conexion->prepare($sqlCabecera);
if ($stmtCabecera === false) {
    die("Error de preparación de cabecera: " . $conexion->error);
}
$stmtCabecera->bind_param("i", $notaId);
$stmtCabecera->execute();
$resultCabecera = $stmtCabecera->get_result();
$cabecera = $resultCabecera->fetch_assoc();
$stmtCabecera->close();

if (!$cabecera) {
    header("Location: consulta_notas.php?error=" . urlencode("Nota no encontrada."));
    exit();
}

// 2. Obtener Detalle de Productos
// NOTA: Se usa 'Subtotal_Egreso' para el detalle, alineado con tu corrección anterior.
$sqlDetalle = "SELECT 
                  de.Cantidad, de.PVP, de.Subtotal_Egreso as Subtotal_Item, 
                  p.Nombre_Producto, b.Nombre_Bodega
               FROM detalle_egreso de
               JOIN producto p ON de.ID_producto = p.ID_Producto
               JOIN bodega b ON de.ID_Bodega = b.ID_Bodega
               WHERE de.Id_Egreso = ?";

$stmtDetalle = $conexion->prepare($sqlDetalle);
if ($stmtDetalle === false) {
    die("Error de preparación de detalle: " . $conexion->error);
}
$stmtDetalle->bind_param("i", $notaId);
$stmtDetalle->execute();
$resultDetalle = $stmtDetalle->get_result();
$detalle = $resultDetalle->fetch_all(MYSQLI_ASSOC);
$stmtDetalle->close();
$conexion->close();


// Asumo que la columna de Motivo en egreso_producto se llama 'Motivo' 
// y el subtotal de detalle se llama 'Subtotal_Egreso'

?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-danger mb-4"><i class="fas fa-search me-2"></i> Detalle de <?= htmlspecialchars($cabecera['Tipo_Egreso'] ?? 'Nota') ?></h2>
    
    <div class="row mb-4 bg-light p-3 rounded shadow-sm">
        <div class="col-md-6">
            <p><strong>Tipo de Documento:</strong> <?= htmlspecialchars($cabecera['Tipo_Egreso']) ?></p>
            <p><strong>Número:</strong> <span class="badge bg-danger fs-5"><?= htmlspecialchars($cabecera['Num_Documento']) ?></span></p>
            <p><strong>Cliente:</strong> <?= htmlspecialchars($cabecera['Nombre_Cliente']) ?></p>
            <p><strong>Fecha de Emisión:</strong> <?= htmlspecialchars($cabecera['Fecha_Egreso']) ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Factura Afectada:</strong> #<?= htmlspecialchars($cabecera['Factura_Referencia_Id']) ?></p>
            <p><strong>Motivo:</strong> <?= nl2br(htmlspecialchars($cabecera['Motivo'] ?? 'N/A')) ?></p>
        </div>
    </div>
    
    <h5 class="mt-4 mb-3">Productos Afectados</h5>
    <table class="table table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>Producto</th>
                <th>Bodega</th>
                <th>Cantidad</th>
                <th>PVP Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['Nombre_Producto']) ?></td>
                <td><?= htmlspecialchars($item['Nombre_Bodega']) ?></td>
                <td><?= number_format($item['Cantidad'], 2) ?></td>
                <td><?= number_format($item['PVP'], 2) ?></td>
                <td><?= number_format($item['Subtotal_Item'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="text-end">
        <p><strong>Subtotal (Neto):</strong> <?= number_format($cabecera['Subtotal_Egreso'], 2) ?></p>
        <p><strong>IVA (19%):</strong> <?= number_format($cabecera['IVA_Egreso'], 2) ?></p>
        <h4 class="text-danger"><strong>Total Afectado:</strong> <?= number_format($cabecera['Total_Egreso'], 2) ?></h4>
    </div>

    <div class="mt-4">
        <a href="consulta_notas.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
    </div>
</main>

<?php 
