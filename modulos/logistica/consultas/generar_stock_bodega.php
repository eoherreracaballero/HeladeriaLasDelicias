<?php
session_start();
require_once __DIR__ . "/../../../public/html/encabezado.php";
include(__DIR__ . "/../../../app/db/conexion.php");
require_once __DIR__ . "/../../../public/html/tablas.php";

// Consulta ajustada: Eliminamos el JOIN con 'bodegas' y usamos el ID directamente
// También puedes cambiar 'ID_Bodega' por el nombre real si tienes la tabla (ej: 'sucursales')
$sql = "SELECT i.ID_Bodega, p.Nombre_Producto, i.Stock, i.Costo_promedio, 
               (i.Stock * i.Costo_promedio) as Subtotal
        FROM inventario i
        INNER JOIN producto p ON i.ID_Producto = p.ID_Producto
        ORDER BY i.ID_Bodega ASC, p.Nombre_Producto ASC";

$resultado = $conexion->query($sql);
?>

<main class="container-fluid p-4 fade-in">
    <div class="mb-4 d-flex justify-content-between">
        <div>
            <h2 class="text-primary fw-bold"><i class="fas fa-warehouse me-2"></i>Inventario por Bodega</h2>
            <p class="text-muted">Consolidado de existencias basado en los registros de inventario.</p>
        </div>
        <div>
            <a href="../reportes.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Volver</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Cód. Bodega</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Costo Promedio</th>
                    <th>Inversión Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $gran_total = 0;
                if ($resultado && $resultado->num_rows > 0):
                    while($row = $resultado->fetch_assoc()): 
                        $gran_total += $row['Subtotal'];
                ?>
                <tr>
                    <td class="fw-bold">BODEGA #<?= $row['ID_Bodega'] ?></td>
                    <td><?= $row['Nombre_Producto'] ?></td>
                    <td><?= number_format($row['Stock'], 2) ?></td>
                    <td>$<?= number_format($row['Costo_promedio'], 2) ?></td>
                    <td class="fw-bold">$<?= number_format($row['Subtotal'], 2) ?></td>
                </tr>
                <?php 
                    endwhile; 
                else:
                ?>
                <tr>
                    <td colspan="5" class="text-center p-4 text-muted">No se encontraron registros de inventario.</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="4" class="text-end fw-bold">VALOR TOTAL DEL INVENTARIO:</td>
                    <td class="fw-bold text-primary fs-5">$<?= number_format($gran_total, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</main>

<?php require_once __DIR__ . "/../../../public/html/pie_de_pagina.php"; ?>