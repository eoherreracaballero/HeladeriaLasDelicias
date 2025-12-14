<?php
session_start();
// Cargar encabezado
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión de base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// 1. Obtener y validar el ID
$idIngreso = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idIngreso <= 0) {
    // Usar la función die() como manejo de error simple por ahora
    die("ID de ingreso inválido. <a href='consulta_ingresos.php'>Volver a la consulta</a>");
}

// 2. Consulta ingreso para confirmación (usando sentencia preparada, ¡bien hecho!)
$sqlIngreso = "SELECT i.ID_Ingreso, i.Tipo_Ingreso, i.Fecha_Ingreso, i.Total, p.Nombre_Proveedor, i.estado
               FROM ingreso_producto i
               JOIN proveedor p ON i.ID_Proveedor = p.ID_Proveedor
               WHERE i.ID_Ingreso = ?";
$stmtIngreso = $conexion->prepare($sqlIngreso);
$stmtIngreso->bind_param("i", $idIngreso);
$stmtIngreso->execute(); 
$resultIngreso = $stmtIngreso->get_result();
$ingreso = $resultIngreso->fetch_assoc();
$stmtIngreso->close();

if (!$ingreso) {
    die("Ingreso #{$idIngreso} no encontrado. <a href='consulta_ingresos.php'>Volver a la consulta</a>");
}

if ($ingreso['estado'] == 'Anulado') {
    die("Este ingreso ya se encuentra anulado. <a href='consulta_ingresos.php'>Volver a la consulta</a>");
}
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <div class="container py-5">
        <div class="card shadow-lg border-danger rounded-3">
            <div class="card-header bg-danger text-white text-center">
                <h3 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Confirmar Anulación de Ingreso</h3>
            </div>
            <div class="card-body">
                <p class="lead text-center text-danger">
                    Estás a punto de anular el Ingreso **#<?= $idIngreso ?>**. Esta acción es **IRREVERSIBLE** y los productos serán **RESTADOS** del inventario.
                </p>
                <hr>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Tipo de Ingreso:</strong> <span class="badge bg-primary"><?= htmlspecialchars($ingreso['Tipo_Ingreso']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Proveedor:</strong> <?= htmlspecialchars($ingreso['Nombre_Proveedor']) ?>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Fecha de Ingreso:</strong> <?= htmlspecialchars($ingreso['Fecha_Ingreso']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Total:</strong> <span class="badge bg-dark">$<?= number_format($ingreso['Total'], 2) ?></span>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Motivo de Anulación:</strong> 
                    <p>Por favor, especifica brevemente la razón de la anulación para mantener un registro claro.</p>
                </div>

                <form action="procesar_anulacion.php" method="POST">
                    <input type="hidden" name="id_ingreso" value="<?= $idIngreso ?>">
                    <div class="mb-3">
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required placeholder="Ej. Documento de proveedor errado, Ingreso duplicado, Ajuste manual por error de conteo."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="consulta_ingresos.php" class="btn btn-secondary">
                            <i class="fas fa-ban"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Sí, Anular Ingreso #<?= $idIngreso ?>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</main>

<?php
$conexion->close();
?>
