<?php
ob_start(); 
session_start(); 

// 1. INCLUSIÓN DE COMPONENTES Y CONEXIÓN
require_once __DIR__ . "/../../public/html/encabezado.php";
include(__DIR__ . "/../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php";
global $conexion;

// 2. CONSULTAS SEPARADAS (Activas vs Suspendidas)
$sql_activas = "SELECT * FROM bodega WHERE Estado != 'Inactiva' ORDER BY Id_Bodega ASC";
$res_activas = $conexion->query($sql_activas);

$sql_suspendidas = "SELECT * FROM bodega WHERE Estado = 'Inactiva' ORDER BY Id_Bodega ASC";
$res_suspendidas = $conexion->query($sql_suspendidas);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Estilos personalizados para legibilidad */
    .table-data { font-size: 1.1rem; }
    .btn-action { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; }
    .nombre-bodega { font-size: 1.2rem; color: #2c3e50; }
    .badge-status { font-size: 0.95rem; padding: 8px 15px; }
</style>

<main class="container-fluid p-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary m-0 fw-bold"><i class="fas fa-warehouse me-2"></i>Gestión de Bodegas</h2>
        <span class="badge bg-primary px-3 py-2 shadow-sm fs-6">Bodegas Operativas: <?= $res_activas->num_rows ?></span>
    </div>

    <div class="card shadow-sm mb-5 border-0">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 small text-uppercase fw-bold"><i class="fas fa-plus-circle me-2"></i>Registrar Nueva Bodega</h5>
        </div>
        <div class="card-body bg-light border">
            <form method="POST" action="crud_bodega/guardar_bodega.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Nombre de Bodega</label>
                        <input type="text" class="form-control form-control-lg fs-6" name="nombre" placeholder="Ej: Bodega Central" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Ubicación Física</label>
                        <input type="text" class="form-control form-control-lg fs-6" name="ubicacion" placeholder="Ej: Av. Principal 123" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase">Estado</label>
                        <select class="form-select form-select-lg fs-6" name="estado" required>
                            <option value="Activa">Activa</option>
                            <option value="Inactiva">Inactiva</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow">
                            <i class="fas fa-save me-2"></i>GUARDAR
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-dark text-white py-3">
            <h6 class="mb-0 fw-bold text-uppercase small"><i class="fas fa-toggle-on me-2 text-info"></i>Bodegas Disponibles</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light text-uppercase small border-bottom">
                        <tr>
                            <th style="width: 100px;">ID</th>
                            <th class="text-start">Nombre / Identificación</th>
                            <th>Ubicación</th>
                            <th>Estado Actual</th>
                            <th style="width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-data">
                        <?php while ($fila = $res_activas->fetch_assoc()): 
                            $statusColor = ($fila['Estado'] == 'Disponible') ? 'bg-success' : 'bg-warning text-dark';
                        ?>
                            <tr>
                                <td><span class="badge bg-white text-dark border p-2 shadow-sm">#<?= $fila['Id_Bodega'] ?></span></td>
                                <td class="text-start fw-bold nombre-bodega"><?= htmlspecialchars($fila['Nombre_Bodega']) ?></td>
                                <td><i class="fas fa-map-marker-alt text-danger me-2"></i><?= htmlspecialchars($fila['Ubicacion']) ?></td>
                                <td><span class="badge <?= $statusColor ?> badge-status shadow-sm"><?= $fila['Estado'] ?></span></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="crud_bodega/editar_bodega.php?id=<?= $fila['Id_Bodega'] ?>" class="btn btn-warning shadow-sm btn-action" title="Editar Información">
                                            <i class="fas fa-edit text-dark"></i>
                                        </a>
                                        <button class="btn btn-danger shadow-sm btn-action" onclick="confirmarSuspension(<?= $fila['Id_Bodega'] ?>, '<?= $fila['Nombre_Bodega'] ?>')" title="Suspender Bodega">
                                            <i class="fas fa-ban text-white"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-top border-danger border-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-danger text-uppercase small"><i class="fas fa-archive me-2"></i>Archivo de Bodegas Inactivas</h6>
        </div>
        <div class="card-body p-0 bg-light">
            <?php if ($res_suspendidas->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody class="table-data">
                        <?php while ($sus = $res_suspendidas->fetch_assoc()): ?>
                            <tr class="border-bottom">
                                <td class="ps-4 text-muted small" style="width: 120px;">ID: #<?= $sus['Id_Bodega'] ?></td>
                                <td class="fw-bold text-secondary fs-5"><?= htmlspecialchars($sus['Nombre_Bodega']) ?></td>
                                <td class="text-muted italic"><?= htmlspecialchars($sus['Ubicacion']) ?></td>
                                <td class="text-end pe-4 py-3">
                                    <button class="btn btn-success btn-sm fw-bold shadow-sm px-4" onclick="confirmarReactivacion(<?= $sus['Id_Bodega'] ?>)">
                                        <i class="fas fa-check-circle me-2"></i>REACTIVAR BODEGA
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-info-circle mb-2 fa-2x"></i>
                    <p class="mb-0 italic">No hay bodegas suspendidas en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Confirmación para Suspender
function confirmarSuspension(id, nombre) {
    Swal.fire({
        title: '¿Suspender Bodega?',
        text: `La bodega "${nombre}" será movida al archivo y no podrá recibir stock nuevo.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-ban me-2"></i>Sí, suspender',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_bodega/suspender_bodega.php?id=' + id; });
}

// Confirmación para Reactivar
function confirmarReactivacion(id) {
    Swal.fire({
        title: '¿Reactivar Bodega?',
        text: 'Esta bodega volverá a aparecer en las listas de inventario activo.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: '<i class="fas fa-undo me-2"></i>Sí, reactivar ahora',
        cancelButtonText: 'Cancelar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_bodega/reactivar_bodega.php?id=' + id; });
}

// Mensajes Automáticos
<?php if (isset($_GET['mensaje'])): ?>
    const msgs = {
        'ok': { t: '¡Excelente!', m: 'Bodega registrada correctamente.', i: 'success' },
        'editado': { t: 'Actualizado', m: 'Los cambios se guardaron con éxito.', i: 'info' },
        'suspendido': { t: 'Suspendida', m: 'La bodega ha sido movida al archivo.', i: 'warning' },
        'activado': { t: 'Reactivada', m: 'La bodega está operativa nuevamente.', i: 'success' }
    };
    const msgData = msgs['<?= $_GET['mensaje'] ?>'];
    if (msgData) {
        Swal.fire({ title: msgData.t, text: msgData.m, icon: msgData.i, confirmButtonColor: '#3085d6' });
    }
<?php endif; ?>
</script>

<?php 
mysqli_close($conexion); 
ob_end_flush(); 
?>