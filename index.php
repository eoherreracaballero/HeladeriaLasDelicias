<?php
session_start();
$error = $_GET['error'] ?? '';
unset($_SESSION['error']); // limpiar error después de mostrar
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ingreso al Sistema - El Palacio de las Delicias</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-image: url('public/img/fondo-heladeria.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
    .login-container {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      backdrop-filter: blur(6px);
      padding: 20px;
    }
    .login-box {
      background: rgba(255, 255, 255, 0.96);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 400px;
    }
    .login-box img { width: 80px; margin-bottom: 20px; }
    .btn-login { background-color: rgb(44, 30, 248); color: white; }
    .btn-login:hover { background-color: rgb(27, 71, 247); }
    @media (max-width: 576px) {
      .login-box { padding: 20px; }
      .login-box img { width: 70px; }
      h3 { font-size: 1.3rem; }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-box text-center">
      <img src="public/img/logo.png" alt="Logo" />
      <h3 class="mb-4 text-primary">Sistema de Gestión de Inventarios</h3>

      <!-- Mostrar errores -->
      <?php if ($error === 'credenciales_invalidas'): ?>
        <div class="alert alert-danger">Usuario o contraseña incorrectos.</div>
      <?php elseif ($error === 'campos_vacios'): ?>
        <div class="alert alert-warning">Por favor, complete todos los campos.</div>
      <?php elseif ($error === 'perfil_no_valido'): ?>
        <div class="alert alert-danger">Perfil de usuario no válido.</div>
      <?php elseif ($error === 'acceso_denegado'): ?>
        <div class="alert alert-danger">No tiene permisos para acceder a esta sección.</div>
      <?php endif; ?>
      

      <form action="login.php" method="POST">
        <div class="mb-3 text-start">
          <label for="usuario" class="form-label">Usuario o Email</label>
          <input type="text" class="form-control" name="no_identificacion" placeholder="Identificación o Email" required />
        </div>
        <div class="mb-4 text-start">
          <label for="clave" class="form-label">Contraseña</label>
          <input type="password" class="form-control" name="contrasena" required />
        </div>
        <button type="submit" class="btn btn-login w-100">Ingresar</button>
      </form>

      <p class="mt-4 text-muted small">
        © 2025 El Palacio de las Delicias<br>
        Desarrollado por Edward Herrera y Alejandra Palacios - ADSO 2377388
      </p>
    </div>
  </div>
</body>
</html>
