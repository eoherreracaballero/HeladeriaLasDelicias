<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Encabezado y conexión
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");

// Consultar los clientes
$res_prod = $conexion->query("SELECT * FROM cliente");
$num_reg = $res_prod->num_rows;

if ($num_reg == 0) {
    echo "No hay clientes registrados";
    mysqli_close($conexion);
    exit();
}
// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";
?>

<main class="container-fluid p-4 fade-in" id="contenido">
    <h2 class="text-primary mb-4">
        <i class="fas fa-users-cog me-2"></i> Módulo de Gestión de Clientes
    </h2>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

    <!-- Mensajes de error o éxito -->
    <?php if (isset($_GET['error']) && $_GET['error'] == 'existe'): ?>
    <div class="alert alert-danger">⚠️ Ya existe un cliente con esa identificación o correo electrónico.</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success">✅ Cliente registrado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
    <div class="alert alert-success">✅ Cliente actualizado correctamente.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div class="alert alert-success">🗑️ Cliente eliminado correctamente.</div>
<?php endif; ?>

    <!-- Formulario de registro -->
    <form class="mb-4" method="POST" action="crud_cliente/guardar_cliente.php">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="Identificacion" class="form-label">No. Identificación</label>
                <input type="number" class="form-control" name="Identificacion" id="Identificacion" placeholder="Número de Identificación" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre del cliente" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Dirección del cliente" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono" placeholder="Número de Teléfono o Celular" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="correo" class="form-label">E-mail</label>
                <input type="email" class="form-control" name="correo" id="correo" placeholder="Correo electrónico" required>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save me-2"></i>Guardar Cliente
                </button>
            </div>
        </div>
    </form>

    <!-- Tabla de clientes -->
    <h4 class="text-secondary">Lista de Clientes Registrados</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mt-3 text-center">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>No. Identificación</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>E-mail</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $res_prod->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $fila['Id_cliente'] ?></td>
                        <td><?= $fila['No_NIT'] ?></td>
                        <td><?= $fila['Nombre_Cliente'] ?></td>
                        <td><?= $fila['Direccion'] ?></td>
                        <td><?= $fila['No_Telefono'] ?></td>
                        <td><?= $fila['Email'] ?></td>
                        <td>
                            <a href="crud_cliente/editar_cliente.php?id=<?= $fila['Id_cliente'] ?>" class="btn btn-sm btn-warning me-1 mb-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="crud_cliente/eliminar_cliente.php?id=<?= $fila['Id_cliente'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('¿Estás segura de eliminar este cliente?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>



<!-- Buscador interno -->
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

<?php mysqli_close($conexion); ?>
