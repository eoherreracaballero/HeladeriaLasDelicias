<?php

// Iniciar sesión si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php?error=acceso_denegado");
    exit;
}

// Función para verificar permisos
function verificar_perfil(array $perfiles_permitidos) {
    if (!in_array($_SESSION['perfil_id'], $perfiles_permitidos)) {
        header("Location: ../../index.php?error=acceso_denegado");
        exit;
    }
}
?>
