<?php
require_once __DIR__ . "/../../../app/db/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre']);
    $ubicacion = trim($_POST['ubicacion']);
    $estado    = trim($_POST['estado']);

    $sql  = "INSERT INTO bodega (Nombre_Bodega, Ubicacion, Estado) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $nombre, $ubicacion, $estado);

    if ($stmt->execute()) {
        header("Location: ../bodegas.php?mensaje=ok");
        exit;
    }
}
?>