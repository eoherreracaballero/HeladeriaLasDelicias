<?php
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "UPDATE bodega SET Estado = 'Activa' WHERE Id_Bodega = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: ../bodegas.php?mensaje=ok");
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>