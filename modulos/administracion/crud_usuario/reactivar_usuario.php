<?php
ob_start();
// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Obtener el ID desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Cambiamos el estado a 'Activo' para restaurar el acceso
    $sql = "UPDATE usuario SET Estado = 'Activo' WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir con mensaje de éxito
        header("Location: ../usuarios.php?msg=reactivated");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Error al reactivar: " . $conexion->error . "</div>";
    }
    $stmt->close();
} else {
    echo "<div class='alert alert-warning'>⚠️ ID inválido proporcionado.</div>";
}

mysqli_close($conexion);
ob_end_flush();
?>