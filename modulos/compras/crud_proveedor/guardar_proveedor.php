<?php
include(__DIR__ . "/../../../app/db/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nit       = mysqli_real_escape_string($conexion, $_POST['identificacion']);
    $nombre    = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $ciudad    = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $asesor    = mysqli_real_escape_string($conexion, $_POST['asesor']);
    $productos = mysqli_real_escape_string($conexion, $_POST['productos']);
    $estado    = "Activo";

    // Verificar NIT duplicado
    $check = $conexion->query("SELECT No_NIT FROM proveedor WHERE No_NIT = '$nit'");
    if ($check->num_rows > 0) {
        header("Location: ../proveedores.php?error=existe");
        exit;
    }

    $sql = "INSERT INTO proveedor (No_NIT, Nombre_Proveedor, Ciudad, Direccion, Tel_Contacto, Asesor_Contacto, Productos_Venta, Estado) 
            VALUES ('$nit', '$nombre', '$ciudad', '$direccion', '$telefono', '$asesor', '$productos', '$estado')";

    if ($conexion->query($sql)) {
        header("Location: ../proveedores.php?msg=success");
    } else {
        echo "Error: " . $conexion->error;
    }
}
mysqli_close($conexion);
