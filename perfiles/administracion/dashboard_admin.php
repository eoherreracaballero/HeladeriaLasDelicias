<?php
session_start();

require_once __DIR__ . "/../../app/config/acceso.php";

// Permitir solo perfil Administrador (id_perfil = 1)
verificar_perfil([1]);

$nombreUsuario = $_SESSION['nombre']; 
$perfilUsuario = $_SESSION['perfil_nombre']; 

// Ruta de imagen del usuario
$imgPath = "../img/usuarios/" . $nombreUsuario . ".jpg";
if (!file_exists($imgPath)) {
    $imgPath = "../../public/img/Usuarios/Caballero.jpg"; // Imagen genérica si no existe
}


// cargando el encabezado
require_once __DIR__ . "/../../public/html/encabezado.php";
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard - <?= htmlspecialchars($perfil_nombre) ?></h2>

    <div class="row g-4">

        <?php if($perfil_id == 1): // Administración ?>
            <!-- Usuarios -->
            <div class="col-md-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">Usuarios</h5>
                        <p class="card-text">Cantidad de usuarios registrados</p>
                        <span class="fs-4">12</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/administracion/usuarios.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>
            <!-- Perfiles -->
            <div class="col-md-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">Perfiles</h5>
                        <p class="card-text">Cantidad de perfiles configurados</p>
                        <span class="fs-4">5</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/administracion/configuracion.php?submenu=perfiles" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>

        <?php elseif($perfil_id == 2): // Compras ?>
            <!-- Órdenes de Compra -->
            <div class="col-md-4">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title">Órdenes de Compra</h5>
                        <p class="card-text">Órdenes pendientes por procesar</p>
                        <span class="fs-4">8</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/compras/ordenes.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>
            <!-- Proveedores -->
            <div class="col-md-4">
                <div class="card text-white bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title">Proveedores</h5>
                        <p class="card-text">Número de proveedores activos</p>
                        <span class="fs-4">15</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/compras/proveedores.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>

        <?php elseif($perfil_id == 3): // Ventas ?>
            <!-- Facturación -->
            <div class="col-md-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">Facturación</h5>
                        <p class="card-text">Facturas generadas hoy</p>
                        <span class="fs-4">20</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/ventas/facturacion.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>
            <!-- Clientes -->
            <div class="col-md-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">Clientes</h5>
                        <p class="card-text">Clientes registrados</p>
                        <span class="fs-4">50</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/ventas/clientes.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>

        <?php elseif($perfil_id == 4): // Logística ?>
            <!-- Inventarios -->
            <div class="col-md-4">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title">Inventarios</h5>
                        <p class="card-text">Productos con stock bajo</p>
                        <span class="fs-4">7</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/logistica/inventarios.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>
            <!-- Bodegas -->
            <div class="col-md-4">
                <div class="card text-white bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title">Bodegas</h5>
                        <p class="card-text">Bodegas activas</p>
                        <span class="fs-4">3</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/logistica/bodegas.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>

        <?php elseif($perfil_id == 5): // Contabilidad ?>
            <!-- Facturación -->
            <div class="col-md-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">Facturación</h5>
                        <p class="card-text">Total facturado este mes</p>
                        <span class="fs-4">$12,500</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/ventas/facturacion.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>
            <!-- Cuentas -->
            <div class="col-md-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">Cuentas</h5>
                        <p class="card-text">Cuentas por pagar y cobrar</p>
                        <span class="fs-4">18</span>
                    </div>
                    <div class="card-footer">
                        <a href="/Heladeria/modulos/contabilidad/cuentas.php" class="text-white">Ver detalle</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
