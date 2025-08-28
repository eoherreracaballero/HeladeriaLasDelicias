<?php
ob_start();
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['ID_Producto']) ? intval($_GET['ID_Producto']) : 0;

if ($id > 0) {

    // Eliminar producto
    $eliminar = mysqli_query($conexion, "DELETE FROM producto WHERE ID_Producto = $id");

    if ($eliminar) {
        header("Location: ../productos.php?msg=deleted");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Error al eliminar: " . mysqli_error($conexion) . "</div>";
    }
} else {
    echo "<div class='alert alert-warning'>⚠️ ID inválido.</div>";
}
?>
