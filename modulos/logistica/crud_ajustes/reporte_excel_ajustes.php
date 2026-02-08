<?php
include(__DIR__ . "/../../../app/db/conexion.php");

// Capturar fechas de la URL
$fecha_desde = $_GET['desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['hasta'] ?? date('Y-m-d');

// Configuración de cabeceras para descargar Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="Reporte_Ajustes_Inventario.xls"');

// Consulta para obtener los datos
$sql = "SELECT n.id_nota, n.fecha_nota, p.Nombre_Producto, b.Nombre_Bodega, 
               k.Tipo_Movimiento, k.Cantidad_Entrada, k.Cantidad_Salida, k.Costo_Entrada, k.Costo_Salida, m.Motivo
        FROM nota_ajuste n
        INNER JOIN movimiento_kardex k ON n.id_nota = k.ID_Documento AND k.Tipo_Documento = 'NOTA_AJUSTE'
        INNER JOIN producto p ON k.ID_Producto = p.ID_Producto
        INNER JOIN bodega b ON k.ID_Bodega = b.Id_Bodega
        LEFT JOIN movimiento_inventario m ON k.ID_Producto = m.ID_Producto 
             AND k.Fecha_Movimiento = m.Fecha_Movimiento
        WHERE n.fecha_nota BETWEEN '$fecha_desde' AND '$fecha_hasta'
        ORDER BY n.id_nota DESC";

$res = $conexion->query($sql);
?>

<table border="1">
    <tr style="background-color: #0d6efd; color: #ffffff; font-weight: bold;">
        <th>Folio</th>
        <th>Fecha</th>
        <th>Producto</th>
        <th>Bodega</th>
        <th>Tipo</th>
        <th>Cantidad</th>
        <th>Costo Unit.</th>
        <th>Total Valorizado</th>
        <th>Motivo</th>
    </tr>

    <?php while ($row = $res->fetch_assoc()): 
        $es_entrada = ($row['Cantidad_Entrada'] > 0);
        $cantidad = $es_entrada ? $row['Cantidad_Entrada'] : $row['Cantidad_Salida'];
        $costo = $es_entrada ? $row['Costo_Entrada'] : $row['Costo_Salida'];
        $total = $cantidad * $costo;
    ?>
        <tr>
            <td>#<?php echo $row['id_nota']; ?></td>
            <td><?php echo $row['fecha_nota']; ?></td>
            <td><?php echo utf8_decode($row['Nombre_Producto']); ?></td>
            <td><?php echo utf8_decode($row['Nombre_Bodega']); ?></td>
            <td style="color: <?php echo $es_entrada ? 'green' : 'red'; ?>;">
                <?php echo $es_entrada ? 'ENTRADA' : 'SALIDA'; ?>
            </td>
            <td><?php echo number_format($cantidad, 2); ?></td>
            <td>$<?php echo number_format($costo, 2); ?></td>
            <td>$<?php echo number_format($total, 2); ?></td>
            <td><?php echo utf8_decode($row['Motivo']); ?></td>
        </tr>
    <?php endwhile; ?>
</table>