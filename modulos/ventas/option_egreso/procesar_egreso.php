<?php
session_start();

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtener datos del formulario
    $tipoEgreso    = trim($_POST['tipo_egreso'] ?? '');
    $clienteId     = intval($_POST['cliente'] ?? 0);
    $bodegaGeneral = intval($_POST['bodega'] ?? 0);

    // Función para limpiar valores numéricos
    function limpiarNumero($valor) {
        return floatval(str_replace([',', '$', ' '], '', $valor));
    }

    $subtotal = limpiarNumero($_POST['subtotal'] ?? 0);
    $iva      = limpiarNumero($_POST['IVA'] ?? 0);
    $total    = limpiarNumero($_POST['total'] ?? 0);

    $productos  = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $pvps       = $_POST['pvp'] ?? [];
    $bodegas    = $_POST['bodega_id'] ?? [];

    // Validación básica
    if (empty($tipoEgreso) || $clienteId <= 0 || $bodegaGeneral <= 0 || count($productos) == 0) {
        die("❌ Datos incompletos para registrar el egreso.");
    }

    $conexion->begin_transaction();

    try {
        // Insertar cabecera del egreso
        $stmt = $conexion->prepare("
            INSERT INTO egreso_producto 
                (Tipo_Egreso, Id_cliente, Subtotal_Egreso, IVA_Egreso, Total_Egreso, Fecha_Egreso) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("siddd", $tipoEgreso, $clienteId, $subtotal, $iva, $total);
        $stmt->execute();
        $egresoId = $conexion->insert_id;
        $stmt->close();

        // Preparar inserciones de detalle y actualización de inventario
        $stmtDetalle = $conexion->prepare("
            INSERT INTO detalle_egreso 
                (Id_Egreso, ID_producto, Cantidad, ID_Bodega, PVP) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmtInventario = $conexion->prepare("
            UPDATE inventario 
            SET stock = stock - ? 
            WHERE ID_Producto = ? AND ID_Bodega = ?
        ");

        for ($i = 0; $i < count($productos); $i++) {
            $idProducto  = intval($productos[$i]);
            $cantidad    = floatval($cantidades[$i] ?? 0);
            $pvp         = limpiarNumero($pvps[$i] ?? 0);
            $idBodega    = isset($bodegas[$i]) && intval($bodegas[$i]) > 0 
                            ? intval($bodegas[$i]) 
                            : $bodegaGeneral;

            if ($idProducto <= 0 || $cantidad <= 0 || $pvp <= 0 || $idBodega <= 0) {
                continue; // Ignorar filas inválidas
            }

            // Insertar detalle
            $stmtDetalle->bind_param("iiiid", $egresoId, $idProducto, $cantidad, $idBodega, $pvp);
            $stmtDetalle->execute();

            // Actualizar inventario (restar stock)
            $stmtInventario->bind_param("dii", $cantidad, $idProducto, $idBodega);
            $stmtInventario->execute();
        }

        $stmtDetalle->close();
        $stmtInventario->close();

        $conexion->commit();

        header("Location: ../facturacion.php?mensaje=" . urlencode("✅ Egreso registrado con éxito. ID: $egresoId"));
        exit;

    } catch (Exception $e) {
        $conexion->rollback();
        die("❌ Error al guardar egreso: " . $e->getMessage());
    }

    $conexion->close();
}
?>
