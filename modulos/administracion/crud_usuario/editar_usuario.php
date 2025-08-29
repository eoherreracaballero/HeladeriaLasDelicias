<?php
ob_start();

// incluir encabezado.php para cargar estilos y scripts
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../../public/html/tablas.php";

// Obtener el ID desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consultar usuario
$sql = "SELECT * FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("⚠️ Usuario no encontrado.");
}

$usuario = $resultado->fetch_assoc();

// Procesar si envían el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identificacion = $_POST['Identificacion'] ?? '';
    $nombre         = $_POST['nombre'] ?? '';
    $ciudad         = $_POST['ciudad'] ?? '';
    $direccion      = $_POST['direccion'] ?? '';
    $telefono       = $_POST['telefono'] ?? '';
    $cargo          = $_POST['cargo'] ?? '';
    $id_perfil      = $_POST['id_perfil'] ?? '';
    $email          = $_POST['email'] ?? '';

    $update = "UPDATE usuario 
               SET no_identificacion=?, nombre=?, ciudad=?, direccion=?, telefono=?, cargo=?, id_perfil=?, email=? 
               WHERE id_usuario=?";
    $stmt_update = $conexion->prepare($update);
    $stmt_update->bind_param(
        "ssssssssi",
        $identificacion,
        $nombre,
        $ciudad,
        $direccion,
        $telefono,
        $cargo,
        $id_perfil,
        $email,
        $id
    );

    if ($stmt_update->execute()) {
        header("Location: ../usuarios.php?msg=updated");
        exit;
    } else {
        echo "<div class='alert alert-danger'>❌ Error al actualizar: " . $stmt_update->error . "</div>";
    }
}
?>

<main class="container-fluid p-4 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-user-edit me-2"></i>Editar Usuario</h2>

    <form class="mb-4" method="POST">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="Identificacion" class="form-label">No Identificación</label>
                <input type="number" class="form-control" name="Identificacion" id="Identificacion" 
                       value="<?= htmlspecialchars($usuario['no_identificacion']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre" 
                       value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" id="ciudad" 
                       value="<?= htmlspecialchars($usuario['ciudad']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" name="direccion" id="direccion" 
                       value="<?= htmlspecialchars($usuario['direccion']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="number" class="form-control" name="telefono" id="telefono" 
                       value="<?= htmlspecialchars($usuario['telefono']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="cargo" class="form-label">Cargo</label>
                <input type="text" class="form-control" name="cargo" id="cargo" 
                       value="<?= htmlspecialchars($usuario['cargo']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="id_perfil" class="form-label">Perfil</label>
                <select class="form-select" name="id_perfil" id="id_perfil" required>
                    <option value="">Seleccione un rol</option>
                    <?php
                        $res_perfiles = $conexion->query("SELECT id_perfil, nombre_perfil FROM perfiles");
                        while ($perfil = $res_perfiles->fetch_assoc()) {
                            $selected = ($perfil['id_perfil'] == $usuario['id_perfil']) ? 'selected' : '';
                            echo "<option value='{$perfil['id_perfil']}' $selected>{$perfil['nombre_perfil']}</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" name="email" id="email" 
                       value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</main>

<?php mysqli_close($conexion); ?>
