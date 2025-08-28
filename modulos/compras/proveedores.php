<?php
// Encabezado y conexión
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");

// Consultar proveedores
$res_prod = $conexion->query("SELECT * FROM proveedor");
$num_reg = $res_prod->num_rows;
if ($num_reg == 0) {
    echo "<div class='alert alert-warning'>⚠️ No hay proveedores registrados.</div>";
    mysqli_close($conexion);
    exit();
}

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4">
        <i class="fas fa-truck me-2"></i>Módulo de Gestión de Proveedores
    </h2>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

    <!-- Mensajes -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success">✅ Proveedor registrado correctamente.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
        <div class="alert alert-info">✅ Proveedor actualizado correctamente.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-warning">🗑️ Proveedor eliminado.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'existe'): ?>
        <div class="alert alert-danger">⚠️ Ese NIT ya existe en el sistema.</div>
    <?php endif; ?>

    <!-- Formulario -->
    <form class="mb-4" method="POST" action="crud_proveedor/guardar_proveedor.php">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="Identificacion" class="form-label">No. NIT</label>
                <input type="number" class="form-control" name="identificacion" id="identificacion" required>
            </div>
            <div class="col-md-4">
                <label for="nombre_proveedor" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre_proveedor" required>
            </div>
            <div class="col-md-4">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" id="ciudad" required>
            </div>
            <div class="col-md-4">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" name="direccion" id="direccion" required>
            </div>
            <div class="col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono" required>
            </div>
            <div class="col-md-4">
                <label for="asesor" class="form-label">Asesor</label>
                <input type="text" class="form-control" name="asesor" id="asesor" required>
            </div>
            <div class="col-md-8">
                <label for="productos" class="form-label">Productos</label>
                <input type="text" class="form-control" name="productos" id="productos" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save me-2"></i>Guardar Proveedor
                </button>
            </div>
        </div>
    </form>

    <!-- Tabla de Proveedores -->
    <h4 class="text-secondary mt-5">Lista de Proveedores Registrados</h4>
    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered text-center align-middle">
            <thead class="table-dark-text-center">
                <tr>
                    <th>ID</th>
                    <th>No. NIT</th>
                    <th>Nombre</th>
                    <th>Ciudad</th>
                    <th>Dirección</th>
                    <th>Tel. Contacto</th>
                    <th>Asesor</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $res_prod->fetch_assoc()) { ?>
                <tr>
                    <td><?= $fila['ID_Proveedor'] ?></td>
                    <td><?= $fila['No_NIT'] ?></td>
                    <td><?= $fila['Nombre_Proveedor'] ?></td>
                    <td><?= $fila['Ciudad'] ?></td>
                    <td><?= $fila['Direccion'] ?></td>
                    <td><?= $fila['Tel_Contacto'] ?></td>
                    <td><?= $fila['Asesor_Contacto'] ?></td>
                    <td><?= $fila['Productos_Venta'] ?></td>
                    <td>
                        <a href=crud_proveedor/editar_proveedor.php?id=<?= $fila['ID_Proveedor'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="crud_proveedor/eliminar_proveedor.php?id=<?= $fila['ID_Proveedor'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>

<?php mysqli_close($conexion); ?>
