<?php
ob_start();

// incluir encabezado.php para cargar estilos y scripts
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../../public/html/tablas.php";

global $conexion;

// --- Función para Hashear Contraseña (Necesaria para guardar el nuevo hash) ---
if (!function_exists('generar_hash_contrasena')) {
    function generar_hash_contrasena($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
// ----------------------------------------------------------------------------


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
$stmt->close(); // Cierra el statement de consulta

// Procesar si envían el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recoger datos de usuario
    $identificacion = $_POST['Identificacion'] ?? '';
    $nombre         = $_POST['nombre'] ?? '';
    $ciudad         = $_POST['ciudad'] ?? '';
    $direccion      = $_POST['direccion'] ?? '';
    $telefono       = $_POST['telefono'] ?? '';
    $cargo          = $_POST['cargo'] ?? '';
    $id_perfil      = intval($_POST['id_perfil'] ?? 0);
    $email          = $_POST['email'] ?? '';
    
    // NUEVO: Campo de contraseña (puede estar vacío)
    $nuevaContrasena = trim($_POST['nueva_contrasena'] ?? ''); 

    // 2. Definir SQL, Tipos y Parámetros
    
    // Parámetros base del UPDATE: Identificación, Nombre, Ciudad, Dirección, Teléfono, Cargo, ID_Perfil, Email, ID_Usuario
    $updateFields = "no_identificacion=?, nombre=?, ciudad=?, direccion=?, telefono=?, cargo=?, id_perfil=?, email=?";
    $tiposBase = "ssssssss";
    $parametros = [
        $identificacion, $nombre, $ciudad, $direccion, $telefono, $cargo, $id_perfil, $email
    ];
    
    // 3. Lógica para la Contraseña
    if (!empty($nuevaContrasena)) {
        $contrasenaHasheada = generar_hash_contrasena($nuevaContrasena);
        
        // Agregar la columna 'contrasena' al UPDATE
        $updateFields .= ", contrasena=?";
        $tiposBase .= "s"; // Añadir 's' para la contraseña hasheada
        $parametros[] = $contrasenaHasheada; // Añadir el valor hasheado
    }

    // 4. Completar SQL con WHERE y preparar
    $update = "UPDATE usuario SET " . $updateFields . " WHERE id_usuario=?";
    
    $tiposBase .= "i"; // Añadir 'i' para el ID del usuario
    $parametros[] = $id; // Añadir el ID del usuario al final de los parámetros

    $stmt_update = $conexion->prepare($update);
    if (!$stmt_update) {
        echo "<div class='alert alert-danger'>❌ Error al preparar la actualización: " . $conexion->error . "</div>";
        exit;
    }

    // 5. Ejecutar bind_param dinámicamente
    // Usamos call_user_func_array para manejar el número dinámico de parámetros.
    array_unshift($parametros, $tiposBase);
    call_user_func_array([$stmt_update, 'bind_param'], $parametros);

    if ($stmt_update->execute()) {
        header("Location: ../usuarios.php?msg=updated");
        exit;
    } else {
        echo "<div class='alert alert-danger'>❌ Error al actualizar: " . $stmt_update->error . "</div>";
    }
    $stmt_update->close();
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
                <label for="nueva_contrasena" class="form-label text-danger">Nueva Contraseña (Opcional)</label>
                <input type="password" class="form-control" name="nueva_contrasena" id="nueva_contrasena" placeholder="Dejar vacío para NO cambiar">
                <small class="text-muted">Si ingresa texto aquí, se generará un nuevo Hash.</small>
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
            <div class="col-12 d-flex align-items-end justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</main>

<?php mysqli_close($conexion); ?>
