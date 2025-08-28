<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión
include(__DIR__ . "/../../../app/db/conexion.php");

// Capturar datos
$nit        = $_POST['identificacion'] ?? '';
$nombre     = $_POST['nombre'] ?? '';
$ciudad     = $_POST['ciudad'] ?? '';
$direccion  = $_POST['direccion'] ?? '';
$telefono   = $_POST['telefono'] ?? '';
$asesor     = $_POST['asesor'] ?? '';
$productos  = $_POST['productos'] ?? '';

// Validar
if (empty($nit) || empty($nombre) || empty($ciudad) || empty($direccion) || empty($telefono) || empty($asesor) || empty($productos)) {
    echo "<script>alert('⚠️ Todos los campos son obligatorios.'); window.location.href='../proveedores.php';</script>";
    exit;
}

// Verificar si ya existe el NIT
$sql_check = "SELECT * FROM proveedor WHERE No_NIT = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $nit);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $stmt_check->close();
    $conexion->close();
    header("Location: ../proveedores.php?error=existe");
    exit;
}
$stmt_check->close();

// Insertar
$sql_insert = "INSERT INTO proveedor 
    (No_NIT, Nombre_Proveedor, Ciudad, Direccion, Tel_Contacto, Asesor_Contacto, Productos_Venta) 
    VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt_insert = $conexion->prepare($sql_insert);
$stmt_insert->bind_param("issssss", $nit, $nombre, $ciudad, $direccion, $telefono, $asesor, $productos);

if ($stmt_insert->execute()) {
    header("Location: ../proveedores.php?msg=Proveedor registrado con exito");
} else {
    echo "❌ Error al registrar proveedor: " . $stmt_insert->error;
}

$stmt_insert->close();
$conexion->close();
?>
