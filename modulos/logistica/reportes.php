<?php
session_start();
require_once __DIR__ . "/../../public/html/encabezado.php";
include(__DIR__ . "/../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php";

// 1. Stock Total (Suma de la tabla inventario)
$res_total = $conexion->query("SELECT SUM(Stock) as total FROM inventario");
$total_articulos = ($res_total) ? $res_total->fetch_assoc()['total'] : 0;

// 2. Valorización (Stock de inventario * Costo_promedio de inventario)
// Según tu imagen, ambos datos están en la misma tabla 'inventario'
$res_valor = $conexion->query("SELECT SUM(Stock * Costo_promedio) as valor FROM inventario");
$valor_inventario = ($res_valor) ? $res_valor->fetch_assoc()['valor'] : 0;

// 3. Productos Críticos (Comparando stock de inventario vs Stock_Minimo de producto)
$sql_criticos = "SELECT COUNT(*) as criticos 
                 FROM inventario i 
                 INNER JOIN producto p ON i.ID_Producto = p.ID_Producto 
                 WHERE i.Stock <= p.Stock_Minimo";
$res_criticos = $conexion->query($sql_criticos);
$productos_criticos = ($res_criticos) ? $res_criticos->fetch_assoc()['criticos'] : 0;
?>

<main class="container-fluid p-4 fade-in">
    <div class="mb-4">
        <h2 class="text-primary fw-bold"><i class="fas fa-chart-line me-2"></i>Panel de Reportes</h2>
        <hr>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>STOCK TOTAL</h6>
                    <h2 class="fw-bold"><?= number_format($total_articulos, 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>VALORIZACIÓN</h6>
                    <h2 class="fw-bold">$<?= number_format($valor_inventario, 2) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>STOCK CRÍTICO</h6>
                    <h2 class="fw-bold"><?= $productos_criticos ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5 class="text-primary fw-bold mb-3">Generar Informes Detallados</h5>
            <div class="d-grid gap-2 d-md-block text-center">
                <a href="consultas/generar_stock_bodega.php" class="btn btn-outline-primary px-4 me-md-2">Inventario por Bodega</a>
                <a href="consultas/generar_stock_critico.php" class="btn btn-outline-danger px-4">Ver Alertas de Compra</a>
            </div>
        </div>
    </div>
</main>

<?php 
// 4. Pie de página - Asegúrate de que el archivo existe
$ruta_pie = __DIR__ . "/../../public/html/pie_de_pagina.php";
if (file_exists($ruta_pie)) {
    require_once $ruta_pie;
}
?>