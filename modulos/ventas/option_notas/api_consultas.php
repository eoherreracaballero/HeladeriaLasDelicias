<?php
// modulos/ventas/option_notas/api_consultas.php

// 1. SUPRESIÓN ESTRICTA DE ERRORES Y ADVERTENCIAS
error_reporting(0); // Suprime Warnings y Notices.
ini_set('display_errors', 0);

// 2. Configuración de respuesta JSON inicial
header('Content-Type: application/json');
$response = ['error' => false, 'message' => '', 'data' => []];

// ** CORRECCIÓN CLAVE: Definir la acción a ejecutar **
$action = $_GET['action'] ?? '';

try {
    // 3. VERIFICACIÓN Y CORRECCIÓN DE LA RUTA
    $conexion_path = __DIR__ . "/../../../app/db/conexion.php";

    // Utilizamos require_once para lanzar un error fatal si el archivo no existe
    if (!file_exists($conexion_path)) {
        throw new Exception("Error: Archivo de conexión no encontrado.");
    }
    require_once $conexion_path;

    // 1.2. Verificar la conexión a la base de datos
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
    }

    // =========================================================================
    // ACCIÓN 1: OBTENER VENTAS POR CLIENTE (getVentas)
    // =========================================================================
    if ($action === 'getVentas') {
        $clienteId = $_GET['cliente_id'] ?? 0;
        
        if ($clienteId <= 0) {
            throw new Exception('ID de cliente inválido.');
        } 

        // Consulta: Obtener ID, Fecha y Total de egresos (facturas) del cliente
        $sql = "SELECT ID_Egreso, Fecha_Egreso, Total_Egreso 
                FROM egreso_producto 
                WHERE Id_cliente = ? 
                AND Tipo_Egreso = 'Factura'
                ORDER BY Fecha_Egreso DESC";
        
        $stmt = $conexion->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception('Error de preparación SQL (getVentas): ' . $conexion->error);
        }
        
        $stmt->bind_param("i", $clienteId);
        
        if (!$stmt->execute()) {
            throw new Exception('Error de ejecución SQL (getVentas): ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        $ventas = [];
        while ($row = $result->fetch_assoc()) {
            $ventas[] = $row;
        }
        $stmt->close();
        
        $response['data'] = $ventas;
    }

    // =========================================================================
    // ACCIÓN 2: OBTENER DETALLE DE UNA FACTURA (getFacturaDetalles)
    // =========================================================================
    elseif ($action === 'getFacturaDetalles') {
        $facturaId = $_GET['id'] ?? 0;
        
        if ($facturaId <= 0) {
            throw new Exception('ID de Factura inválido.');
        } 

        // Consulta: Obtener detalles del egreso, usando PVP para el costo
        $sql = "SELECT 
                    de.ID_producto, 
                    p.Nombre_Producto,
                    de.Cantidad, 
                    de.PVP, 
                    de.ID_Bodega,
                    ep.Id_cliente,
                    ep.Total_Egreso,
                    ep.IVA_Egreso
                FROM detalle_egreso de
                JOIN producto p ON de.ID_producto = p.ID_Producto
                JOIN egreso_producto ep ON de.Id_Egreso = ep.ID_Egreso
                WHERE de.Id_Egreso = ?";
        
        $stmt = $conexion->prepare($sql);

        if ($stmt === false) {
            throw new Exception('Error de preparación SQL (getFacturaDetalles): ' . $conexion->error);
        }
        
        $stmt->bind_param("i", $facturaId);
        
        if (!$stmt->execute()) {
            throw new Exception('Error de ejecución SQL (getFacturaDetalles): ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
        $stmt->close();

        if (count($detalles) > 0) {
            // Extrayendo datos de cabecera del primer detalle
            $cabecera = [
                'Id_cliente' => $detalles[0]['Id_cliente'],
                'Total_Egreso' => $detalles[0]['Total_Egreso'],
                'IVA_Egreso' => $detalles[0]['IVA_Egreso']
            ];
            
            // Eliminamos los campos redundantes en los detalles y los devolvemos limpios
            foreach ($detalles as $key => $d) {
                unset($detalles[$key]['Id_cliente']);
                unset($detalles[$key]['Total_Egreso']);
                unset($detalles[$key]['IVA_Egreso']);
            }
            
            // Devolvemos cabecera y detalles anidados
            $response['data'] = array_merge($cabecera, ['detalles' => $detalles]);
        } else {
            $response['error'] = true;
            $response['message'] = 'No se encontraron detalles para esta factura.';
        }
    }

    // =========================================================================
    // ACCIÓN NO RECONOCIDA
    // =========================================================================
    else {
        // Solo lanza error si se intentó una acción y no fue reconocida
        if (!empty($action)) {
             throw new Exception('Acción no reconocida: ' . $action);
        }
    }

} catch (Exception $e) {
    // Captura cualquier excepción y la formatea en la respuesta JSON
    $response['error'] = true;
    $response['message'] = "Fallo en la API: " . $e->getMessage();
}

// 3. Devolver la respuesta en formato JSON
echo json_encode($response);
// Cierre de la conexión
if (isset($conexion)) {
    $conexion->close();
}
?>