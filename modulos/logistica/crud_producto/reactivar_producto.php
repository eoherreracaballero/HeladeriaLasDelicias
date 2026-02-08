<?php
include(__DIR__ . "/../../../app/db/conexion.php");
$id = intval($_GET['id']);

$sql = "UPDATE producto SET Estado = 'Activo' WHERE ID_Producto = $id";
if ($conexion->query($sql)) {
    header("Location: ../productos.php?msg=reactivated");
} else {
    echo "Error: " . $conexion->error;
}
?>