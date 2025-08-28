<?php
include("../db/conexion.php");

$result = $conexion->query("SELECT id_usuario, contrasena FROM usuario");

while ($row = $result->fetch_assoc()) {
    $id = $row['id_usuario'];
    $pass = $row['contrasena'];

    // Saltar si ya está hasheada
    if (strpos($pass, '$2y$') === 0) continue;

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conexion->prepare("UPDATE usuario SET contrasena = ? WHERE id_usuario = ?");
    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
    $stmt->close();

    echo "Usuario $id actualizado.<br>";
}

$conexion->close();
echo "✅ Todas las contraseñas convertidas a hash.";
?>