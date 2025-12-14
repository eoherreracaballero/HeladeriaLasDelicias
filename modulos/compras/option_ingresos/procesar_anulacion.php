<?php
session_start();

// Cargar conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// 1. Obtener y validar el ID y el motivo
$idIngreso = isset($_POST['id_ingreso']) ? intval($_POST['id_ingreso']) : 0;
$motivo = isset($_POST['motivo_anulacion']) ? trim($_POST['motivo_anulacion']) : '';

if ($idIngreso <= 0 || empty($motivo)) {
    header("Location: ../anulaciones.php?error=Datos de anulación incompletos o inválidos.");
    exit;
}

// 2. Iniciar la Transacción
$conexion->begin_transaction();
$exito = true;
$mensaje = "El Ingreso #{$idIngreso} ha sido anulado con éxito, y el inventario se ha revertido.";

try {
    // A. Obtener el detalle del ingreso a anular (para saber qué restar del inventario)
    $sqlDetalle = "SELECT ID_Producto, ID_Bodega, Cantidad, Costo_Unitario FROM detalle_ingreso WHERE ID_Ingreso = ?";
    $stmtDetalle = $conexion->prepare($sqlDetalle);
    if (!$stmtDetalle) throw new Exception("Error al preparar consulta de detalle: " . $conexion->error);

    $stmtDetalle->bind_param("i", $idIngreso);
    $stmtDetalle->execute();
    $resultDetalle = $stmtDetalle->get_result();

    if ($resultDetalle->num_rows == 0) {
        throw new Exception("No se encontraron detalles para el Ingreso #{$idIngreso}. Anulación fallida.");
    }
    
    // B. Preparar Sentencias para Inventario (usaremos las mismas para todos los detalles)
    
    // Consulta para obtener stock y Costo Promedio actual, y BLOQUEAR la fila
    $sqlGetInventario = "SELECT stock, Costo_Promedio FROM inventario WHERE ID_Producto = ? AND ID_Bodega = ? FOR UPDATE";
    $stmtGetInventario = $conexion->prepare($sqlGetInventario);

    // Consulta para actualizar Stock y Costo Promedio
    // El Costo_Promedio se mantiene igual si el stock es > 0, o se resetea a 0 si el stock es 0.
    $sqlUpdateInventario = "UPDATE inventario SET stock = ?, Costo_Promedio = ? WHERE ID_Producto = ? AND ID_Bodega = ?";
    $stmtUpdateInventario = $conexion->prepare($sqlUpdateInventario);
    
    if (!$stmtGetInventario || !$stmtUpdateInventario) {
        throw new Exception("Error al preparar sentencias de inventario.");
    }

    // C. Procesar cada detalle para revertir el inventario
    while ($fila = $resultDetalle->fetch_assoc()) {
        $cantidadReversion = floatval($fila['Cantidad']);
        $costoEntrada = floatval($fila['Costo_Unitario']);
        $idProducto = intval($fila['ID_Producto']);
        $idBodega = intval($fila['ID_Bodega']);

        // 1. Obtener datos actuales del inventario y BLOQUEAR la fila
        $stmtGetInventario->bind_param("ii", $idProducto, $idBodega);
        $stmtGetInventario->execute();
        $resultInv = $stmtGetInventario->get_result();
        
        if ($rowInv = $resultInv->fetch_assoc()) {
            $stockActual = floatval($rowInv['stock']);
            $cppActual = floatval($rowInv['Costo_Promedio']);
        } else {
            // Si no hay registro en inventario, el producto nunca se ingresó correctamente
            throw new Exception("El producto {$idProducto} no tiene registro en inventario. Anulación Abortada."); 
        }

        // VALIDACIÓN CRÍTICA: Asegurar que la anulación no genere stock negativo (o un stock más negativo)
        if ($stockActual < $cantidadReversion) {
            // Nota: Podrías cambiar esto a solo emitir una advertencia o forzar la reversión
            // Aquí abortamos para proteger la integridad contable.
            throw new Exception("Stock insuficiente ({$stockActual}) para revertir la cantidad de {$cantidadReversion} del Producto {$idProducto}.");
        }

        // 2. Calcular nuevo Stock
        $nuevoStock = $stockActual - $cantidadReversion;
        
        // 3. Calcular Nuevo CPP (Mantener el CPP actual si aún hay stock, o resetear a 0)
        $nuevoCPP = ($nuevoStock > 0) ? $cppActual : 0.00;
        
        // 4. Actualizar inventario (Stock y CPP)
        // Parámetros: (nuevoStock, nuevoCPP, idProducto, idBodega) -> ddii
        $stmtUpdateInventario->bind_param("ddii", $nuevoStock, $nuevoCPP, $idProducto, $idBodega);
        
        if (!$stmtUpdateInventario->execute()) {
            throw new Exception("Fallo al revertir inventario para Producto {$idProducto}.");
        }
        
        // 5. Opcional: Registrar el movimiento en Kardex (Si tienes la tabla movimiento_kardex)
        /*
        $sqlKardex = "... INSERT INTO movimiento_kardex (..., Tipo_Movimiento) VALUES (..., 'EGRESO_ANULACION')...";
        // Asegúrate de registrar la salida con el CPP actual ($cppActual) como costo de egreso.
        */
    }
    
    // D. Actualizar el estado del ingreso y añadir el motivo en la tabla principal
    $sqlAnular = "UPDATE ingreso_producto SET estado = 'Anulado', motivo_anulacion = ?, fecha_anulacion = NOW() WHERE ID_Ingreso = ? AND estado != 'Anulado'";
    $stmtAnular = $conexion->prepare($sqlAnular);
    if (!$stmtAnular) throw new Exception("Error al preparar la anulación del ingreso: " . $conexion->error);
    
    $stmtAnular->bind_param("si", $motivo, $idIngreso);
    if (!$stmtAnular->execute()) {
        throw new Exception("Fallo al actualizar el estado del Ingreso #{$idIngreso}.");
    }

    // 3. Si todo es exitoso, confirmar los cambios
    $conexion->commit();
    
    // Cerrar statements
    $stmtDetalle->close();
    $stmtGetInventario->close();
    $stmtUpdateInventario->close();
    $stmtAnular->close();

} catch (Exception $e) {
    // 4. Si hay un error, revertir los cambios
    $conexion->rollback();
    $exito = false;
    $mensaje = "ERROR en la Anulación: " . $e->getMessage();
}

// 5. Cerrar conexión y redirigir
$conexion->close();

if ($exito) {
    header("Location: ../anulaciones.php?msg=" . urlencode($mensaje));
} else {
    header("Location: ../anulaciones.php?error=" . urlencode($mensaje));
}
exit;
?>