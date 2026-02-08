<?php
session_start();
require_once __DIR__ . "/../../../public/html/encabezado.php";
include(__DIR__ . "/../../../app/db/conexion.php");
require_once __DIR__ . "/../../../public/html/tablas.php";

// Consulta basada en tus capturas: i.Stock vs p.Stock_Minimo
$sql = "SELECT p.Nombre_Producto, p.Marca, p.Categoria, i.Stock, p.Stock_Minimo, 
               (p.Stock_Minimo - i.Stock) as Faltante
        FROM inventario i
        INNER JOIN producto p ON i.ID_Producto = p.ID_Producto
        WHERE i.Stock <= p.Stock_Minimo
        ORDER BY Faltante DESC";

$resultado = $conexion->query($sql);
?>

<main class="container-fluid p-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Alertas de Reposición</h2>
            <p class="text-muted">Productos que han alcanzado o superado el límite mínimo de seguridad.</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-dark"><i class="fas fa-print me-2"></i>Imprimir PDF</button>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Marca / Categoría</th>
                    <th>Stock Actual</th>
                    <th>Mínimo Requerido</th>
                    <th>Estado</th>
                    <th>Sugerencia de Compra</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td class="fw-bold"><?= $row['Nombre_Producto'] ?></td>
                    <td><?= $row['Marca'] ?> / <?= $row['Categoria'] ?></td>
                    <td class="text-danger fw-bold"><?= number_format($row['Stock'], 0) ?></td>
                    <td><?= number_format($row['Stock_Minimo'], 0) ?></td>
                    <td>
                        <span class="badge bg-danger">BAJO STOCK</span>
                    </td>
                    <td class="fw-bold text-primary"><?= number_format($row['Faltante'], 0) ?> unidades</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . "/../../../public/html/pie_de_pagina.php"; ?>