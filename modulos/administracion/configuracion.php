<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Conexiones y dependencias
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php";

// Leer submenu activo
$submenu = $_GET['submenu'] ?? 'perfiles';
?>

<style>
    /* Diseño de pestañas moderno */
    .nav-tabs { border-bottom: 2px solid #dee2e6; }
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding: 1rem 1.5rem;
        transition: all 0.3s;
    }
    .nav-tabs .nav-link.active {
        background-color: #14ec87 !important;
        color: white !important;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
        box-shadow: 0 -4px 10px rgba(20, 236, 135, 0.2);
    }
    .config-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .config-card:hover { transform: translateY(-3px); }
</style>

<main class="p-4 flex-grow-1 fade-in" id="contenido" style="background-color: #f8f9fa; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary fw-bold"><i class="fas fa-tools me-2"></i>Panel de Configuración</h2>
        <span class="badge bg-light text-dark border p-2">Versión 1.2.0</span>
    </div>

    <ul class="nav nav-tabs mb-4 gap-2">
        <?php 
        $tabs = [
            'perfiles' => ['Perfiles', 'fas fa-user-tag'],
            'parametros' => ['Empresa', 'fas fa-building'],
            'facturacion' => ['Facturación', 'fas fa-file-invoice-dollar'],
            'alertas' => ['Alertas', 'fas fa-bell'],
            'seguridad' => ['Seguridad', 'fas fa-shield-alt']
        ];
        foreach ($tabs as $key => $val): ?>
            <li class="nav-item">
                <a class="nav-link <?= $submenu === $key ? 'active' : '' ?>" href="configuracion.php?submenu=<?= $key ?>">
                    <i class="<?= $val[1] ?> me-2"></i><?= $val[0] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="card p-4 border-0 shadow-sm" style="border-radius: 15px;">

        <?php if ($submenu === 'perfiles'): ?>
            <div class="d-flex justify-content-between mb-3">
                <h4><i class="fas fa-users-cog text-success me-2"></i>Roles y Permisos</h4>
                <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i>Nuevo Perfil</button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Perfil</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $result = $conexion->query("SELECT * FROM perfiles");
                        while ($fila = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold">#<?= $fila['id_perfil'] ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($fila['nombre_perfil']) ?></span></td>
                            <td class="text-muted"><?= htmlspecialchars($fila['descripcion']) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="permisos.php?id=<?= $fila['id_perfil'] ?>" class="btn btn-sm btn-outline-primary" title="Editar Permisos"><i class="fas fa-key"></i></a>
                                    <a href="editar_perfil.php?id=<?= $fila['id_perfil'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarSuspender(<?= $fila['id_perfil'] ?>)"><i class="fas fa-ban"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($submenu === 'parametros'): ?>
            <h4><i class="fas fa-store text-success me-2"></i>Datos de la Empresa</h4>
            <form class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre del Negocio</label>
                    <input type="text" class="form-control" value="El Palacio de las Delicias">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">NIT / Registro Fiscal</label>
                    <input type="text" class="form-control" value="900.123.456-1">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Moneda Principal</label>
                    <select class="form-select"><option>COP ($)</option><option>USD ($)</option></select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Logo del Sistema</label>
                    <input type="file" class="form-control">
                </div>
                <div class="col-12 mt-4 text-end">
                    <button type="button" class="btn btn-success px-4 rounded-pill">Guardar Cambios</button>
                </div>
            </form>

        <?php elseif ($submenu === 'facturacion'): ?>
            <h4><i class="fas fa-receipt text-success me-2"></i>Ajustes Fiscales</h4>
            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="card config-card p-3 border border-light">
                        <label class="form-label fw-bold">Porcentaje de IVA (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="19">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card config-card p-3 border border-light">
                        <label class="form-label fw-bold">Resolución DIAN / Facturación</label>
                        <input type="text" class="form-control" placeholder="Ej: 18760000001">
                    </div>
                </div>
            </div>

        <?php elseif ($submenu === 'alertas'): ?>
            <h4><i class="fas fa-bell text-success me-2"></i>Gestión de Notificaciones</h4>
            <div class="list-group list-group-flush mt-3">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <h6 class="mb-0">Alerta de Stock Crítico</h6>
                        <small class="text-muted">Notificar cuando un producto llegue al mínimo.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <h6 class="mb-0">Vencimiento de Productos</h6>
                        <small class="text-muted">Avisar 30 días antes de la fecha de caducidad.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<script>
    function confirmarSuspender(id) {
        if(confirm("¿Está seguro de suspender este perfil? El registro no se eliminará físicamente para preservar la integridad de los datos.")) {
            window.location.href = "suspender_perfil.php?id=" + id;
        }
    }
</script>

<?php $conexion->close(); require_once __DIR__ . "/../../public/html/pie_de_pagina.php"; ?>