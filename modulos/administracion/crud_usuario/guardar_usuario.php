<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . "/../../../app/db/conexion.php");

$identificacion = trim($_POST['Identificacion'] ?? '');
$nombre         = trim($_POST['nombre'] ?? '');
$ciudad         = trim($_POST['ciudad'] ?? '');
$direccion      = trim($_POST['direccion'] ?? '');
$telefono       = trim($_POST['telefono'] ?? '');
$cargo          = trim($_POST['cargo'] ?? '');
$id_perfil      = trim($_POST['id_perfil'] ?? '');
$email          = trim($_POST['email'] ?? '');
$clave_plana    = trim($_POST['clave'] ?? '');

if (empty($identificacion) || empty($nombre) || empty($ciudad) || empty($direccion) ||
    empty($telefono) || empty($cargo) || empty($id_perfil) || empty($email) || empty($clave_plana)) {
    header("Location: ../usuarios.php?error=campos");
    exit;
}

// 1. Verificar duplicados
$sql_check = "SELECT id_usuario FROM usuario WHERE no_identificacion = ? OR email = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ss", $identificacion, $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $stmt_check->close();
    header("Location: ../usuarios.php?error=existe");
    exit;
}
$stmt_check->close();

// 2. Encriptar contraseña
$clave_hash = password_hash($clave_plana, PASSWORD_DEFAULT);

// 3. Insertar nuevo usuario (El 'Activo' va directo en el VALUES)
$sql_insert = "INSERT INTO usuario 
    (no_identificacion, nombre, ciudad, direccion, telefono, cargo, id_perfil, email, contrasena, Estado) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";

$stmt_insert = $conexion->prepare($sql_insert);

// IMPORTANTE: Solo pasamos 9 "s" porque el Estado 'Activo' ya está definido arriba
$stmt_insert->bind_param("sssssssss",
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
    header("Location: ../usuarios.php?success=1");
} else {
    header("Location: ../usuarios.php?error=insertar");
}

$stmt_insert->close();
$conexion->close();
?>