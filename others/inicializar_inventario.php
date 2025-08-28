<?php
include '../db/conexion.php';

$sql = "SELECT ID_Producto, ID_Bodega, Stock FROM producto WHERE Stock > 0";

$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $idProducto = $row['ID_Producto'];
        $idBodega = $row['ID_Bodega'];
        $stock = $row['Stock'];

        // Verifica si ya existe para evitar duplicados
        $check = $conexion->prepare("SELECT * FROM inventario WHERE ID_Producto = ? AND ID_Bodega = ?");
        $check->bind_param("ii", $idProducto, $idBodega);
        $check->execute();
        $resCheck = $check->get_result();

        if ($resCheck->num_rows == 0) {
            $insert = $conexion->prepare("INSERT INTO inventario (ID_Producto, ID_Bodega, Stock) VALUES (?, ?, ?)");
            $insert->bind_param("iid", $idProducto, $idBodega, $stock);
            $insert->execute();
            $insert->close();
        }
        $check->close();
    }
    echo "Inventario inicializado correctamente.";
} else {
    echo "No hay productos con stock para inicializar.";
}

$conexion->close();
?>
