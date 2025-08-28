<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Capturar datos del formulario
$identificacion = $_POST['Identificacion'] ?? '';
$nombre         = $_POST['nombre'] ?? '';
$ciudad         = $_POST['ciudad'] ?? '';
$direccion      = $_POST['direccion'] ?? '';
$telefono       = $_POST['telefono'] ?? '';
$cargo          = $_POST['cargo'] ?? '';
$id_perfil      = $_POST['id_perfil'] ?? '';
$email          = $_POST['email'] ?? '';
$clave_plana    = $_POST['clave'] ?? '';

// Validar campos requeridos
if (
    empty($identificacion) || 
    empty($nombre) || 
    empty($ciudad) || 
    empty($direccion) ||
    empty($telefono) || 
    empty($cargo) || 
    empty($id_perfil) || 
    empty($email) || 
    empty($clave_plana)
) {
    echo "<script>alert('Todos los campos son obligatorios.'); window.location.href='../usuarios.php';</script>";
    exit;
}

// Verificar si ya existe identificación o correo
$sql_check = "SELECT * FROM usuario WHERE no_identificacion = ? OR email = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("is", $identificacion, $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $stmt_check->close();
    $conexion->close();
    header("Location: ../usuarios.php?error=existe");
    exit;
}
$stmt_check->close();

// Encriptar la contraseña
$clave_hash = password_hash($clave_plana, PASSWORD_DEFAULT);

// Insertar nuevo usuario
$sql_insert = "INSERT INTO usuario 
    (no_identificacion, nombre, ciudad, direccion, telefono, cargo, id_perfil, email, contrasena) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt_insert = $conexion->prepare($sql_insert);
$stmt_insert->bind_param("issssssss",
    $identificacion,
    $nombre,
    $ciudad,
    $direccion,
    $telefono,
    $cargo,
    $id_perfil,
    $email,
    $clave_hash
);

if ($stmt_insert->execute()) {
    header("Location: ../usuarios.php?mensaje=Usuario registrado con éxito");
} else {
    echo "Error al registrar usuario: " . $stmt_insert->error;
}

$stmt_insert->close();
$conexion->close();
?>
