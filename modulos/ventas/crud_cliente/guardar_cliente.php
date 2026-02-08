<?php
include(__DIR__ . "/../../../app/db/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Captura de datos y limpieza básica
    $identificacion = mysqli_real_escape_string($conexion, $_POST['Identificacion']);
    $nombre         = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo         = mysqli_real_escape_string($conexion, $_POST['correo']);
    $ciudad         = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $direccion      = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $telefono       = mysqli_real_escape_string($conexion, $_POST['telefono']);
    
    // El estado por defecto siempre será 'Activo' al registrar
    $estado         = "Activo";

    // 2. Preparar la consulta SQL
    // Asegúrate de que los nombres de las columnas coincidan EXACTAMENTE con tu tabla SQL
    $sql = "INSERT INTO cliente (No_NIT, Nombre_Cliente, Email, Ciudad, Direccion, No_Telefono, Estado) 
            VALUES ('$identificacion', '$nombre', '$correo', '$ciudad', '$direccion', '$telefono', '$estado')";

    // 3. Ejecutar y redireccionar
    if ($conexion->query($sql)) {
        // Redirige con éxito
        header("Location: ../clientes.php?success=1");
    } else {
        // En caso de error, muestra qué pasó (puedes quitar esto en producción)
        echo "Error al guardar: " . $conexion->error;
    }
}

mysqli_close($conexion);
?>