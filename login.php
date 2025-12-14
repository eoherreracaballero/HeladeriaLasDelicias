<?php
session_start();

// Desactivar la depuración de errores en producción
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(0);

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=metodo_invalido");
    exit;
}

// RUTA RELATIVA: Asegúrate de que esta ruta sea correcta para tu estructura
require_once 'app/db/conexion.php'; 

// Obtener datos del formulario y filtrar
$identificacion = isset($_POST['no_identificacion']) ? filter_var(trim($_POST['no_identificacion']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

if (empty($identificacion) || empty($contrasena)) {
    header("Location: index.php?error=campos_vacios");
    exit;
}

// 1. Sentencia preparada
$stmt = $conexion->prepare("
    SELECT u.id_usuario, u.no_identificacion, u.nombre, u.id_perfil, u.contrasena, 
           p.nombre_perfil
    FROM usuario u
    INNER JOIN perfiles p ON u.id_perfil = p.id_perfil
    WHERE u.no_identificacion = ? OR u.email = ?
");

// 2. Enlace de parámetros
$stmt->bind_param("ss", $identificacion, $identificacion);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// 3. Validación robusta de contraseña y gestión de errores
if ($usuario) {
    // Verificar si la contraseña proporcionada coincide con el hash almacenado
    if (password_verify($contrasena, $usuario['contrasena'])) {
        
        // Regenerar ID de sesión para prevenir ataques de fijación de sesión (CRÍTICO)
        session_regenerate_id(true);

        // Almacenar datos en sesión
        $_SESSION['autenticado'] = true; // Flag de autenticación
        $_SESSION['no_identificacion'] = $usuario['no_identificacion'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['perfil_id'] = $usuario['id_perfil'];
        $_SESSION['perfil_nombre'] = $usuario['nombre_perfil'];

        

        // Redirigir según perfil
        switch ($usuario['id_perfil']) {
            case 1:
                header("Location: perfiles/administracion/inicio_admin.php");
                break;
            case 2:
                header("Location: perfiles/compras/inicio_compras.php");
                break;
            case 3:
                header("Location: perfiles/ventas/inicio_ventas.php");
                break;
            case 4:
                header("Location: perfiles/logistica/inicio_logistica.php");
                break;
            case 5:
                header("Location: perfiles/contabilidad/inicio_contabilidad.php");
                break;
            default:
                header("Location: index.php?error=perfil_no_valido");
        }
        exit;
    }

    
    // Si la contraseña no coincide, cae a credenciales inválidas.
}

// Si el usuario no existe o la contraseña es incorrecta
header("Location: index.php?error=credenciales_invalidas");
exit;