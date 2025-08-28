<?php
session_start();

// Cargar encabezado
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Consulta los ingresos con datos básicos y nombre del proveedor
$sql = "SELECT ip.ID_Ingreso, ip.tipo_ingreso, ip.fecha_ingreso, ip.total, p.Nombre_Proveedor
        FROM ingreso_producto ip
        LEFT JOIN proveedor p ON ip.ID_proveedor = p.ID_Proveedor
        ORDER BY ip.fecha_ingreso DESC";

$resultado = $conexion->query($sql);
?>

<!-- Boton de regreso al menu anterior -->
<main class="p-4 flex-grow-1 fade-in" id="contenido">
        <div class="mb-3 text-end">
            <a href="../ingresos.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Compras y Ajustes
            </a>
        </div>
        
<div class="container-fluid py-4">
    <h2 class="mb-4">Consulta de Ingresos</h2>

    <?php if ($resultado->num_rows > 0): ?>
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID Ingreso</th>
                    <th>Tipo de Ingreso</th>
                    <th>Fecha Ingreso</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody> 
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td><?= $fila['ID_Ingreso'] ?></td>
                        <td><?= htmlspecialchars($fila['tipo_ingreso']) ?></td>
                        <td><?= $fila['fecha_ingreso'] ?></td>
                        <td><?= htmlspecialchars($fila['Nombre_Proveedor']) ?></td>
                        <td>$ <?= number_format($fila['total'], 2) ?></td>
                        <td><a href="detalle_ingreso.php?id=<?= $fila['ID_Ingreso'] ?>" class="btn btn-info btn-sm">Ver Detalle</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No se encontraron ingresos registrados.</div>
    <?php endif; ?>
</div>

<?php
$conexion->close();
?>
