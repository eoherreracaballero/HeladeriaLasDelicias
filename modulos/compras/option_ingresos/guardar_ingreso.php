<?php
session_start();

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

// Capturar datos del formulario

    $tipoIngreso = $_POST['tipoIngreso'] ?? '';
    $idProveedor = $_POST['idProveedor'] ?? '';
    $noDocProveedor = $_POST['noDocProveedor'] ?? '';
    $fechaIngreso = $_POST['fechaIngreso'] ?? '';
    $productos = $_POST['producto'] ?? [];
    $bodegas = $_POST['bodega'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $costos = $_POST['costo'] ?? []; 

    // Validar campos obligatorios

    if (empty($tipoIngreso) || empty($idProveedor) || empty($fechaIngreso) || count($productos) == 0) {
        die("Faltan datos obligatorios.");
    }

    $subtotal = 0;
    for ($i = 0; $i < count($productos); $i++) {
        $subtotal += floatval($cantidades[$i]) * floatval($costos[$i]);
    }
    $iva = $subtotal * 0.19;
    $total = $subtotal + $iva;

    $conexion->begin_transaction();

    try {
        // Insertar en ingreso_producto
        $stmt = $conexion->prepare
        ("INSERT INTO ingreso_producto 
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

        // Insertar detalles y actualizar inventario
        $stmtDetalle = $conexion->prepare("INSERT INTO detalle_ingreso 
            (id_ingreso, id_producto, id_bodega, cantidad, costo_unitario) 
            VALUES (?, ?, ?, ?, ?)");

        $stmtInventario = $conexion->prepare("UPDATE inventario 
            SET stock = stock + ? 
            WHERE ID_Producto = ? AND ID_Bodega = ?");

        for ($i = 0; $i < count($productos); $i++) {
            $idProducto = intval($productos[$i]);
            $idBodega = intval($bodegas[$i]);
            $cantidad = floatval($cantidades[$i]);
            $costoUnitario = floatval($costos[$i]);

            // Insertar detalle
            $stmtDetalle->bind_param("iiidd", $idIngreso, $idProducto, $idBodega, $cantidad, $costoUnitario);
            $stmtDetalle->execute();

            // Actualizar inventario
            $stmtInventario->bind_param("dii", $cantidad, $idProducto, $idBodega);
            $stmtInventario->execute();
        }

        $stmtDetalle->close();
        $stmtInventario->close();

        $conexion->commit();

        header("Location: ../ingresos.php?msg=Ingreso registrado con éxito");
        exit;

    } catch (Exception $e) {
        $conexion->rollback();
        die("Error al guardar ingreso: " . $e->getMessage());
    }

    $conexion->close();
    }
