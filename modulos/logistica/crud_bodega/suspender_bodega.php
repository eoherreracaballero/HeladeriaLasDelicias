<?php
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // CAMBIO: Usamos 'Inactiva' para que coincida con la consulta de bodegas.php
    $sql = "UPDATE bodega SET Estado = 'Inactiva' WHERE Id_Bodega = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: ../bodegas.php?mensaje=suspendido");
        exit();
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>