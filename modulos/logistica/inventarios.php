<?php
session_start();

// ✅ Control de acceso: solo usuarios autenticados
require_once __DIR__ . "/../../app/config/acceso.php"; 

    // Cargar encabezado y conexion a la base de datos
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");


// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// Obtener tipos únicos para filtro
$tiposResult = $conexion->query("SELECT DISTINCT Tipo FROM producto ORDER BY Tipo");
$tipos = [];
while ($row = $tiposResult->fetch_assoc()) {
    $tipos[] = $row['Tipo'];
}

// Obtener categorías únicas para filtro
$categoriasResult = $conexion->query("SELECT DISTINCT Categoria FROM producto ORDER BY Categoria");
$categorias = [];
while ($row = $categoriasResult->fetch_assoc()) {
    $categorias[] = $row['Categoria'];
}

// Leer filtros enviados por GET
$tipoFiltro = $_GET['tipo'] ?? '';
$categoriaFiltro = $_GET['categoria'] ?? '';

// Obtener bodegas para columnas
$bodegasResult = $conexion->query("SELECT ID_Bodega, Nombre_Bodega FROM bodega ORDER BY ID_Bodega");
$bodegas = [];
while ($row = $bodegasResult->fetch_assoc()) {
    $bodegas[$row['ID_Bodega']] = $row['Nombre_Bodega'];
}

// Construir parte dinámica para stock por bodega
$bodegaSelectParts = [];
foreach ($bodegas as $idBodega => $nombreBodega) {
    $bodegaSelectParts[] = "COALESCE(SUM(CASE WHEN i.ID_Bodega = $idBodega THEN i.Stock ELSE 0 END),0) AS stock_bodega_$idBodega";
}
$bodegaSelect = implode(", ", $bodegaSelectParts);

// Construir consulta SQL con filtros
$sql = "SELECT 
            p.ID_Producto,
            p.Nombre_Producto,
            $bodegaSelect,
            (
                SELECT di.Costo_Unitario
                FROM detalle_ingreso di
                JOIN ingreso_producto ip ON di.ID_Ingreso = ip.ID_Ingreso
                WHERE di.ID_Producto = p.ID_Producto
                ORDER BY ip.Fecha_Ingreso DESC, di.ID_Detalle_Ingreso DESC
                LIMIT 1
            ) AS Ultimo_Costo,
            (
                SELECT 
                    CASE WHEN SUM(di.Cantidad) > 0 
                    THEN SUM(di.Cantidad * di.Costo_Unitario) / SUM(di.Cantidad) 
                    ELSE 0 END
                FROM detalle_ingreso di
                WHERE di.ID_Producto = p.ID_Producto
            ) AS Costo_Promedio
        FROM producto p
        LEFT JOIN inventario i ON p.ID_Producto = i.ID_Producto
        WHERE 1=1 ";

if ($tipoFiltro !== '') {
    $tipoFiltroEscaped = $conexion->real_escape_string($tipoFiltro);
    $sql .= " AND p.Tipo = '$tipoFiltroEscaped' ";
}
if ($categoriaFiltro !== '') {
    $categoriaFiltroEscaped = $conexion->real_escape_string($categoriaFiltro);
    $sql .= " AND p.Categoria = '$categoriaFiltroEscaped' ";
}

$sql .= " GROUP BY p.ID_Producto, p.Nombre_Producto
          ORDER BY p.Nombre_Producto";

$resultado = $conexion->query($sql);

// Consumir la API
$apiUrl = "http://localhost/Heladeria/app/apis/api_productos.php";
if ($tipoFiltro !== '' || $categoriaFiltro !== '') {
    $apiUrl .= "?" . http_build_query([
        'tipo' => $tipoFiltro,
        'categoria' => $categoriaFiltro
    ]);
}

$apiResponse = '';
$apiProductos = [];
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $apiResponse = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception("Error en cURL: " . curl_error($ch));
    }
    curl_close($ch);

    // Parsear JSON
    $apiData = json_decode($apiResponse, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($apiData['productos'])) {
        $apiProductos = $apiData['productos'];
    } else {
        throw new Exception("Formato JSON inválido o sin productos.");
    }
} catch (Exception $e) {
    $apiResponse = '<div class="alert alert-danger">Error al consumir la API: ' . $e->getMessage() . '</div>';
}
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-boxes me-2"></i>Consulta de Producto x Bodega</h2>

    <!-- Formulario de filtros -->
    <form method="GET" action="inventarios.php" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="tipo" class="form-label">Tipo:</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="">-- Todos --</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo) ?>" <?= $tipoFiltro === $tipo ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="categoria" class="form-label">Categoría:</label>
                <select name="categoria" id="categoria" class="form-select">
                    <option value="">-- Todas --</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= htmlspecialchars($categoria) ?>" <?= $categoriaFiltro === $categoria ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="inventarios.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="mb-3">
        <strong>Mostrando productos para:</strong>
        Tipo: 
        <span class="text-primary">
            <?= $tipoFiltro !== '' ? htmlspecialchars($tipoFiltro) : 'Todos' ?>
        </span> | Categoría: 
        <span class="text-primary">
            <?= $categoriaFiltro !== '' ? htmlspecialchars($categoriaFiltro) : 'Todas' ?>
        </span>
    </div>

    <!-- Tabla de la base de datos -->
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <h3>Productos desde la Base de Datos</h3>
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>Producto</th>
                    <?php foreach ($bodegas as $nombreBodega): ?>
                        <th><?= htmlspecialchars($nombreBodega) ?></th>
                    <?php endforeach; ?>
                    <th>Último Costo</th>
                    <th>Costo Promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td><?= htmlspecialchars($fila['Nombre_Producto']) ?></td>
                        <?php foreach ($bodegas as $idBodega => $nombreBodega): ?>
                            <td><?= number_format($fila["stock_bodega_$idBodega"], 2) ?></td>
                        <?php endforeach; ?>
                        <td>$<?= number_format(floatval($fila['Ultimo_Costo']), 2) ?></td>
                        <td>$<?= number_format(floatval($fila['Costo_Promedio']), 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No se encontraron productos en la base de datos.</div>
    <?php endif; ?>

    <!-- Tabla de la API -->
    <h3>Productos desde la API</h3>
    <?php if (!empty($apiProductos)): ?>
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID Producto</th>
                    <th>Producto</th>
                    <th>Stock Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apiProductos as $producto): ?>
                    <tr class="text-center">
                        <td><?= htmlspecialchars($producto['ID_Producto']) ?></td>
                        <td><?= htmlspecialchars($producto['Nombre_Producto']) ?></td>
                        <td><?= number_format(floatval($producto['Stock_Total']), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No se encontraron productos en la API o la respuesta no es válida.</div>
    <?php endif; ?>
</main>

<?php
$conexion->close();
?>