<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Redirigir al index
header("Location: /Heladeria/index.php");
exit;
?>
