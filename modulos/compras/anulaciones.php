<?php
session_start();

// Cargar encabezado
require_once __DIR__ . "/../../public/html/encabezado.php";

// Conexión de base de datos
include(__DIR__ . "/../../app/db/conexion.php");

// Definir estados válidos para el filtro
$estados_validos = ['pendiente', 'grabado', 'anulado'];
$estado_seleccionado = '';
$condicion_where = 'ip.estado IS NOT NULL'; // Condición inicial para mostrar todos por defecto, o si no hay filtro

// 1. Manejo del Filtro de Estado (usando Sentencias Preparadas si aplica)
if (isset($_GET['estado']) && in_array(strtolower(trim($_GET['estado'])), $estados_validos)) {
    $estado_seleccionado = strtolower(trim($_GET['estado']));
    $condicion_where = 'ip.estado = ?';
}

// Construcción de la consulta SQL
$sql = "SELECT ip.ID_Ingreso, ip.tipo_ingreso, ip.fecha_ingreso, ip.total, p.Nombre_Proveedor, ip.estado
        FROM ingreso_producto ip
        LEFT JOIN proveedor p ON ip.ID_proveedor = p.ID_Proveedor
        WHERE " . $condicion_where . "
        ORDER BY ip.fecha_ingreso DESC";

$resultado = null; // Inicializar resultado

// Usar Sentencias Preparadas si hay un filtro aplicado
if ($estado_seleccionado) {
    // Consulta con filtro
    $stmt = $conexion->prepare($sql);
    // 's' indica que el parámetro es un string
    $stmt->bind_param("s", $estado_seleccionado);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
} else {
    // Consulta sin filtro (muestra todos)
    $resultado = $conexion->query($sql);
}

?>

<!-- Boton de regreso al menu anterior -->
<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <div class="mb-3 text-end">
        <a href="./ingresos.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver a Compras y Ajustes
        </a>
    </div>
        
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-undo-alt me-2"></i> Anulación y Filtro de Ingresos</h2>

    <!-- Formulario de Filtro -->
    <form method="GET" action="anulaciones.php" class="row g-3 align-items-end mb-4 bg-light p-3 rounded shadow-sm">
        <div class="col-md-4">
            <label for="filtroEstado" class="form-label fw-bold">Filtrar por Estado:</label>
            <select name="estado" id="filtroEstado" class="form-select">
                <option value="">-- Mostrar Todos --</option>
                <option value="pendiente" <?= ($estado_seleccionado == 'pendiente' ? 'selected' : '') ?>>Pendiente</option>
                <option value="grabado" <?= ($estado_seleccionado == 'grabado' ? 'selected' : '') ?>>Grabado (Activo)</option>
                <option value="anulado" <?= ($estado_seleccionado == 'anulado' ? 'selected' : '') ?>>Anulado</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filtrar
            </button>
            <?php if ($estado_seleccionado): ?>
                <a href="anulaciones.php" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-sync-alt"></i> Limpiar Filtro
                </a>
            <?php endif; ?>
        </div>
    </form>
    
    <hr>

    <!-- Manejo de Mensajes (Éxito o Error de Anulación) -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla de Resultados -->
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID Ingreso</th>
                        <th>Tipo de Ingreso</th>
                        <th>Fecha Ingreso</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody> 
                    <?php while($fila = $resultado->fetch_assoc()): 
                        // Lógica de colores (igual que en consulta_ingresos.php)
                        $estado_db = strtolower(trim($fila['estado'])); 
                        $estado_display = htmlspecialchars($fila['estado']);
                        
                        $clase_estado = 'bg-secondary'; 

                        if ($estado_db == 'anulado') {
                            $clase_estado = 'bg-danger'; // Rojo para Anulado
                        } elseif ($estado_db == 'grabado') {
                            $clase_estado = 'bg-success'; // Verde para Grabado (Completado)
                        } elseif ($estado_db == 'pendiente') {
                            $clase_estado = 'bg-warning text-dark'; // Amarillo para Pendiente
                        } 
                    ?>
                        <tr class="text-center">
                            <td><?= $fila['ID_Ingreso'] ?></td>
                            <td><?= htmlspecialchars($fila['tipo_ingreso']) ?></td>
                            <td><?= $fila['fecha_ingreso'] ?></td>
                            <td><?= htmlspecialchars($fila['Nombre_Proveedor']) ?></td>
                            <td>$ <?= number_format($fila['total'], 2) ?></td>
                            <td><span class="badge <?= $clase_estado ?>"><?= $estado_display ?></span></td>
                            <td>
                                <a href="option_ingresos/detalle_ingreso.php?id=<?= $fila['ID_Ingreso'] ?>" class="btn btn-info btn-sm" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php 
                                // Botón de anulación solo si no está anulado (CRÍTICO: INDEPENDIZADO AQUÍ)
                                if ($estado_db != 'anulado'): 
                                ?>
                                    <a href="option_ingresos/confirmar_anulacion.php?id=<?= $fila['ID_Ingreso'] ?>" class="btn btn-danger btn-sm" title="Anular Ingreso">
                                        <i class="fas fa-times-circle"></i> Anular
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            No se encontraron ingresos registrados <?= ($estado_seleccionado) ? "con el estado: " . htmlspecialchars($estado_seleccionado) : "en la base de datos." ?>
        </div>
    <?php endif; ?>
</div>

<?php
$conexion->close();
?>
