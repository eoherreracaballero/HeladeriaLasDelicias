<?php
ob_start();

// Inclusión de componentes de interfaz
require_once __DIR__ . "/../../../public/html/encabezado.php";
include(__DIR__ . "/../../../app/db/conexion.php");
require_once __DIR__ . "/../../../public/html/tablas.php";

global $conexion;

// Obtener el ID desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consultar usuario actual
$sql = "SELECT * FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: ../usuarios.php?error=no_encontrado");
    exit;
}

$usuario = $resultado->fetch_assoc();
$stmt->close();

// Procesar Formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identificacion = $_POST['Identificacion'] ?? '';
    $nombre         = $_POST['nombre'] ?? '';
    $ciudad         = $_POST['ciudad'] ?? '';
    $direccion      = $_POST['direccion'] ?? '';
    $telefono       = $_POST['telefono'] ?? '';
    $cargo          = $_POST['cargo'] ?? '';
    $id_perfil      = intval($_POST['id_perfil'] ?? 0);
    $email          = $_POST['email'] ?? '';
    $nuevaClave     = trim($_POST['nueva_contrasena'] ?? ''); 

    // Construcción dinámica del UPDATE
    $campos = "no_identificacion=?, nombre=?, ciudad=?, direccion=?, telefono=?, cargo=?, id_perfil=?, email=?";
    $tipos = "ssssssis"; // 'i' para id_perfil
    $params = [$identificacion, $nombre, $ciudad, $direccion, $telefono, $cargo, $id_perfil, $email];

    if (!empty($nuevaClave)) {
        $campos .= ", contrasena=?";
        $tipos .= "s";
        $params[] = password_hash($nuevaClave, PASSWORD_DEFAULT);
    }

    $sql_update = "UPDATE usuario SET $campos WHERE id_usuario=?";
    $tipos .= "i";
    $params[] = $id;

    $stmt_upd = $conexion->prepare($sql_update);
    $stmt_upd->bind_param($tipos, ...$params);

    if ($stmt_upd->execute()) {
        header("Location: ../usuarios.php?msg=updated");
        exit;
    } else {
        $error_msg = "Error al actualizar registro.";
    }
}
?>

<main class="container-fluid p-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0"><i class="fas fa-user-edit me-2"></i>Editar Perfil de Usuario</h2>
        <a href="../usuarios.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver a la lista
        </a>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><?= $error_msg ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-database me-2"></i>Datos de: <?= htmlspecialchars($usuario['nombre']) ?></h5>
        </div>
        <div class="card-body p-4 bg-light">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">No. Identificación</label>
                        <input type="number" class="form-control shadow-sm" name="Identificacion" 
                               value="<?= htmlspecialchars($usuario['no_identificacion']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nombre Completo</label>
                        <input type="text" class="form-control shadow-sm" name="nombre" 
                               value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Correo Electrónico</label>
                        <input type="email" class="form-control shadow-sm" name="email" 
                               value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted">Ciudad</label>
                        <input type="text" class="form-control shadow-sm" name="ciudad" 
                               value="<?= htmlspecialchars($usuario['ciudad']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted">Dirección</label>
                        <input type="text" class="form-control shadow-sm" name="direccion" 
                               value="<?= htmlspecialchars($usuario['direccion']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted">Teléfono</label>
                        <input type="number" class="form-control shadow-sm" name="telefono" 
                               value="<?= htmlspecialchars($usuario['telefono']) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cargo</label>
                        <input type="text" class="form-control shadow-sm" name="cargo" 
                               value="<?= htmlspecialchars($usuario['cargo']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Perfil / Rol</label>
                        <select class="form-select shadow-sm border-primary" name="id_perfil" required>
                            <?php
                            $res_perfiles = $conexion->query("SELECT id_perfil, nombre_perfil FROM perfiles");
                            while ($p = $res_perfiles->fetch_assoc()):
                                $sel = ($p['id_perfil'] == $usuario['id_perfil']) ? 'selected' : '';
                            ?>
                                <option value="<?= $p['id_perfil'] ?>" <?= $sel ?>><?= $p['nombre_perfil'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-danger">Restablecer Contraseña</label>
                        <input type="password" class="form-control shadow-sm border-danger" name="nueva_contrasena" 
                               placeholder="Dejar en blanco si no desea cambiarla">
                        <small class="text-muted d-block mt-1">Solo llene este campo si el usuario olvidó su clave.</small>
                    </div>

                    <div class="col-12 mt-5">
                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="fas fa-sync-alt me-2"></i>Actualizar Información
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php 
mysqli_close($conexion);
ob_end_flush();
?>
