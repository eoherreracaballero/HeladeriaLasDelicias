<?php
include(__DIR__ . "/../../../app/db/conexion.php");

// Datos de Producto
$nombre = $_POST['nombre'];
$marca = $_POST['marca'];
$empaque = $_POST['und_empaque'];
$costo = $_POST['costo_unitario'];
$pvp = $_POST['pvp'];
$prov = $_POST['id_proveedor'];
$estado = $_POST['estado'];

// Datos de Inventario
$bodega = $_POST['id_bodega'];
$min = $_POST['stock_min'];
$max = $_POST['stock_max'];

// 1. Insertar en tabla PRODUCTO
$sql1 = "INSERT INTO producto (Nombre_Producto, Marca, Und_Empaque, Costo_Unitario, PVP, ID_Proveedor, Estado) 
         VALUES ('$nombre', '$marca', '$empaque', $costo, $pvp, $prov, '$estado')";

if ($conexion->query($sql1)) {
    $nuevo_id = $conexion->insert_id;

    // 2. Insertar en tabla INVENTARIO (Stock inicial 0 por regla de negocio)
    $sql2 = "INSERT INTO inventario (ID_Producto, ID_Bodega, Stock, Costo_promedio, Stock_Minimo, Stock_Optimo, Fecha_Actualizacion) 
             VALUES ($nuevo_id, $bodega, 0, $costo, $min, $max, NOW())";
    
    $conexion->query($sql2);
    header("Location: ../productos.php?msg=success");
} else {
    echo "Error: " . $conexion->error;
}
?>