<?php
session_start();

/**
 * CONSULTA DE AJUSTES TÉCNICOS CON BUSCADOR
 * Ubicación: Heladeria/modulos/logistica/crud_ajustes/consulta_ajustes.php
 */

// 1. CONFIGURACIÓN DE RUTAS DINÁMICAS
$ruta_raiz = __DIR__ . "/../../../";

if (!file_exists($ruta_raiz . "public/html/encabezado.php")) {
    die("Error de sistema: No se pudo localizar el archivo de encabezado.");
}

require_once $ruta_raiz . "public/html/encabezado.php";
include($ruta_raiz . "app/db/conexion.php");
require_once $ruta_raiz . "public/html/tablas.php";

// 2. CAPTURA DE FILTROS
$fecha_desde = $_GET['desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['hasta'] ?? date('Y-m-d');
$buscar_producto = $_GET['buscar_producto'] ?? ''; // Nuevo filtro

// 3. CONSTRUCCIÓN DE LA CONSULTA SQL CON FILTRO DINÁMICO
$sql = "SELECT 
            n.id_nota, 
            n.fecha_nota, 
            n.tipo_nota,
            p.Nombre_Producto,
            b.Nombre_Bodega,
            k.Tipo_Movimiento,
            k.Cantidad_Entrada,
            k.Cantidad_Salida,
            k.Costo_Entrada,
            k.Costo_Salida,
            m.Motivo
        FROM nota_ajuste n
        INNER JOIN movimiento_kardex k ON n.id_nota = k.ID_Documento AND k.Tipo_Documento = 'NOTA_AJUSTE'
        INNER JOIN producto p ON k.ID_Producto = p.ID_Producto
        INNER JOIN bodega b ON k.ID_Bodega = b.Id_Bodega
        LEFT JOIN movimiento_inventario m ON k.ID_Producto = m.ID_Producto 
             AND k.Fecha_Movimiento = m.Fecha_Movimiento 
             AND m.Tipo_Movimiento = 'Ajuste'
        WHERE n.fecha_nota BETWEEN '$fecha_desde' AND '$fecha_hasta'";

// Agregar condición de búsqueda si el usuario escribió algo
if (!empty($buscar_producto)) {
    $busqueda = mysqli_real_escape_string($conexion, $buscar_producto);
    $sql .= " AND p.Nombre_Producto LIKE '%$busqueda%'";
}

$sql .= " ORDER BY n.id_nota DESC";
$resultado = $conexion->query($sql);
?>

<style>
    .badge-entrada { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; font-weight: bold; }
    .badge-salida { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; font-weight: bold; }
    .text-valor-salida { color: #dc3545 !important; font-weight: bold; }
    .text-valor-entrada { color: #0d6efd !important; font-weight: bold; }
    .table-modern tbody tr:hover { background-color: #fff4f4; transition: 0.3s; }
    @media print { .no-print { display: none !important; } }
</style>

<main class="container-fluid p-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div class="d-flex align-items-center">
            <a href="../ajustes.php" class="btn btn-outline-danger me-3 shadow-sm border-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div>
                <h2 class="text-primary fw-bold mb-0">Historial de Ajustes</h2>
                <p class="text-muted mb-0 small text-uppercase fw-bold">Auditoría Técnica de Inventario</p>
            </div>
        </div>
        <a href="../ajustes.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Registro
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body bg-light rounded border">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">DESDE</label>
                    <input type="date" name="desde" id="desde" class="form-control" value="<?= $fecha_desde ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">HASTA</label>
                    <input type="date" name="hasta" id="hasta" class="form-control" value="<?= $fecha_hasta ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">BUSCAR PRODUCTO</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="buscar_producto" id="buscar_producto" class="form-control" placeholder="Nombre o sabor..." value="<?= htmlspecialchars($buscar_producto) ?>">
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <button type="submit" class="btn btn-dark px-3 shadow-sm">
                        <i class="fas fa-sync-alt"></i> Filtrar
                    </button>
                    <button type="button" onclick="exportarExcel()" class="btn btn-danger px-3 shadow-sm">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4">Folio</th>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Bodega</th>
                            <th class="text-center">Operación</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Valorizado</th>
                            <th class="ps-4">Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && $resultado->num_rows > 0): ?>
                            <?php while ($row = $resultado->fetch_assoc()): 
                                $es_entrada = ($row['Cantidad_Entrada'] > 0);
                                $cantidad = $es_entrada ? $row['Cantidad_Entrada'] : $row['Cantidad_Salida'];
                                $costo = $es_entrada ? $row['Costo_Entrada'] : $row['Costo_Salida'];
                                $subtotal = $cantidad * $costo;
                                $clase_monto = $es_entrada ? 'text-valor-entrada' : 'text-valor-salida';
                            ?>
                                <tr class="hover-row">
                                    <td class="ps-4 fw-bold text-muted">#<?= $row['id_nota'] ?></td>
                                    <td class="small"><?= date('d/m/Y', strtotime($row['fecha_nota'])) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['Nombre_Producto']) ?></td>
                                    <td><?= htmlspecialchars($row['Nombre_Bodega']) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $es_entrada ? 'badge-entrada' : 'badge-salida' ?> px-3 py-2 rounded-pill">
                                            <?= $es_entrada ? 'ENTRADA' : 'SALIDA' ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold"><?= number_format($cantidad, 2) ?></td>
                                    <td class="text-end <?= $clase_monto ?>">$<?= number_format($subtotal, 2) ?></td>
                                    <td class="ps-4 small text-muted border-start italic"><?= htmlspecialchars($row['Motivo'] ?? 'Sin comentario') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-box-open fa-3x mb-3 text-light"></i><br>
                                    No se encontraron registros que coincidan con los filtros.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
function exportarExcel() {
    const desde = document.getElementById('desde').value;
    const hasta = document.getElementById('hasta').value;
    const producto = document.getElementById('buscar_producto').value; // También exportamos el filtro de producto
    
    window.location.href = `reporte_excel_ajustes.php?desde=${desde}&hasta=${hasta}&buscar_producto=${producto}`;
}
</script>

<?php if(isset($conexion)) $conexion->close(); ?>