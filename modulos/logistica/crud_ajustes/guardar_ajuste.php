<?php
include(__DIR__ . "/../../../app/db/conexion.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura de datos de cabecera
    $tipo_ajuste = $_POST['tipo_ajuste']; // ENTRADA o SALIDA
    $fecha = $_POST['fecha'];
    $referencia = mysqli_real_escape_string($conexion, $_POST['referencia']);
    $usuario = $_SESSION['ID_Usuario'] ?? 1;

    // Captura de arreglos del detalle
    $productos = $_POST['id_producto'];
    $bodegas = $_POST['id_bodega'];
    $cantidades = $_POST['cantidad'];

    // Iniciar Transacción
    $conexion->begin_transaction();

    try {
        // 1. Crear la Nota de Ajuste (Cabecera)
        $stmt_nota = $conexion->prepare("INSERT INTO nota_ajuste (tipo_nota, fecha_nota, usuario_id, total) VALUES (?, ?, ?, 0)");
        $nota_tipo_str = "AJUSTE_" . $tipo_ajuste;
        $stmt_nota->bind_param("ssi", $nota_tipo_str, $fecha, $usuario);
        $stmt_nota->execute();
        $id_nota = $conexion->insert_id;

        // 2. Procesar cada fila del detalle
        foreach ($productos as $index => $id_p) {
            $id_b = intval($bodegas[$index]);
            $cant = floatval($cantidades[$index]);
            
            // A. Obtener datos actuales del producto (Stock y Costo)
            $res_p = $conexion->query("SELECT Stock, Costo_Unitario FROM producto WHERE ID_Producto = $id_p");
            $data_p = $res_p->fetch_assoc();
            $stock_anterior = floatval($data_p['Stock']);
            $costo = floatval($data_p['Costo_Unitario']);

            // B. Calcular nuevo stock
            $nuevo_stock = ($tipo_ajuste == 'ENTRADA') ? ($stock_anterior + $cant) : ($stock_anterior - $cant);

            // C. Insertar en movimiento_inventario
            $stmt_inv = $conexion->prepare("INSERT INTO movimiento_inventario (ID_Producto, ID_Bodega_Destino, Tipo_Movimiento, Cantidad, Fecha_Movimiento, Motivo, ID_Usuario) VALUES (?, ?, 'Ajuste', ?, ?, ?, ?)");
            $stmt_inv->bind_param("iidssi", $id_p, $id_b, $cant, $fecha, $referencia, $usuario);
            $stmt_inv->execute();

            // D. Insertar en movimiento_kardex
            $c_ent = ($tipo_ajuste == 'ENTRADA') ? $cant : 0;
            $c_sal = ($tipo_ajuste == 'SALIDA') ? $cant : 0;
            
            $stmt_kar = $conexion->prepare("INSERT INTO movimiento_kardex (ID_Producto, ID_Bodega, Fecha_Movimiento, Tipo_Documento, ID_Documento, Tipo_Movimiento, Cantidad_Entrada, Costo_Entrada, Cantidad_Salida, Costo_Salida, Stock_Final, Costo_Promedio_Final) VALUES (?, ?, ?, 'NOTA_AJUSTE', ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_kar->bind_param("iissidddddd", $id_p, $id_b, $fecha, $id_nota, $tipo_ajuste, $c_ent, $costo, $c_sal, $costo, $nuevo_stock, $costo);
            $stmt_kar->execute();

            // E. Actualizar el stock maestro en la tabla producto
            $stmt_upd = $conexion->prepare("UPDATE producto SET Stock = ? WHERE ID_Producto = ?");
            $stmt_upd->bind_param("di", $nuevo_stock, $id_p);
            $stmt_upd->execute();
        }

        $conexion->commit();
        header("Location: ../ajustes.php?msg=Ajuste procesado correctamente con el folio #" . $id_nota);

    } catch (Exception $e) {
        $conexion->rollback();
        header("Location: ../ajustes.php?msg=Error: " . urlencode($e->getMessage()));
    }
}
?>