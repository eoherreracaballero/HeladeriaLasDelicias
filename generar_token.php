<?php
session_start();
require_once 'app/db/conexion.php'; 

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email)) {
    header("Location: recuperar_paso1.php?error=vacio");
    exit;
}

// 1. Verificar si el email existe
$stmt = $conexion->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // PRÁCTICA DE SEGURIDAD: No revelar si el usuario existe o no
    header("Location: recuperar_paso1.php?mensaje=enviado");
    exit; 
}

// 2. Generar token seguro y tiempo de expiración (Ej: 1 hora)
$token = bin2hex(random_bytes(32)); 
$expira = date("Y-m-d H:i:s", time() + 3600); // Token válido por 1 hora

// 3. Almacenar/Actualizar el token en la tabla password_resets
$sql = "REPLACE INTO password_resets (email, token, expira) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sss", $email, $token, $expira);
$stmt->execute();
$stmt->close();

// 4. SIMULACIÓN DE ENVÍO DE CORREO (CRÍTICO)
// En un sistema real, aquí enviarías un correo con un enlace como:
// $enlace = "http://tudominio.com/resetear_paso2.php?token=" . $token . "&email=" . urlencode($email);

// Por ahora, redirigimos como si se hubiera enviado
header("Location: recuperar_paso1.php?mensaje=enviado");
exit;