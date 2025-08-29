<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// conexión y encabezado
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");

// Traemos usuarios con su perfil
$res_prod = $conexion->query("
    SELECT u.*, p.nombre_perfil 
    FROM usuario u
    INNER JOIN perfiles p ON u.id_perfil = p.id_perfil
    ORDER BY u.id_usuario ASC
");

$num_reg = $res_prod->num_rows;

// Traemos los perfiles para el <select>
$res_perfiles = $conexion->query("SELECT id_perfil, nombre_perfil FROM perfiles");

if ($num_reg == 0) {
    echo "No hay usuarios registrados";
    mysqli_close($conexion);
    exit();
}
// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";
?>

<main class="container-fluid p-4 fade-in" id="contenido">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success">✅ Usuario registrado correctamente.</div>
<?php elseif (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] == "campos"): ?>
        <div class="alert alert-danger">⚠️ Todos los campos son obligatorios.</div>
    <?php elseif ($_GET['error'] == "existe"): ?>
        <div class="alert alert-warning">⚠️ Ya existe un usuario con esa identificación o correo.</div>
    <?php elseif ($_GET['error'] == "insertar"): ?>
        <div class="alert alert-danger">❌ Ocurrió un error al registrar el usuario.</div>
    <?php endif; ?>
<?php endif; ?>

    <h2 class="text-primary mb-4"><i class="fas fa-users-cog me-2"></i>Módulo de Gestión de Usuarios</h2>

    <form class="mb-4" method="POST" action="crud_usuario/guardar_usuario.php">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="Identificacion" class="form-label">No Identificación</label>
                <input type="number" class="form-control" name="Identificacion" id="Identificacion" placeholder="Número de Identificación" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre de usuario" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" id="ciudad" placeholder="Ciudad de Ubicación" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Dirección" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="number" class="form-control" name="telefono" id="telefono" placeholder="No. Tel o Celular" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="cargo" class="form-label">Cargo</label>
                <input type="text" class="form-control" name="cargo" id="cargo" placeholder="Cargo" required>
            </div>

            <!-- Perfil dinámico -->
            <div class="col-12 col-md-4">
                <label for="Perfil" class="form-label">Perfil</label>
                <select class="form-select" name="id_perfil" id="Perfil" required>
                    <option value="">Seleccione un rol</option>
                    <?php while ($perfil = $res_perfiles->fetch_assoc()) { ?>
                        <option value="<?= $perfil['id_perfil'] ?>">
                            <?= $perfil['nombre_perfil'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" name="email" id="email" placeholder="Correo electrónico" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="clave" id="clave" placeholder="Contraseña" required>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100"><i class="fas fa-save me-2"></i>Guardar Usuario</button>
            </div>
        </div>
    </form>

    <h4 class="text-secondary">Lista de Usuarios Registrados</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mt-3">
            <thead class="table-primary text-center">
                <tr>
                    <th>ID</th>
                    <th>Identificación</th>
                    <th>Nombre</th>
                    <th>Ciudad</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Cargo</th>
                    <th>Perfil</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $res_prod->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $fila['id_usuario']; ?></td>
                        <td><?php echo $fila['no_identificacion']; ?></td>
                        <td><?php echo $fila['nombre']; ?></td>
                        <td><?php echo $fila['ciudad']; ?></td>
                        <td><?php echo $fila['direccion']; ?></td>
                        <td><?php echo $fila['telefono']; ?></td>
                        <td><?php echo $fila['cargo']; ?></td>
                        <td><?php echo $fila['nombre_perfil']; ?></td>
                        <td><?php echo $fila['email']; ?></td>
                        <td class="text-center">
                            <!-- Botón Editar -->
                            <a href="crud_usuario/editar_usuario.php?id=<?= $fila['id_usuario'] ?>" class="btn btn-sm btn-warning me-1 mb-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <!-- Botón Eliminar -->
                            <a href="crud_usuario/eliminar_usuario.php?id=<?= $fila['id_usuario'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('¿Estás seguro que deseas eliminar este usuario?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>

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

<?php 
mysqli_close($conexion);
ob_end_flush();
?>
