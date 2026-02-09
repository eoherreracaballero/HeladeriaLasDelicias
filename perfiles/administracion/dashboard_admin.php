<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Conexión a Base de Datos
include(__DIR__ . "/../../app/db/conexion.php");

// 2. Inicialización segura de variables
$total_usuarios = 0;
$total_criticos = 0;
$valor_total = 0;

// --- MÉTRICA 1: USUARIOS (Prueba varios nombres comunes de tabla) ---
$tablas_usuarios = ['usuario', 'usuarios', 'users', 'usuarios_sistema'];
foreach ($tablas_usuarios as $tabla) {
    $res = @$conexion->query("SELECT COUNT(*) as total FROM $tabla");
    if ($res) {
        $total_usuarios = $res->fetch_assoc()['total'];
        break; // Detenerse cuando encuentre la tabla correcta
    }
}

// --- MÉTRICA 2: STOCK CRÍTICO ---
$sql_c = "SELECT COUNT(*) as total FROM inventario i 
          INNER JOIN producto p ON i.ID_Producto = p.ID_Producto 
          WHERE i.Stock <= p.Stock_Minimo";
$res_c = @$conexion->query($sql_c);
if ($res_c) $total_criticos = $res_c->fetch_assoc()['total'];

// --- MÉTRICA 3: VALORIZACIÓN TOTAL ---
$res_v = @$conexion->query("SELECT SUM(Stock * Costo_promedio) as total FROM inventario");
if ($res_v) $valor_total = $res_v->fetch_assoc()['total'] ?? 0;

// --- DATOS PARA LA GRÁFICA ---
$nombres = []; $stocks = []; $minimos = [];
$sql_grafica = "SELECT p.Nombre_Producto, i.Stock, p.Stock_Minimo 
                FROM inventario i 
                INNER JOIN producto p ON i.ID_Producto = p.ID_Producto 
                ORDER BY i.Stock ASC LIMIT 6";
$res_g = @$conexion->query($sql_grafica);
if ($res_g) {
    while($f = $res_g->fetch_assoc()){
        $nombres[] = $f['Nombre_Producto'];
        $stocks[]  = (float)$f['Stock'];
        $minimos[] = (float)$f['Stock_Minimo'];
    }
}

// 3. Carga de Interfaz
require_once __DIR__ . "/../../public/html/encabezado.php";
?>

<main class="p-4 flex-grow-1" style="background-color: #f8f9fa; min-height: 100vh;">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-primary fw-bold mb-0"><i class="fas fa-chart-line me-2"></i>Panel Administrativo</h2>
            <p class="text-muted small">Indicadores de gestión de inventario</p>
        </div>
        <span class="badge bg-white text-primary shadow-sm p-3 rounded-pill">
            <i class="fas fa-calendar-day me-2"></i><?= date('d/m/Y') ?>
        </span>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #0d47a1, #42a5f5); border-radius: 15px;">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small opacity-75">Usuarios</h6>
                    <h2 class="display-6 fw-bold mb-0"><?= $total_usuarios ?></h2>
                    <i class="fas fa-user-shield opacity-25" style="position: absolute; right: 20px; bottom: 20px; font-size: 3rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #b71c1c, #ef5350); border-radius: 15px;">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small opacity-75">Alertas de Stock</h6>
                    <h2 class="display-6 fw-bold mb-0"><?= $total_criticos ?></h2>
                    <i class="fas fa-exclamation-triangle opacity-25" style="position: absolute; right: 20px; bottom: 20px; font-size: 3rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #263238, #546e7a); border-radius: 15px;">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small opacity-75">Valorización</h6>
                    <h2 class="display-6 fw-bold mb-0">$<?= number_format($valor_total, 0) ?></h2>
                    <i class="fas fa-coins opacity-25" style="position: absolute; right: 20px; bottom: 20px; font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
        <h5 class="fw-bold text-dark mb-4">Stock Actual vs Mínimo</h5>
        <div style="height: 350px;">
            <canvas id="graficoAdmin"></canvas>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('graficoAdmin').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($nombres) ?>,
            datasets: [
                { label: 'Stock Actual', data: <?= json_encode($stocks) ?>, backgroundColor: '#42a5f5', borderRadius: 5 },
                { label: 'Stock Mínimo', data: <?= json_encode($minimos) ?>, backgroundColor: '#ef5350', borderRadius: 5 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>

<?php require_once __DIR__ . "/../../public/html/pie_de_pagina.php"; ?>
