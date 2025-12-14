<?php
session_start();

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CAPTURA DE DATOS ---
    $tipoIngreso = $_POST['tipoIngreso'] ?? '';
    $idProveedor = intval($_POST['idProveedor'] ?? 0);
    $noDocProveedor = $_POST['noDocProveedor'] ?? '';
    $fechaIngreso = $_POST['fechaIngreso'] ?? '';
    $productos = $_POST['producto'] ?? [];
    $bodegas = $_POST['bodega'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $costos = $_POST['costo'] ?? []; 

    // --- VALIDACIÓN INICIAL DE CAMPOS ---
    if (empty($tipoIngreso) || $idProveedor <= 0 || empty($fechaIngreso) || count($productos) == 0) {
        die("Faltan datos obligatorios.");
    }

    // --- CÁLCULO DE TOTALES DE CABECERA ---
    $subtotal = 0;
    for ($i = 0; $i < count($productos); $i++) {
        $subtotal += floatval($cantidades[$i]) * floatval($costos[$i]);
    }
    $iva = $subtotal * 0.19;
    $total = $subtotal + $iva;

    // --- INICIO DE TRANSACCIÓN ---
    $conexion->begin_transaction();

    try {
        // 1. Insertar en ingreso_producto (Cabecera)
        $stmt = $conexion->prepare(
            "INSERT INTO ingreso_producto 
                (tipo_ingreso, id_proveedor, no_doc_proveedor, fecha_ingreso, subtotal, iva, total, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->bind_param(
            "sissddd", 
            $tipoIngreso, 
            $idProveedor, 
            $noDocProveedor, 
            $fechaIngreso, 
            $subtotal, 
            $iva, 
            $total
        );

        $stmt->execute();
        $idIngreso = $conexion->insert_id;
        $stmt->close();

        // 2. Preparar consulta para obtener stock y costo promedio actual
        $stmtStockActual = $conexion->prepare("
            SELECT stock, Costo_Promedio 
            FROM inventario 
            WHERE ID_Producto = ? AND ID_Bodega = ? 
            FOR UPDATE" // Bloquear fila para evitar concurrencia
        );

        // 3. Preparar UPDATE para inventario (stock y Costo_Promedio)
        $stmtInventarioUpdate = $conexion->prepare("
            UPDATE inventario 
            SET stock = ?, Costo_Promedio = ? 
            WHERE ID_Producto = ? AND ID_Bodega = ?"
        );

        // 4. Preparar INSERT/UPDATE para detalle_ingreso y stock
        $stmtDetalle = $conexion->prepare("INSERT INTO detalle_ingreso 
            (id_ingreso, id_producto, id_bodega, cantidad, costo_unitario) 
            VALUES (?, ?, ?, ?, ?)");


        for ($i = 0; $i < count($productos); $i++) {
            $idProducto = intval($productos[$i]);
            $idBodega = intval($bodegas[$i]);
            $cantidadEntrada = floatval($cantidades[$i]);
            $costoUnitarioEntrada = floatval($costos[$i]);

            // Obtener stock y CPP actual
            $stockActual = 0;
            $costoPromedioActual = 0;
            
            $stmtStockActual->bind_param("ii", $idProducto, $idBodega);
            $stmtStockActual->execute();
            $result = $stmtStockActual->get_result();
            if ($row = $result->fetch_assoc()) {
                $stockActual = floatval($row['stock']);
                $costoPromedioActual = floatval($row['Costo_Promedio']);
            }
            // NOTA: Si no existe fila en inventario, stockActual=0 y CPP=0 (es la primera vez)

            // --- CÁLCULO DEL NUEVO COSTO PROMEDIO PONDERADO (CPP) ---
            $costoTotalAnterior = $stockActual * $costoPromedioActual;
            $costoTotalEntrada = $cantidadEntrada * $costoUnitarioEntrada;
            
            $nuevoStock = $stockActual + $cantidadEntrada;

            if ($nuevoStock > 0) {
                $nuevoCostoPromedio = ($costoTotalAnterior + $costoTotalEntrada) / $nuevoStock;
            } else {
                $nuevoCostoPromedio = $costoPromedioActual; // Debería ser imposible, pero por seguridad
            }

            // --- EJECUTAR ACTUALIZACIONES ---

            // a) Insertar detalle de ingreso
            $stmtDetalle->bind_param("iiidd", $idIngreso, $idProducto, $idBodega, $cantidadEntrada, $costoUnitarioEntrada);
            $stmtDetalle->execute();

            // b) Actualizar inventario (Stock y CPP)
            // Usamos INSERT ON DUPLICATE KEY UPDATE si la fila podría no existir (mejor práctica)
            $sqlInventarioUpsert = "
                INSERT INTO inventario (ID_Producto, ID_Bodega, stock, Costo_Promedio)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                stock = VALUES(stock), 
                Costo_Promedio = VALUES(Costo_Promedio)
            ";
            $stmtInventarioUpsert = $conexion->prepare($sqlInventarioUpsert);
            $stmtInventarioUpsert->bind_param("iidd", $idProducto, $idBodega, $nuevoStock, $nuevoCostoPromedio);
            $stmtInventarioUpsert->execute();
            $stmtInventarioUpsert->close();

            // 5. ¡PUNTO CLAVE! Insertar un registro en el LEDGER KARDEX UNIFICADO
            // Asumiendo una tabla 'movimiento_kardex' con columnas: 
            // ID_Producto, ID_Bodega, Fecha, Tipo, Cantidad_Entrada, Costo_Unitario_Entrada, Saldo_Stock, Saldo_Costo_Promedio
            
            // --- Lógica para insertar en movimiento_kardex iría aquí si la tabla existe ---

        }

        $stmtDetalle->close();
        $stmtStockActual->close();

        $conexion->commit();

        header("Location: ../ingresos.php?msg=Ingreso registrado con éxito y Costo Promedio actualizado.");
        exit;

    } catch (Exception $e) {
        $conexion->rollback();
        die("Error al guardar ingreso: " . $e->getMessage());
    }

    $conexion->close();
}
?>
