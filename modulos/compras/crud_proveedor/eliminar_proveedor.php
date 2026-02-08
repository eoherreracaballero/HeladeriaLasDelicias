<?php
include(__DIR__ . "/../../../app/db/conexion.php");
$id = $_GET['id'] ?? 0;
if($id > 0) {
    $conexion->query("UPDATE proveedor SET Estado = 'Suspendido' WHERE ID_Proveedor = $id");
}
header("Location: ../proveedores.php?msg=suspended");   

