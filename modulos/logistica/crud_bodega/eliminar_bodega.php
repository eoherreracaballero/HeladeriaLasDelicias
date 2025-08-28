<?php
include("../db/conexion.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_bodega = intval($_GET['id']);

    // Verificar si la bodega existe antes de eliminar
    $consulta = $conexion->prepare("SELECT * FROM bodega WHERE Id_Bodega = ?");
    $consulta->bind_param("i", $id_bodega);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        // Intentar eliminar
        $eliminar = $conexion->prepare("DELETE FROM bodega WHERE Id_Bodega = ?");
        $eliminar->bind_param("i", $id_bodega);

        if ($eliminar->execute()) {
            header("Location: bodegas.php?mensaje=eliminado");
            exit();
        } else {
            echo "<div class='alert alert-danger'>❌ Error al eliminar la bodega: " . $eliminar->error . "</div>";
        }

        $eliminar->close();
    } else {
        echo "<div class='alert alert-warning'>⚠️ Bodega no encontrada.</div>";
    }

    $consulta->close();
} else {
    echo "<div class='alert alert-danger'>❌ ID de bodega no válido.</div>";
}

$conexion->close();
?>
