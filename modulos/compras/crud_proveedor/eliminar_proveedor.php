<?php
ob_start();
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Eliminar proveedor
    $eliminar = mysqli_query($conexion, "DELETE FROM proveedor WHERE ID_Proveedor = $id");

    if ($eliminar) {
        header("Location: ../proveedores.php?msg=deleted");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Error al eliminar: " . mysqli_error($conexion) . "</div>";
    }
} else {
    echo "<div class='alert alert-warning'>⚠️ ID inválido.</div>";
}

mysqli_close($conexion);
ob_end_flush();
?>    

