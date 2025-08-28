<?php

// Incluir control de sesión y perfil
require_once __DIR__ . "/../../app/config/acceso.php";

$perfil_id = $_SESSION['perfil_id'];
$perfil_nombre = $_SESSION['perfil_nombre'];
$nombreUsuario = $_SESSION['nombre'];

// Definir módulos permitidos según perfil
$modulosPermitidos = [];
switch($perfil_id) {
    case 1: $modulosPermitidos = ['parametrizacion','abastecimiento','ventas','inventarios']; break;
    case 2: $modulosPermitidos = ['abastecimiento','inventarios']; break;
    case 3: $modulosPermitidos = ['ventas']; break;
    case 4: $modulosPermitidos = ['inventarios']; break;
}

// Función para verificar si la ruta actual coincide con alguna de las rutas objetivo
function isActive($currentPath, $targetPaths) {
    foreach ($targetPaths as $path) {
        if (strpos($currentPath, $path) !== false) return true;
    }
    return false;
}

// Rutas de inicio y dashboard según perfil
switch ($perfil_id) {
    case 1: // Administración
        $rutaInicio = "/Heladeria/perfiles/administracion/inicio_admin.php";
        $rutaDashboard = "/Heladeria/perfiles/administracion/dashboard_admin.php";
        break;
    case 2: // Compras
        $rutaInicio = "/Heladeria//perfiles/compras/inicio_compras.php";
        $rutaDashboard = "/Heladeria//perfiles/compras/dashboard_compras.php";
        break;
    case 3: // Ventas
        $rutaInicio = "/Heladeria//perfiles/ventas/inicio_ventas.php";
        $rutaDashboard = "/Heladeria//perfiles/ventas/dashboard_ventas.php";
        break;
    case 4: // Logística
        $rutaInicio = "/Heladeria//perfiles/logistica/inicio_logistica.php";
        $rutaDashboard = "/Heladeria//perfiles/logistica/dashboard_logistica.php";
        break;
    case 5: // Contabilidad
        $rutaInicio = "/Heladeria//perfiles/contabilidad/inicio_contabilidad.php";
        $rutaDashboard = "/Heladeria//perfiles/contabilidad/dashboard_contabilidad.php";
        break;
    default:
        $rutaInicio = "/Heladeria/index.php";
        $rutaDashboard = "/Heladeria/index.php";
}

// Ruta actual para marcar menú activo
$rutaActual = $_SERVER['PHP_SELF'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>El Palacio de las Delicias</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../app/css/styles.css">
</head>
<body>
<header class="text-white p-2 d-flex flex-wrap justify-content-between align-items-center" style="background-color: rgb(73, 98, 255);">
    <div class="d-flex align-items-center mb-2 mb-md-0">
        <button class="btn btn-light d-md-none me-2" id="toggleMenu"><i class="fas fa-bars"></i></button>
        <img src="../../public/img/logo.png" width="60" height="60" class="me-2">
        <span class="fs-5 fw-bold">El Palacio de las Delicias</span>
    </div>
    <div class="flex-grow-1 ms-md-3">
        <input type="text" id="searchGlobal" class="form-control busqueda-input" placeholder="Buscar en la página...">
    </div>
        <div class="d-flex align-items-center ms-3">
        <span class="me-3 text-white"><?= htmlspecialchars($nombreUsuario) ?></span>
            <a href="/Heladeria/app/config/logout.php" id="logoutBtn" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>    
        </div>
</header>

<div class="main-container d-flex flex-column flex-md-row">
<nav class="bg-primary text-white p-3" id="sidebar">
    <ul class="nav flex-column">
        <!-- Inicio -->
        <li class="nav-item">
            <a href="<?= $rutaInicio ?>" class="nav-link text-white <?= ($rutaActual == $rutaInicio) ? 'active' : ''; ?>">
                <i class="fas fa-home me-2"></i>Inicio
            </a>
        </li>

        <!-- Dashboard -->
        <li class="nav-item">
            <a href="<?= $rutaDashboard ?>" class="nav-link text-white <?= ($rutaActual == $rutaDashboard) ? 'active' : ''; ?>">
                <i class="fas fa-chart-line me-2"></i>Dashboard
            </a>
        </li>

        <!-- Parametrización -->
        <?php if(in_array('parametrizacion', $modulosPermitidos)):
        $paramActive = isActive($rutaActual, [
            '/modulos/administracion/usuarios.php',
            '/modulos/administracion/configuracion.php'
        ]); ?>
        <li class="nav-item">
            <a class="nav-link text-white d-flex justify-content-between align-items-center <?= $paramActive ? 'active-main' : ''; ?>" 
               data-bs-toggle="collapse" href="#paramMenu" role="button" aria-expanded="<?= $paramActive ? 'true' : 'false'; ?>">
                <span><i class="fas fa-cogs me-2"></i>Parametrización</span>
                <i class="fas fa-chevron-down transition-arrow <?= $paramActive ? 'rotate' : ''; ?>"></i>
            </a>
            <ul class="collapse list-unstyled ms-4 <?= $paramActive ? 'show' : ''; ?>" id="paramMenu">
                <li><a href="/Heladeria/modulos/administracion/usuarios.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/administracion/usuarios.php') !== false ? 'active-sub' : ''; ?>">Usuarios</a></li>
                <li><a href="/Heladeria/modulos/administracion/configuracion.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/administracion/configuracion.php') !== false ? 'active-sub' : ''; ?>">Configuración</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- Compras -->
        <?php if(in_array('abastecimiento', $modulosPermitidos)):
        $comprasActive = isActive($rutaActual, [
            '/modulos/compras/proveedores.php',
            '/modulos/compras/ingresos.php',
            '/modulos/compras/anulaciones.php'
        ]); ?>
        <li class="nav-item">
            <a class="nav-link text-white d-flex justify-content-between align-items-center <?= $comprasActive ? 'active-main' : ''; ?>" 
                data-bs-toggle="collapse" href="#comprasMenu" role="button" aria-expanded="<?= $comprasActive ? 'true' : 'false'; ?>">
                    <span><i class="fas fa-truck me-2"></i>Compras</span>
                    <i class="fas fa-chevron-down transition-arrow <?= $comprasActive ? 'rotate' : ''; ?>"></i>
            </a>
            <ul class="collapse list-unstyled ms-4 <?= $comprasActive ? 'show' : ''; ?>" id="comprasMenu">
                <li><a href="/Heladeria/modulos/compras/proveedores.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/compras/proveedores.php') !== false ? 'active-sub' : ''; ?>">Proveedores</a></li>
                <li><a href="/Heladeria/modulos/compras/ingresos.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/compras/ingresos.php') !== false ? 'active-sub' : ''; ?>">Ingresos</a></li>
                <li><a href="/Heladeria/modulos/compras/anulaciones.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/compras/anulaciones.php') !== false ? 'active-sub' : ''; ?>">Anulaciones</a></li>
            </ul>
        </li>
        <?php endif; ?>         


        <!-- Ventas -->
        <?php if(in_array('ventas', $modulosPermitidos)):
        $ventasActive = isActive($rutaActual, [
            '/modulos/ventas/clientes.php',
            '/modulos/ventas/facturacion.php',
            '/modulos/ventas/notas.php'
        ]); ?>
        <li class="nav-item">
            <a class="nav-link text-white d-flex justify-content-between align-items-center <?= $ventasActive ? 'active-main' : ''; ?>" 
               data-bs-toggle="collapse" href="#ventasMenu" role="button" aria-expanded="<?= $ventasActive ? 'true' : 'false'; ?>">
                <span><i class="fas fa-shopping-cart me-2"></i>Ventas</span>
                <i class="fas fa-chevron-down transition-arrow <?= $ventasActive ? 'rotate' : ''; ?>"></i>
            </a>
            <ul class="collapse list-unstyled ms-4 <?= $ventasActive ? 'show' : ''; ?>" id="ventasMenu">
                <li><a href="/Heladeria/modulos/ventas/clientes.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/ventas/clientes.php') !== false ? 'active-sub' : ''; ?>">Clientes</a></li>
                <li><a href="/Heladeria/modulos/ventas/facturacion.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/ventas/facturacion.php') !== false ? 'active-sub' : ''; ?>">Facturación</a></li>
                <li><a href="/Heladeria/modulos/ventas/notas.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/ventas/notas.php') !== false ? 'active-sub' : ''; ?>">Notas Débito y Crédito</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- Inventarios y Reportes -->
        <?php if(in_array('inventarios', $modulosPermitidos)):
        $invActive = isActive($rutaActual, [
            '/modulos/logistica/inventarios.php',
            '/modulos/logistica/productos.php',
            '/modulos/logistica/bodegas.php',
            '/modulos/logistica/ajustes.php',
            '/modulos/logistica/reportes.php'
        ]); ?>
        <li class="nav-item">
            <a class="nav-link text-white d-flex justify-content-between align-items-center <?= $invActive ? 'active-main' : ''; ?>" 
               data-bs-toggle="collapse" href="#invMenu" role="button" aria-expanded="<?= $invActive ? 'true' : 'false'; ?>">
                <span><i class="fas fa-warehouse me-2"></i>Inventarios y Reportes</span>
                <i class="fas fa-chevron-down transition-arrow <?= $invActive ? 'rotate' : ''; ?>"></i>
            </a>
            <ul class="collapse list-unstyled ms-4 <?= $invActive ? 'show' : ''; ?>" id="invMenu">
                <li><a href="/Heladeria/modulos/logistica/inventarios.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/logistica/inventarios.php') !== false ? 'active-sub' : ''; ?>">Stocks</a></li>
                <li><a href="/Heladeria/modulos/logistica/productos.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/logistica/productos.php') !== false ? 'active-sub' : ''; ?>">Productos</a></li>
                <li><a href="/Heladeria/modulos/logistica/bodegas.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/logistica/bodegas.php') !== false ? 'active-sub' : ''; ?>">Bodegas</a></li>
                <li><a href="/Heladeria/modulos/logistica/ajustes.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/logistica/ajustes.php') !== false ? 'active-sub' : ''; ?>">Ajustes</a></li>
                <li><a href="/Heladeria/modulos/logistica/reportes.php" class="nav-link text-white <?= strpos($rutaActual, '/modulos/logistica/reportes.php') !== false ? 'active-sub' : ''; ?>">Reportes</a></li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
</nav>

<main class="main-content flex-grow-1 p-3">
<!-- Contenido de cada página -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggleMenu = document.getElementById('toggleMenu');
const sidebar = document.getElementById('sidebar');

toggleMenu.addEventListener('click', () => sidebar.classList.toggle('active'));
sidebar.querySelectorAll('a.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        if(window.innerWidth < 768) sidebar.classList.remove('active');
    });
});
document.getElementById('searchGlobal')?.addEventListener('input', function() {
    const text = this.value.toLowerCase();
    document.querySelectorAll('#contenido *').forEach(el => {
        if(el.textContent.toLowerCase().includes(text)) el.style.display = '';
        else if(!['SCRIPT','STYLE'].includes(el.tagName)) el.style.display = 'none';
    });
});
// Confirmación antes de cerrar sesión
const logoutBtn = document.getElementById('logoutBtn');
logoutBtn?.addEventListener('click', function(e) {
    e.preventDefault(); // Evita la redirección automática
    if (confirm("⚠️ ¿Estás seguro que deseas cerrar sesión?")) {
        window.location.href = this.href; // Redirige si confirma
    }
});
</script>

    