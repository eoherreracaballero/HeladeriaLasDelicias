<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// conexión y encabezado
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// Leer submenu activo
$submenu = $_GET['submenu'] ?? 'perfiles';
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-cogs me-2"></i>Configuración del Sistema</h2>

        <!-- Agregando Styles a las pestañas de menu -->
         
    <style>
    /* Pestañas de navegación */
    .nav-tabs .nav-link {
    transition: background-color 0.3s ease, color 0.3s ease; /* Transición suave */
    }

    /* Pestaña activa */
    .nav-tabs .nav-link.active {
    background-color: #14ec87 !important; /* Color de fondo activo */
    color: #fff !important; /* Color del texto activo */
    font-weight: 600;
    }

    /* Hover sobre pestaña */
    .nav-tabs .nav-link:hover {
    background-color: #12d277 !important; /* Color al pasar el mouse */
    color: #fff !important;
}
    </style>

    <!-- Menú de submenus -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $submenu === 'perfiles' ? 'active' : '' ?>" href="configuracion.php?submenu=perfiles">Perfiles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $submenu === 'parametros' ? 'active' : '' ?>" href="configuracion.php?submenu=parametros">Parámetros</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $submenu === 'facturacion' ? 'active' : '' ?>" href="configuracion.php?submenu=facturacion">Facturación</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $submenu === 'alertas' ? 'active' : '' ?>" href="configuracion.php?submenu=alertas">Alertas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $submenu === 'seguridad' ? 'active' : '' ?>" href="configuracion.php?submenu=seguridad">Seguridad</a>
        </li>
    </ul>

    <!-- Contenido dinámico según submenu -->
    <?php if ($submenu === 'perfiles'): ?>
        <h3>Gestión de Perfiles</h3>
        <?php
        $result = $conexion->query("SELECT * FROM perfiles ORDER BY id_perfil");
        ?>
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID</th>
                    <th>Perfil</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $result->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td><?= $fila['id_perfil'] ?></td>
                        <td><?= htmlspecialchars($fila['nombre_perfil']) ?></td>
                        <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                        <td>
                            <a href="editar_perfil.php?id=<?= $fila['id_perfil'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="eliminar_perfil.php?id=<?= $fila['id_perfil'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
                            <!-- Botón para ir a permisos -->
                            <a href="permisos.php?perfil=<?= $fila['id_perfil'] ?>" class="btn btn-sm btn-info">Permisos</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php elseif ($submenu === 'parametros'): ?>
        <h3>Parámetros del Sistema</h3>
        <p>Opciones generales:</p>
        <ul>
            <li>Logo del sistema</li>
            <li>Moneda y símbolos</li>
            <li>Impuestos por defecto</li>
            <li>Alertas de stock bajo</li>
        </ul>

    <?php elseif ($submenu === 'facturacion'): ?>
        <h3>Configuración de Facturación</h3>
        <ul>
            <li>IVA por defecto</li>
            <li>Serie de facturas</li>
            <li>Formato de tickets</li>
            <li>Opciones de impresión</li>
        </ul>

    <?php elseif ($submenu === 'alertas'): ?>
        <h3>Alertas del Sistema</h3>
        <ul>
            <li>Stock mínimo de productos</li>
            <li>Productos próximos a vencer</li>
            <li>Notificaciones de pedidos pendientes</li>
        </ul>

    <?php elseif ($submenu === 'seguridad'): ?>
        <h3>Seguridad y Accesos</h3>
        <ul>
            <li>Cambio de contraseña</li>
            <li>Políticas de acceso por módulo</li>
            <li>Control de sesiones activas</li>
        </ul>
    <?php endif; ?>
</main>

<?php $conexion->close(); ?>

