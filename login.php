<?php
session_start();

// Habilitar depuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=metodo_invalido");
    exit;
}

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=heladeria;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener datos del formulario
$identificacion = isset($_POST['no_identificacion']) ? trim($_POST['no_identificacion']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

if (empty($identificacion) || empty($contrasena)) {
    header("Location: index.php?error=campos_vacios");
    exit;
}

// Buscar usuario y traer nombre del perfil
$stmt = $pdo->prepare("
    SELECT u.*, p.nombre_perfil
    FROM usuario u
    INNER JOIN perfiles p ON u.id_perfil = p.id_perfil
    WHERE u.no_identificacion = ? OR u.email = ?
");
$stmt->execute([$identificacion, $identificacion]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
    $_SESSION['no_identificacion'] = $usuario['no_identificacion'];
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['perfil_id'] = $usuario['id_perfil'];
    $_SESSION['perfil_nombre'] = $usuario['nombre_perfil'];

    // Redirigir según perfil
    switch ($usuario['id_perfil']) {
        case 1: // Administración
            header("Location: perfiles/administracion/inicio_admin.php");
            break;
        case 2: // Compras
            header("Location: perfiles/compras/inicio_compras.php");
            break;
        case 3: // Ventas
            header("Location: perfiles/ventas/inicio_ventas.php");
            break;
        case 4: // Logística
            header("Location: perfiles/logistica/inicio_logistica.php");
            break;
        case 5: // Contabilidad
            header("Location: perfiles/contabilidad/inicio_contabilidad.php");
            break;
        default:
            header("Location: index.php?error=perfil_no_valido");
    }
    exit;
} else {
    header("Location: index.php?error=credenciales_invalidas");
    exit;
}
?>
