<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

if (
    isset($_POST['Identificacion']) &&
    isset($_POST['nombre']) &&
    isset($_POST['direccion']) &&
    isset($_POST['telefono']) &&
    isset($_POST['correo']) 
) {

    // Capturar datos del formulario
    $identificacion = trim($_POST['Identificacion']);
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);

    // Preparar consulta con nombres de campos correctos
    $stmt = $conexion->prepare("INSERT INTO cliente (No_NIT, Nombre_Cliente, Direccion, No_Telefono, Email) VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    $stmt->bind_param("sssss", $identificacion, $nombre, $direccion, $telefono, $correo);

    if ($stmt->execute()) {
        header("Location: ../clientes.php?msg=Cliente agregado con éxito");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Error al guardar el cliente: " . $stmt->error . "</div>";
    }

    $stmt->close();
} else {
    echo "<div class='alert alert-warning'>⚠️ Por favor, completa todos los campos del formulario.</div>";
}

mysqli_close($conexion);
?>

