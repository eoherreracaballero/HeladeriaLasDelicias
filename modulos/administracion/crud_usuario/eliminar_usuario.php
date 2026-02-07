<?php
ob_start();
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // En lugar de DELETE, actualizamos el campo Estado
    // Usamos 'Suspendido' para inhabilitar al usuario preservando sus datos
    $sql = "UPDATE usuario SET Estado = 'Suspendido' WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../usuarios.php?msg=suspended");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Error al suspender: " . $conexion->error . "</div>";
    }
    $stmt->close();
} else {
    echo "<div class='alert alert-warning'>⚠️ ID inválido.</div>";
}

mysqli_close($conexion);
ob_end_flush();
?>
