<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=metodo_invalido");
    exit;
}

require_once 'app/db/conexion.php'; 

$identificacion = isset($_POST['no_identificacion']) ? filter_var(trim($_POST['no_identificacion']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

if (empty($identificacion) || empty($contrasena)) {
    header("Location: index.php?error=campos_vacios");
    exit;
}

// 1. Sentencia preparada
// Eliminamos el filtro de 'Estado' en el SQL para poder dar un mensaje específico si está suspendido
$stmt = $conexion->prepare("
    SELECT u.id_usuario, u.no_identificacion, u.nombre, u.id_perfil, u.contrasena, 
           u.Estado, p.nombre_perfil
    FROM usuario u
    INNER JOIN perfiles p ON u.id_perfil = p.id_perfil
    WHERE u.no_identificacion = ? OR u.email = ?
");

// 2. Enlace de parámetros
$stmt->bind_param("ss", $identificacion, $identificacion);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// 3. Validación
if ($usuario) {
    // Primero verificamos la contraseña
    if (password_verify($contrasena, $usuario['contrasena'])) {
        
        // Verificamos si la cuenta está suspendida
        if ($usuario['Estado'] !== 'Activo') {
            header("Location: index.php?error=cuenta_suspendida");
            exit;
        }
        
        // Si todo está bien, iniciamos sesión
        session_regenerate_id(true);

        $_SESSION['autenticado'] = true;
        $_SESSION['no_identificacion'] = $usuario['no_identificacion'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['perfil_id'] = $usuario['id_perfil'];
        $_SESSION['perfil_nombre'] = $usuario['nombre_perfil'];

        // Redirigir según perfil
        switch ($usuario['id_perfil']) {
            case 1: header("Location: perfiles/administracion/inicio_admin.php"); break;
            case 2: header("Location: perfiles/compras/inicio_compras.php"); break;
            case 3: header("Location: perfiles/ventas/inicio_ventas.php"); break;
            case 4: header("Location: perfiles/logistica/inicio_logistica.php"); break;
            case 5: header("Location: perfiles/contabilidad/inicio_contabilidad.php"); break;
            default: header("Location: index.php?error=perfil_no_valido");
        }
        exit;
    }
}

// Error genérico para no dar pistas a atacantes si el usuario no existe
header("Location: index.php?error=credenciales_invalidas");
exit;