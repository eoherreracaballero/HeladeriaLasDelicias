<?php
include(__DIR__ . "/../../../app/db/conexion.php");
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conexion->prepare("UPDATE cliente SET Estado = 'Activo' WHERE Id_cliente = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: ../clientes.php?msg=Cliente reactivado");
    }
}
mysqli_close($conexion);
?>