<?php
session_start();

// Cargar encabezado y estilos de tablas
require_once __DIR__ . "/../../../public/html/encabezado.php";
require_once __DIR__ . "/../../../public/html/tablas.php";

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// 1. OBTENER PRODUCTOS y BODEGAS para el formulario de selección
// (Simulamos la obtención de datos para los <select>)
$sqlProductos = "SELECT ID_Producto, Nombre_Producto FROM producto ORDER BY Nombre_Producto";
$resultProductos = $conexion->query($sqlProductos);

$sqlBodegas = "SELECT Id_Bodega, Nombre_Bodega FROM bodega ORDER BY Nombre_Bodega";
$resultBodegas = $conexion->query($sqlBodegas);

// 2. CAPTURAR FILTROS Y EJECUTAR CONSULTA KARDEX
$kardexData = [];
$idProductoFiltro = isset($_GET['producto']) ? intval($_GET['producto']) : 0;
$idBodegaFiltro = isset($_GET['bodega']) ? intval($_GET['bodega']) : 0;

if ($idProductoFiltro > 0 && $idBodegaFiltro > 0) {
    // ESTE ES EL SQL CLAVE: Consulta a una tabla unificada (movimiento_kardex)
    // donde cada transacción (ingreso, egreso, nota) ha dejado un registro estandarizado.
    $sqlKardex = "
        SELECT 
            Fecha_Movimiento, 
            Documento_Referencia, 
            Tipo_Movimiento, 
            Cantidad_Entrada, 
            Costo_Unitario_Entrada, 
            Cantidad_Salida, 
            Costo_Unitario_Salida, 
            Saldo_Stock, 
            Saldo_Costo_Promedio 
        FROM movimiento_kardex 
        WHERE ID_Producto = ? AND ID_Bodega = ?
        ORDER BY Fecha_Movimiento ASC, ID_Movimiento ASC"; 
        
    $stmtKardex = $conexion->prepare($sqlKardex);
    $stmtKardex->bind_param("ii", $idProductoFiltro, $idBodegaFiltro);
    $stmtKardex->execute();
    $resultKardex = $stmtKardex->get_result();
    $kardexData = $resultKardex->fetch_all(MYSQLI_ASSOC);
    $stmtKardex->close();
}
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-layer-group me-2"></i> Reporte Kardex (Costo Promedio)</h2>

    <!-- Formulario de Filtro -->
    <form method="GET" action="kardex_reporte.php" class="bg-light p-4 mb-4 rounded shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="producto" class="form-label fw-bold">Producto:</label>
                <select class="form-select" id="producto" name="producto" required>
                    <option value="">Seleccione un producto</option>
                    <?php while ($prod = $resultProductos->fetch_assoc()): ?>
                        <option value="<?= $prod['ID_Producto'] ?>" <?= $idProductoFiltro == $prod['ID_Producto'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prod['Nombre_Producto']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="bodega" class="form-label fw-bold">Bodega:</label>
                <select class="form-select" id="bodega" name="bodega" required>
                    <option value="">Seleccione una bodega</option>
                    <?php while ($bod = $resultBodegas->fetch_assoc()): ?>
                        <option value="<?= $bod['Id_Bodega'] ?>" <?= $idBodegaFiltro == $bod['Id_Bodega'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($bod['Nombre_Bodega']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Consultar Kardex
                </button>
            </div>
        </div>
    </form>

    <!-- Tabla de Resultados del Kardex -->
    <?php if ($idProductoFiltro > 0 && $idBodegaFiltro > 0): ?>
        <div class="card shadow-lg border-0 rounded-3 mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Movimientos Históricos</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($kardexData) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle text-center table-sm mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="2">Fecha</th>
                                    <th rowspan="2">Documento / Tipo</th>
                                    <th colspan="3">ENTRADAS (Ingresos)</th>
                                    <th colspan="3">SALIDAS (Egresos)</th>
                                    <th colspan="3">SALDO (Existencia)</th>
                                </tr>
                                <tr>
                                    <th>Cant.</th>
                                    <th>Costo Unit.</th>
                                    <th>Costo Total</th>
                                    <th>Cant.</th>
                                    <th>Costo Unit.</th>
                                    <th>Costo Total</th>
                                    <th>Cant.</th>
                                    <th>Costo Unit.</th>
                                    <th>Costo Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($kardexData as $fila): 
                                    // Calcular Costo Total de Saldo
                                    $saldoCostoTotal = $fila['Saldo_Stock'] * $fila['Saldo_Costo_Promedio'];
                                    // Calcular Costo Total de Entrada/Salida
                                    $entradaCostoTotal = $fila['Cantidad_Entrada'] * $fila['Costo_Unitario_Entrada'];
                                    $salidaCostoTotal = $fila['Cantidad_Salida'] * $fila['Costo_Unitario_Salida'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['Fecha_Movimiento']) ?></td>
                                    <td><?= htmlspecialchars($fila['Documento_Referencia']) . " (" . htmlspecialchars($fila['Tipo_Movimiento']) . ")" ?></td>
                                    
                                    <!-- ENTRADAS -->
                                    <td class="text-success"><?= number_format($fila['Cantidad_Entrada'], 2) ?></td>
                                    <td>$<?= number_format($fila['Costo_Unitario_Entrada'], 2) ?></td>
                                    <td>$<?= number_format($entradaCostoTotal, 2) ?></td>

                                    <!-- SALIDAS -->
                                    <td class="text-danger"><?= number_format($fila['Cantidad_Salida'], 2) ?></td>
                                    <td>$<?= number_format($fila['Costo_Unitario_Salida'], 2) ?></td>
                                    <td>$<?= number_format($salidaCostoTotal, 2) ?></td>
                                    
                                    <!-- SALDO -->
                                    <td class="fw-bold"><?= number_format($fila['Saldo_Stock'], 2) ?></td>
                                    <td class="fw-bold">$<?= number_format($fila['Saldo_Costo_Promedio'], 2) ?></td>
                                    <td class="fw-bold">$<?= number_format($saldoCostoTotal, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info m-3 text-center">
                        No hay movimientos registrados para este producto y bodega.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif (isset($_GET['producto']) || isset($_GET['bodega'])): ?>
        <div class="alert alert-warning text-center">
            Seleccione un Producto y una Bodega para ver el Kardex.
        </div>
    <?php endif; ?>

</main>
<?php $conexion->close(); ?>
