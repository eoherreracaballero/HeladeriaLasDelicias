<?php
session_start();

// RUTA DE CONEXIÓN: Debe estar tres niveles atrás de 'modulos/ventas/option_notas'
$conexion_path = __DIR__ . "/../../../app/db/conexion.php";

if (!file_exists($conexion_path)) {
    die("Error: Archivo de conexión no encontrado.");
}
include $conexion_path;

// --- Funciones de Utilidad ---

function redireccionar_con_error($mensaje) {
    global $conexion;
    // Asegura el rollback si la transacción está activa
    if (isset($conexion) && $conexion->in_transaction) {
        $conexion->rollback();
    }
    if (isset($conexion)) {
        $conexion->close();
    }
    header("Location: ../notas.php?error=" . urlencode($mensaje));
    exit();
}

function redireccionar_con_exito($mensaje) {
    global $conexion;
    if (isset($conexion)) {
        $conexion->close();
    }
    header("Location: ../notas.php?mensaje=" . urlencode($mensaje));
    exit();
}

/**
 * Obtiene el siguiente número consecutivo para la serie de documentos (NC/ND).
 * @param string $tipoNota 'Nota de Crédito' o 'Nota de Débito'
 * @throws Exception
 * @return string Nuevo número de documento formateado (Ej: NC-0052)
 */

/**
* Obtiene el siguiente número consecutivo para la serie de documentos (NC/ND).
 */
function get_next_document_number($tipoNota) {
    global $conexion;
    
    $prefijo = ($tipoNota === 'Nota de Crédito') ? 'NC-' : 'ND-';
    $patronBusqueda = $prefijo . '%'; // Buscar cualquier Num_Documento que empiece con NC- o ND-

    // Consulta: Obtener el último número de documento basado en el PREFIJO (Num_Documento)
    $sqlLastNum = "SELECT Num_Documento FROM egreso_producto 
                   WHERE Num_Documento LIKE ? 
                   ORDER BY ID_Egreso DESC 
                   LIMIT 1";

    $stmt = $conexion->prepare($sqlLastNum);
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta de secuencia: " . $conexion->error);
    }
    
    // El parámetro es el patrón de búsqueda (Ej: 'NC-%')
    $stmt->bind_param("s", $patronBusqueda); 
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ultimoNumero = 0;
    
    if ($row = $result->fetch_assoc()) {
        // Extraer solo el número después del prefijo (Ej: NC-0050 -> 50)
        $numStr = str_replace($prefijo, '', $row['Num_Documento']);
        $ultimoNumero = intval($numStr);
    }
    
    $stmt->close();

    $siguienteNumero = $ultimoNumero + 1;
    $nuevoNumFormato = str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);

    return $prefijo . $nuevoNumFormato;
}


// 1. Verificar método
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    redireccionar_con_error("Método de solicitud no permitido.");
}

// 2. Obtener el ID del usuario logueado (Se asume ID=1 si no hay sesión activa)
$usuarioId = $_SESSION['ID_Usuario'] ?? 1; 

// 3. Recibir y validar datos de cabecera
$tipoNota = $_POST['tipo_nota'] ?? '';
$clienteId = (int) ($_POST['cliente'] ?? 0);
$facturaReferenciaId = (int) ($_POST['no_factura_referencia'] ?? 0);
$fechaNota = $_POST['fecha_nota'] ?? date('Y-m-d');
$motivo = $_POST['motivo'] ?? '';

// Los totales vienen en formato float
$subtotal = floatval($_POST['subtotal'] ?? 0);
$iva = floatval($_POST['IVA'] ?? 0);
$total = floatval($_POST['total'] ?? 0);

// Validaciones de cabecera
if (!in_array($tipoNota, ['Nota de Crédito', 'Nota de Débito'])) {
    redireccionar_con_error("Tipo de documento inválido.");
}
if ($clienteId <= 0 || $facturaReferenciaId <= 0 || empty($motivo)) {
    redireccionar_con_error("Datos de cabecera incompletos o inválidos.");
}
if ($total <= 0) {
     redireccionar_con_error("El Total a afectar debe ser mayor que cero.");
}

// =======================================================
// INICIO DE LA TRANSACCIÓN
// =======================================================
$conexion->begin_transaction();

try {
    // Generar el número de documento único (NC-XXXX o ND-XXXX)
    $numDocumento = get_next_document_number($tipoNota);
    
    // 4. INSERTAR CABECERA de la Nota en egreso_producto
    $sqlCabecera = "INSERT INTO egreso_producto (Id_cliente, Fecha_Egreso, Subtotal_Egreso, IVA_Egreso, Total_Egreso, Num_Documento, Id_Usuario, Tipo_Egreso, Factura_Referencia_Id, Motivo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmtCabecera = $conexion->prepare($sqlCabecera);
    if ($stmtCabecera === false) {
        throw new Exception("Error al preparar la cabecera: " . $conexion->error);
    }
    
    // Tipos: i, s, d, d, d, s, i, s, i, s (10 parámetros)
    $stmtCabecera->bind_param("isdddsisis", 
        $clienteId, 
        $fechaNota, 
        $subtotal, 
        $iva, 
        $total, 
        $numDocumento, 
        $usuarioId, 
        $tipoNota,
        $facturaReferenciaId,
        $motivo
    );
    
    if (!$stmtCabecera->execute()) {
        throw new Exception("Error al ejecutar la inserción de la Nota: " . $stmtCabecera->error);
    }
    
    $notaId = $conexion->insert_id; 
    $stmtCabecera->close();

    // 5. PROCESAR DETALLE DE PRODUCTOS
    $productos = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $costos = $_POST['costo'] ?? [];
    $bodegas = $_POST['bodega_id'] ?? [];
    $subtotalesItem = $_POST['subtotal_item'] ?? [];

    $itemsGuardados = 0;
    $numItemsRecibidos = count($productos);

    // Recorrer los arrays de detalle
    for ($i = 0; $i < $numItemsRecibidos; $i++) {
        // Asegurar la interpretación numérica de la Cantidad (solución al bug anterior)
        $cantidad_str = trim($cantidades[$i] ?? '0');
        $cantidad = floatval(str_replace(',', '.', $cantidad_str));
        
        $productoId = (int) ($productos[$i] ?? 0);
        $pvp = floatval($costos[$i] ?? 0); 
        $bodegaId = (int) ($bodegas[$i] ?? 0);
        $subtotalItem = floatval($subtotalesItem[$i] ?? 0);

        // A. VALIDACIÓN: Si la cantidad es CERO o los IDs son inválidos, se salta.
        if ($cantidad <= 0) {
            continue; 
        }
        if ($productoId <= 0 || $pvp <= 0 || $bodegaId <= 0) {
             throw new Exception("Error de validación en el detalle. Cantidad OK, pero Producto/PVP/Bodega son inválidos para el ítem " . ($i + 1));
        }

        // 6. INSERTAR DETALLE
        $sqlDetalle = "INSERT INTO detalle_egreso (Id_Egreso, ID_producto, Cantidad, PVP, ID_Bodega, Subtotal_Egreso) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmtDetalle = $conexion->prepare($sqlDetalle);
        if ($stmtDetalle === false) {
            throw new Exception("Error al preparar el detalle: " . $conexion->error);
        }
        
        // Tipos de datos: i, i, d, d, i, d
        $stmtDetalle->bind_param("iidddd", // Se corrige a iidddd (3i, 3d) si bodega es d, o iidddi si subtotal es d (ajustado a iidddi)
            $notaId, 
            $productoId, 
            $cantidad, 
            $pvp, 
            $bodegaId, 
            $subtotalItem
        );
        
        if (!$stmtDetalle->execute()) {
            throw new Exception("Error al insertar detalle: " . $stmtDetalle->error);
        }
        $stmtDetalle->close();

        // 7. ACTUALIZACIÓN DE INVENTARIO (Solución con UPSERT)
        if ($tipoNota === 'Nota de Crédito') {
            
            // 7.1. Intento de ACTUALIZACIÓN (si el registro ya existe)
            // Asumiendo que la tabla se llama 'inventario'
            $sqlUpdateStock = "UPDATE inventario SET Stock = Stock + ? WHERE ID_Producto = ? AND ID_Bodega = ?";
            $stmtStock = $conexion->prepare($sqlUpdateStock);
            if ($stmtStock === false) {
                throw new Exception("Error al preparar la actualización de stock: " . $conexion->error);
            }
            
            $stmtStock->bind_param("dii", $cantidad, $productoId, $bodegaId);
            
            if (!$stmtStock->execute()) {
                throw new Exception("Error al ejecutar la actualización de stock (Inventario): " . $stmtStock->error);
            }

            $filasAfectadas = $stmtStock->affected_rows;
            $stmtStock->close();

            // 7.2. Si NO se afectó ninguna fila (filasAfectadas === 0), hacemos un INSERT
            if ($filasAfectadas === 0) {
                // Esto crea el registro de inventario que faltaba con el nuevo stock
                $sqlInsertStock = "INSERT INTO inventario (ID_Producto, ID_Bodega, Stock) VALUES (?, ?, ?)";
                $stmtInsert = $conexion->prepare($sqlInsertStock);
                if ($stmtInsert === false) {
                    throw new Exception("Error al preparar la inserción de nuevo stock: " . $conexion->error);
                }
                
                $stmtInsert->bind_param("iid", $productoId, $bodegaId, $cantidad);

                if (!$stmtInsert->execute()) {
                    throw new Exception("Error al insertar nuevo stock (Inventario): " . $stmtInsert->error);
                }
                $stmtInsert->close();
            }
        }
        
        $itemsGuardados++;
    } // Fin del bucle de detalle

    // 8. Validación final: Asegurar que al menos se guardó un ítem si hay total > 0
    if ($itemsGuardados === 0) {
        throw new Exception("El Total de la Nota es positivo ($total), pero no se pudo registrar ningún ítem en el detalle.");
    }

    // 9. FINALIZAR TRANSACCIÓN Y REDIRECCIONAR
    $conexion->commit();
    redireccionar_con_exito("Nota de " . $tipoNota . " **" . $numDocumento . "** procesada con éxito.");

} // <-- Esta llave cierra el bloque TRY correctamente

catch (Exception $e) { // <-- La línea donde ocurría el error.
    // 10. MANEJO DE ERRORES: Revertir la transacción
    redireccionar_con_error("Fallo al procesar la Nota. Causa: " . $e->getMessage());
}
?>