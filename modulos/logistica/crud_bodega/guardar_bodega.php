<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
require_once __DIR__ . "/../../../app/db/conexion.php";

// Aceptar solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "⚠️ Acceso no autorizado.";
    exit;
}

// Recibir datos
$nombre    = trim($_POST['nombre']    ?? '');
$ubicacion = trim($_POST['ubicacion'] ?? '');
$estado    = trim($_POST['estado']    ?? '');

// Validación mínima
if ($nombre === '' || $ubicacion === '' || $estado === '') {
    echo "Todos los campos son obligatorios.";
    exit;
}

// Insertar
$sql  = "INSERT INTO bodega (Nombre_Bodega, Ubicacion, Estado) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("❌ Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("sss", $nombre, $ubicacion, $estado);

if ($stmt->execute()) {
    // Redirige al listado con mensaje de éxito
    header("Location: ../bodegas.php?mensaje=Bodega agregada con exito");
    exit;
} else {
    echo "❌ Error al guardar la bodega: " . $stmt->error;
}

$stmt->close();
$conexion->close();
 