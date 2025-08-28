<?php
// validar sesión y perfil
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
?>

<?php require_once __DIR__ . "/../../public/html/encabezado.php"; ?>

<div class="d-flex flex-column flex-md-row">
    <main class="p-4 flex-grow-1 fade-in" id="contenido">
        <div class="text-center mb-4">
            <!-- Imagen circular del usuario -->
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="Foto de <?= htmlspecialchars($nombreUsuario) ?>" 
                 class="rounded-circle shadow" width="120" height="120" style="object-fit: cover; border: 3px solid #ccc;">

            <!-- Nombre y perfil -->
            <h4 class="text-secondary mt-3 mb-3">
                Usuario: <strong><?= htmlspecialchars($nombreUsuario) ?></strong> | Perfil: <strong><?= htmlspecialchars($perfilUsuario) ?></strong>
            </h4>

            <h2 class="text-primary">Bienvenido a El Palacio de las Delicias</h2>
            <p class="lead">Sistema de gestión para heladerías. Usa el menú lateral para acceder a los diferentes módulos.</p>
        </div>

        <hr>
        <div class="d-flex justify-content-center flex-wrap gap-4">
            <!-- Parametrización -->
            <div class="card border-primary shadow-sm" style="width: 22rem;">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary"><i class="fas fa-sliders-h me-2"></i>Parametrización</h5>
                    <ul class="list-unstyled text-start">
                        <li>Gestión de usuarios / empleados</li>
                        <li>Creación de Proveedores</li>
                        <li>Creación de Clientes</li>
                        <li>Configuración de Bodegas</li>
                        <li>Configuración de permisos, seguridad y credenciales</li>
                    </ul>
                </div>
            </div>

            <!-- Abastecimiento -->
            <div class="card border-success shadow-sm" style="width: 22rem;">
                <div class="card-body text-center">
                    <h5 class="card-title text-success"><i class="fas fa-truck-loading me-2"></i>Abastecimiento</h5>
                    <ul class="list-unstyled text-start">
                        <li>Órdenes de compra (crear, modificar, anular)</li>
                        <li>Ajustes de Ingreso</li>
                        <li>Registro y anulación de ingresos</li>
                    </ul>
                </div>
            </div>

            <!-- Ventas -->
            <div class="card border-warning shadow-sm" style="width: 22rem;">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning"><i class="fas fa-cash-register me-2"></i>Ventas</h5>
                    <ul class="list-unstyled text-start">
                        <li>Facturación (generación y anulación)</li>
                        <li>Notas de crédito y débito</li>
                    </ul>
                </div>
            </div>

            <!-- Inventario y Reportes -->
            <div class="card border-info shadow-sm" style="width: 22rem;">
                <div class="card-body text-center">
                    <h5 class="card-title text-info"><i class="fas fa-boxes me-2"></i>Inventario y Reportes</h5>
                    <ul class="list-unstyled text-start">
                        <li>Creación y ajustes de productos</li>
                        <li>Informes de ventas, compras, stocks y más</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('searchGlobal')?.addEventListener('input', function () {
        const text = this.value.toLowerCase();
        document.querySelectorAll('#contenido *').forEach(el => {
            if (el.textContent.toLowerCase().includes(text)) {
                el.style.display = '';
            } else if (el.tagName !== 'SCRIPT' && el.tagName !== 'STYLE') {
                el.style.display = 'none';
            }
        });
    });
</script>

</div>
</body>
</html>