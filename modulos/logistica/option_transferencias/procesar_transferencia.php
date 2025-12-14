<?php
session_start();
// Asegúrate de que la ruta de conexión sea correcta desde /modulos/logistica/option_transferencias/
require_once "../../../app/db/conexion.php";

// Definir la URL de redirección para el formulario (transferencia_bodegas.php)
$URL_FORMULARIO = "../transferencia_bodegas.php"; 
global $conexion; // Aseguramos que $conexion sea global

// --- Funciones de Redirección ---
function redireccionar_con_error($mensaje) {
    global $conexion, $URL_FORMULARIO;
    if (isset($conexion) && $conexion->in_transaction) {
        $conexion->rollback();
    }
    if (isset($conexion)) {
        $conexion->close();
    }
    header("Location: " . $URL_FORMULARIO . "?error=" . urlencode($mensaje));
    exit();
}

function redireccionar_con_exito($mensaje) {
    global $conexion, $URL_FORMULARIO;
    if (isset($conexion)) {
        $conexion->close();
    }
    header("Location: " . $URL_FORMULARIO . "?msg=" . urlencode($mensaje));
    exit();
}
// ---------------------------------


// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    redireccionar_con_error("Método de solicitud no válido.");
}

// 1. Recibir y sanear datos
$productoId = intval($_POST['producto_id'] ?? 0);
$origenId = intval($_POST['bodega_origen_id'] ?? 0);
$destinoId = intval($_POST['bodega_destino_id'] ?? 0);
$cantidad = intval($_POST['cantidad'] ?? 0);
$motivo = trim($_POST['motivo'] ?? 'Transferencia interna');
// ID del usuario que realiza la transferencia
$usuarioId = $_SESSION['id_usuario'] ?? 1; 

if ($productoId <= 0 || $origenId <= 0 || $destinoId <= 0 || $cantidad <= 0) {
    redireccionar_con_error("Datos incompletos o inválidos.");
}
if ($origenId === $destinoId) {
    redireccionar_con_error("Las bodegas de origen y destino deben ser diferentes.");
}

// 2. Definir la fecha actual para los movimientos
$fechaMovimiento = date('Y-m-d H:i:s');


// =======================================================
// INICIO DE LA TRANSACCIÓN
// =======================================================
$conexion->begin_transaction();

try {
    // A. COMPROBACIÓN DE STOCK EN DB (Bloqueo de fila FOR UPDATE)
    $sqlStockCheck = "SELECT Stock FROM inventario WHERE ID_Producto = ? AND Id_Bodega = ? FOR UPDATE";
    $stmtCheck = $conexion->prepare($sqlStockCheck);
    $stmtCheck->bind_param("ii", $productoId, $origenId);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    $stockActual = $result->fetch_assoc()['Stock'] ?? 0;
    $stmtCheck->close();

    if ($cantidad > $stockActual) {
        throw new Exception("Stock insuficiente en la bodega de origen. Disponible: " . $stockActual);
    }
    
    // ----------------------------------------------------------------------
    // B. ACTUALIZACIÓN EN TABLA INVENTARIO (FÍSICO)
    // ----------------------------------------------------------------------

    // 1. REDUCIR STOCK EN LA BODEGA DE ORIGEN
    $sqlOrigen = "UPDATE inventario SET Stock = Stock - ? WHERE ID_Producto = ? AND Id_Bodega = ?";
    $stmtOrigen = $conexion->prepare($sqlOrigen);
    $stmtOrigen->bind_param("dii", $cantidad, $productoId, $origenId);
    
    if (!$stmtOrigen->execute() || $stmtOrigen->affected_rows === 0) {
        throw new Exception("Error al reducir stock de origen. Producto no encontrado en esa bodega.");
    }
    $stmtOrigen->close();
    
    // 2. AUMENTAR STOCK EN LA BODEGA DE DESTINO (UPSERT)
    $sqlDestinoUpdate = "UPDATE inventario SET Stock = Stock + ? WHERE ID_Producto = ? AND Id_Bodega = ?";
    $stmtDestinoUpdate = $conexion->prepare($sqlDestinoUpdate);
    $stmtDestinoUpdate->bind_param("dii", $cantidad, $productoId, $destinoId);
    $stmtDestinoUpdate->execute();
    $filasAfectadas = $stmtDestinoUpdate->affected_rows;
    $stmtDestinoUpdate->close();

    if ($filasAfectadas === 0) {
        $sqlDestinoInsert = "INSERT INTO inventario (ID_Producto, Id_Bodega, Stock) VALUES (?, ?, ?)";
        $stmtDestinoInsert = $conexion->prepare($sqlDestinoInsert);
        $stmtDestinoInsert->bind_param("iid", $productoId, $destinoId, $cantidad);
        if (!$stmtDestinoInsert->execute()) {
             throw new Exception("Fallo al crear el registro de stock en la bodega de destino.");
        }
        $stmtDestinoInsert->close();
    }
    
    // ----------------------------------------------------------------------
    // C. REGISTRO EN TABLA MOVIMIENTO_INVENTARIO (AUDITORÍA)
    // ----------------------------------------------------------------------
    $sqlMovimiento = "
        INSERT INTO movimiento_inventario 
        (ID_Producto, ID_Bodega_Origen, ID_Bodega_Destino, Tipo_Movimiento, Cantidad, Fecha_Movimiento, Motivo, ID_Usuario) 
        VALUES (?, ?, ?, 'Transferencia', ?, ?, ?, ?)
    ";
    $stmtMovimiento = $conexion->prepare($sqlMovimiento);

    if (!$stmtMovimiento) {
         throw new Exception("Error al preparar el registro de movimiento: " . $conexion->error);
    }

    // El ID_Bodega_Destino será NULL en la salida, y ID_Bodega_Origen será NULL en la entrada.

    // 3. REGISTRAR SALIDA (Bodega de Origen)
    $cantidadSalida = -$cantidad; // Se registra la salida como un valor negativo
    $stmtMovimiento->bind_param("iiidsis", 
        $productoId, 
        $origenId, 
        $destinoId, // Se mantiene el destino para vincular el registro
        $cantidadSalida,
        $fechaMovimiento,
        $motivo,
        $usuarioId
    );
    if (!$stmtMovimiento->execute()) {
        throw new Exception("Fallo al registrar la salida de la transferencia.");
    }

    // 4. REGISTRAR ENTRADA (Bodega de Destino)
    $cantidadEntrada = $cantidad; // Se registra la entrada como un valor positivo
    $stmtMovimiento->bind_param("iiidsis", 
        $productoId, 
        $origenId, // Se mantiene el origen para vincular el registro
        $destinoId, 
        $cantidadEntrada,
        $fechaMovimiento,
        $motivo,
        $usuarioId
    );
    if (!$stmtMovimiento->execute()) {
        throw new Exception("Fallo al registrar la entrada de la transferencia.");
    }
    $stmtMovimiento->close();


    // 5. FINALIZAR TRANSACCIÓN Y REDIRECCIONAR
    $conexion->commit();
    redireccionar_con_exito("Transferencia de $cantidad unidades completada con éxito.");

} catch (Exception $e) {
    // 6. MANEJO DE ERRORES: Revertir la transacción y redirigir
    redireccionar_con_error("Fallo en la transferencia: " . $e->getMessage());
}
?>