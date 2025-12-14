<?php
session_start();

// Cargar conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// URL del servicio de Node.js (Asignar la IP si no es localhost)
const NODE_ALERT_URL = 'http://localhost:3001/api/notify-stock';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtener datos del formulario
    $tipoEgreso         = trim($_POST['tipo_egreso'] ?? '');
    $clienteId          = intval($_POST['cliente'] ?? 0);
    $fechaEgreso        = date('Y-m-d H:i:s'); 

    // Función para limpiar valores numéricos
    function limpiarNumero($valor) {
        return floatval($valor); 
    }
    
    // ------------------------------------------------------------------
    // 🟢 RECEPCIÓN DE TOTALES Y DESCUENTO (Basado en facturacion.php)
    // ------------------------------------------------------------------
    $subtotalBruto     = limpiarNumero($_POST['subtotal_bruto'] ?? 0); 
    $descuentoAplicado = limpiarNumero($_POST['descuento_aplicado'] ?? 0); 
    
    $subtotalNeto      = $subtotalBruto - $descuentoAplicado;
    
    $iva               = limpiarNumero($_POST['IVA'] ?? 0);
    $total             = limpiarNumero($_POST['total'] ?? 0);

    $productos          = $_POST['producto_id'] ?? [];
    $cantidades         = $_POST['cantidad'] ?? [];
    $pvps               = $_POST['pvp'] ?? [];
    $bodegasDetalle     = $_POST['bodega_id'] ?? []; 

    $cmvTotal = 0;
    $alertaStockDisparada = false; // 🟢 Bandera para el mensaje final

    // Validación básica
    if (empty($tipoEgreso) || $clienteId <= 0 || count($productos) == 0) {
        header("Location: ../facturacion.php?error=" . urlencode("❌ Datos incompletos para registrar el egreso (tipo, cliente o productos)."));
        exit;
    }

    // --- INICIO DE TRANSACCIÓN ---
    $conexion->begin_transaction();

    try {
        // 1. Insertar cabecera del egreso (Incluye Descuento_Aplicado)
        $stmtEgreso = $conexion->prepare("
             INSERT INTO egreso_producto 
                 (Tipo_Egreso, Id_cliente, Subtotal_Egreso, Descuento_Aplicado, IVA_Egreso, Total_Egreso, Costo_Mercancia_Vendida, Fecha_Egreso) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $cmvPlaceholder = 0.00; 
        
        $stmtEgreso->bind_param("siddddd", 
            $tipoEgreso, 
            $clienteId, 
            $subtotalNeto, 
            $descuentoAplicado, // Valor del descuento
            $iva, 
            $total, 
            $cmvPlaceholder
        );
        
        $stmtEgreso->execute();
        $egresoId = $conexion->insert_id;
        $stmtEgreso->close();
        
        // 2. Preparar consultas para el bucle (Fuera del bucle)
        
        // 🟢 CRÍTICO: Consulta que obtiene Stock_Minimo, Nombre_Producto y Nombre_Bodega
        $sqlGetInventario = "
            SELECT i.stock, i.Costo_Promedio, p.Stock_Minimo, p.Nombre_Producto, b.Nombre_Bodega
            FROM inventario i
            JOIN producto p ON i.ID_Producto = p.ID_Producto
            JOIN bodega b ON i.Id_Bodega = b.Id_Bodega
            WHERE i.ID_Producto = ? AND i.Id_Bodega = ? FOR UPDATE
        ";
        $stmtGetInventario = $conexion->prepare($sqlGetInventario);
        
        // Resto de sentencias (Update Inventario, Detalle Egreso, Kardex)
        $sqlUpdateInventario = "UPDATE inventario SET stock = stock - ? WHERE ID_Producto = ? AND ID_Bodega = ?";
        $stmtUpdateInventario = $conexion->prepare($sqlUpdateInventario);
        $sqlDetalle = "INSERT INTO detalle_egreso (Id_Egreso, ID_producto, Cantidad, ID_Bodega, PVP, Costo_Unitario) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtDetalle = $conexion->prepare($sqlDetalle);
        $sqlKardex = "INSERT INTO movimiento_kardex (
            ID_Producto, ID_Bodega, Fecha_Movimiento, Tipo_Documento, ID_Documento, Tipo_Movimiento,
            Cantidad_Salida, Costo_Salida, Stock_Final, Costo_Promedio_Final
        ) VALUES (?, ?, ?, ?, ?, 'SALIDA', ?, ?, ?, ?)";
        $stmtKardex = $conexion->prepare($sqlKardex);
        
        // 2.1. Validación de Sentencias
        if (!$stmtGetInventario || !$stmtUpdateInventario || !$stmtDetalle || !$stmtKardex) {
             throw new Exception("Error al preparar las consultas SQL. Revise la sintaxis de las tablas: " . $conexion->error);
        }
        
        // 3. Procesar cada ítem de la venta
        for ($i = 0; $i < count($productos); $i++) {
            $idProducto  = intval($productos[$i]);
            $cantidad    = floatval($cantidades[$i] ?? 0);
            $pvp         = limpiarNumero($pvps[$i] ?? 0);
            $idBodega    = intval($bodegasDetalle[$i] ?? 0); 
            
            if ($idProducto <= 0 || $cantidad <= 0 || $pvp <= 0 || $idBodega <= 0) {
                 throw new Exception("Datos de ítem inválidos en la fila #".($i+1));
            }

            // --- A. VALIDACIÓN Y OBTENCIÓN DE DATOS (Bloqueo de fila) ---
            $stmtGetInventario->bind_param("ii", $idProducto, $idBodega);
            $stmtGetInventario->execute();
            $resultInv = $stmtGetInventario->get_result();
            
            if ($rowInv = $resultInv->fetch_assoc()) {
                $stockActual = floatval($rowInv['stock']);
                $cppProducto = floatval($rowInv['Costo_Promedio']);
                $stockMinimo = floatval($rowInv['Stock_Minimo'] ?? 0); 
                $nombreProducto = $rowInv['Nombre_Producto'] ?? 'Producto Desconocido';
                $nombreBodega = $rowInv['Nombre_Bodega'] ?? 'Bodega Desconocida';
            } else {
                throw new Exception("El Producto ID {$idProducto} no tiene registro de inventario en Bodega {$idBodega}.");
            }

            // A.1. Validación de stock
            if ($stockActual < $cantidad) {
                throw new Exception("Stock insuficiente. Solo hay {$stockActual} unidades disponibles del Producto ID {$idProducto} en Bodega {$idBodega}.");
            }
            
            // A.2. Cálculo de CMV y Stock Final
            $costoUnitarioVenta = $cppProducto;
            $cmvItem = $cantidad * $costoUnitarioVenta;
            $cmvTotal += $cmvItem; 
            $stockDespuesVenta = $stockActual - $cantidad;

            // ---------------------------------------------------------------------
            // 🟢 INTEGRACIÓN NODE.JS: Disparar Alerta Asíncrona si stock es bajo
            // ---------------------------------------------------------------------
            if ($stockDespuesVenta < $stockMinimo) {
                
                $notificationData = [
                    'productoNombre' => $nombreProducto,
                    'bodegaNombre' => $nombreBodega, 
                    'stockActual' => $stockDespuesVenta,
                    'stockMinimo' => $stockMinimo
                ];
                
                $jsonPayload = json_encode($notificationData);
                
                // Configuración cURL para la llamada ASÍNCRONA
                $ch = curl_init(NODE_ALERT_URL);
                
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                
                // CRÍTICO: Configuración para llamada asíncrona rápida
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500); 
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true); 
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonPayload)
                ]);
                
                curl_exec($ch); 
                curl_close($ch);
                
                // Levantar bandera de alerta para el mensaje final
                $alertaStockDisparada = true; 
            }
            // ---------------------------------------------------------------------

            // B.1. Insertar detalle
            $stmtDetalle->bind_param("iiiddd", $egresoId, $idProducto, $cantidad, $idBodega, $pvp, $costoUnitarioVenta);
            $stmtDetalle->execute();

            // INTEGRACIÓN KARDEX y B.2. Actualizar inventario (restar stock)
            $stockFinal = $stockActual - $cantidad;
            $costoPromedioFinal = $cppProducto; 
            $tipoDocumento = $tipoEgreso; 
            
            $stmtKardex->bind_param(
                "iisiddddd", $idProducto, $idBodega, $fechaEgreso, $tipoDocumento, $egresoId, 
                $cantidad, $costoUnitarioVenta, $stockFinal, $costoPromedioFinal
            );
            if (!$stmtKardex->execute()) {
                throw new Exception("Error al registrar movimiento de KARDEX (SALIDA) para producto {$idProducto}: " . $stmtKardex->error);
            }
            $stmtUpdateInventario->bind_param("dii", $cantidad, $idProducto, $idBodega);
            $stmtUpdateInventario->execute();
        } // Fin del bucle for

        // 4. Actualizar la cabecera del egreso con el CMV Total
        $stmtUpdateCMV = $conexion->prepare("
            UPDATE egreso_producto SET Costo_Mercancia_Vendida = ? WHERE ID_Egreso = ?
        ");
        $stmtUpdateCMV->bind_param("di", $cmvTotal, $egresoId);
        $stmtUpdateCMV->execute();
        $stmtUpdateCMV->close();
        
        // 5. Commit y Cierre
        $conexion->commit();

        // 🟢 MENSAJE FINAL CON ALERTA DE STOCK
        $mensajeFinal = "✅ Egreso registrado con éxito. ID: $egresoId. CMV Total: " . number_format($cmvTotal, 2);
        if ($alertaStockDisparada) {
            $mensajeFinal .= " | ⚠️ Alerta de Stock Mínimo Enviada por Correo.";
        }

        header("Location: ../facturacion.php?mensaje=" . urlencode($mensajeFinal));
        exit;

    } catch (Exception $e) {
        $conexion->rollback();
        $errorMsg = "❌ ERROR: " . $e->getMessage();
        header("Location: ../facturacion.php?error=" . urlencode($errorMsg));
        exit;
    }
}
?>