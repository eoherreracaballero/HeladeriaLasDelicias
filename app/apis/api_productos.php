<?php
// 1. Encabezados para definir el tipo de respuesta
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// 2. Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "heladeria");
if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// 3. Captura de filtros dinámicos
$tipo = $_GET['tipo'] ?? '';
$categoria = $_GET['categoria'] ?? '';

$where = "WHERE 1=1 ";
if ($tipo !== '') {
    $tipoEsc = $conexion->real_escape_string($tipo);
    $where .= " AND p.Tipo = '$tipoEsc' ";
}
if ($categoria !== '') {
    $categoriaEsc = $conexion->real_escape_string($categoria);
    $where .= " AND p.Categoria = '$categoriaEsc' ";
}

// 4. Consulta SQL con filtros
$sql = "SELECT p.ID_Producto, p.Nombre_Producto, 
            COALESCE(SUM(i.Stock),0) AS Stock_Total
        FROM producto p
        LEFT JOIN inventario i ON p.ID_Producto = i.ID_Producto
        $where
        GROUP BY p.ID_Producto, p.Nombre_Producto
        ORDER BY p.Nombre_Producto";

// 5. Ejecución de consulta
$result = $conexion->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la consulta SQL',
        'detalle' => $conexion->error,
        'sql' => $sql
    ]);
    exit;
}

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

// Si no hay productos
if (empty($productos)) {
    http_response_code(404);
    echo json_encode(['message' => 'No se encontraron productos']);
    exit;
}
// 6. Respuesta en JSON
http_response_code(200);
echo json_encode(['productos' => $productos]);

$conexion->close();
?>
