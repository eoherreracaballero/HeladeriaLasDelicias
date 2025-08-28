<?php
include("../../../app/db/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $perfil_id = intval($_POST['perfil_id']);
    $permisos = $_POST['permisos'] ?? [];

    // Primero eliminar permisos anteriores
    $stmt_del = $conexion->prepare("DELETE FROM perfil_permiso WHERE id_perfil = ?");
    $stmt_del->bind_param("i", $perfil_id);
    $stmt_del->execute();
    $stmt_del->close();

    // Insertar permisos seleccionados
    if (!empty($permisos)) {
        $stmt_ins = $conexion->prepare("INSERT INTO perfil_permiso (id_perfil, id_permiso) VALUES (?, ?)");
        foreach ($permisos as $permiso_id) {
            $pid = intval($permiso_id);
            $stmt_ins->bind_param("ii", $perfil_id, $pid);
            $stmt_ins->execute();
        }
        $stmt_ins->close();
    }

    header("Location: ../permisos.php?perfil_id=$perfil_id");
    exit();
}
?>
