<?php
session_start();

// Cargar encabezado
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Consulta los egresos con datos básicos y nombre del cliente
$sql = "SELECT ip.Id_Egreso, ip.Tipo_Egreso, ip.Fecha_Egreso, ip.Subtotal_Egreso, 
               ip.IVA_Egreso, ip.Total_Egreso, p.Nombre_Cliente
        FROM egreso_producto ip
        LEFT JOIN cliente p ON ip.Id_cliente = p.Id_cliente
        ORDER BY ip.Id_Egreso DESC";

$resultado = $conexion->query($sql);
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <div class="mb-3 text-end">
        <a href="../facturacion.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Facturación y Egresos
        </a>
    </div>

    <h2 class="text-primary mb-4">
        <i class="fas fa-list"></i> Consulta de Egresos
    </h2>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID Egreso</th>
                    <th>Tipo de Egreso</th>
                    <th>Fecha Egreso</th>
                    <th>Cliente</th>
                    <th>Subtotal</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td><?= htmlspecialchars($fila['Id_Egreso']) ?></td>
                        <td><?= htmlspecialchars($fila['Tipo_Egreso']) ?></td>
                        <td><?= htmlspecialchars($fila['Fecha_Egreso']) ?></td>
                        <td><?= htmlspecialchars($fila['Nombre_Cliente']) ?></td>
                        <td>$ <?= number_format($fila['Subtotal_Egreso'], 2) ?></td>
                        <td>$ <?= number_format($fila['IVA_Egreso'], 2) ?></td>
                        <td>$ <?= number_format($fila['Total_Egreso'], 2) ?></td>
                        <td>
                            <a href="detalle_egreso.php?id=<?= urlencode($fila['Id_Egreso']) ?>" 
                               class="btn btn-info btn-sm">
                               Ver Detalle
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No se encontraron egresos registrados.</div>
    <?php endif; ?>
</main>

<?php
$conexion->close();
?>
