<?php
include(__DIR__ . "/../../../app/db/conexion.php");
$id = intval($_GET['id']);

// Cambiamos el estado a Inactivo
$sql = "UPDATE producto SET Estado = 'Inactivo' WHERE ID_Producto = $id";

if ($conexion->query($sql)) {
    header("Location: ../productos.php?msg=suspended");
} else {
    echo "Error: " . $conexion->error;
}
?>
