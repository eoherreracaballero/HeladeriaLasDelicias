<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Rutas de Inclusión
require_once __DIR__ . "/../../public/html/encabezado.php"; 
include(__DIR__ . "/../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php"; 

global $conexion;

// 1. Consultas para las tablas (Activos y Suspendidos)
$res_activos = $conexion->query("SELECT * FROM proveedor WHERE Estado = 'Activo' ORDER BY ID_Proveedor ASC");
$res_suspendidos = $conexion->query("SELECT * FROM proveedor WHERE Estado = 'Suspendido' ORDER BY ID_Proveedor ASC");
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="container-fluid p-4 fade-in" id="contenido">
    
    <?php if (isset($_GET['msg'])): ?>
        <script>
            const msgs = {
                'updated': ['¡Actualizado!', 'Proveedor modificado con éxito', 'success'],
                'suspended': ['Inactivado', 'El proveedor ha sido movido a suspendidos', 'warning'],
                'reactivated': ['¡Reactivado!', 'El proveedor está activo de nuevo', 'success'],
                'success': ['¡Registrado!', 'Nuevo proveedor creado correctamente', 'success']
            };
            const m = msgs['<?= $_GET['msg'] ?>'];
            if(m) Swal.fire(m[0], m[1], m[2]);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'existe'): ?>
        <script>Swal.fire('⚠️ Error', 'Ese NIT ya existe en el sistema', 'error');</script>
    <?php endif; ?>

    <h2 class="text-primary mb-4"><i class="fas fa-truck me-2"></i>Gestión de Proveedores</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Proveedor</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="crud_proveedor/guardar_proveedor.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">No. NIT</label>
                        <input type="number" class="form-control" name="identificacion" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Razón Social / Nombre</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ciudad</label>
                        <input type="text" class="form-control" name="ciudad" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-primary fw-bold">Asesor de Contacto</label>
                        <input type="text" class="form-control" name="asesor" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Productos / Línea de Venta</label>
                        <input type="text" class="form-control" name="productos" placeholder="Ej: Papelería, Aseo, Tecnología..." required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-success px-4 shadow-sm w-100">
                            <i class="fas fa-save me-2"></i>Guardar Proveedor
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Proveedores Activos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover tabla-datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NIT</th>
                            <th>Proveedor</th>
                            <th>Ciudad</th>
                            <th>Asesor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $res_activos->fetch_assoc()): ?>
                            <tr>
                                <td><?= $fila['ID_Proveedor'] ?></td>
                                <td><?= $fila['No_NIT'] ?></td>
                                <td><strong><?= htmlspecialchars($fila['Nombre_Proveedor']) ?></strong></td>
                                <td><?= htmlspecialchars($fila['Ciudad']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['Asesor_Contacto']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white" onclick='verDetalles(<?= json_encode($fila) ?>)' title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="crud_proveedor/editar_proveedor.php?id=<?= $fila['ID_Proveedor'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarSuspension(<?= $fila['ID_Proveedor'] ?>, '<?= addslashes($fila['Nombre_Proveedor']) ?>')" title="Inactivar">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-danger">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-danger"><i class="fas fa-user-lock me-2"></i>Proveedores Inactivos</h5>
            <span class="badge bg-danger"><?= $res_suspendidos->num_rows ?></span>
        </div>
        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>NIT</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_suspendidos->num_rows > 0): ?>
                            <?php while ($sus = $res_suspendidos->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $sus['ID_Proveedor'] ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($sus['Nombre_Proveedor']) ?></td>
                                    <td><?= htmlspecialchars($sus['No_NIT']) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info text-white" onclick='verDetalles(<?= json_encode($sus) ?>)'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" onclick="confirmarReactivacion(<?= $sus['ID_Proveedor'] ?>, '<?= addslashes($sus['Nombre_Proveedor']) ?>')">
                                            <i class="fas fa-undo me-1"></i> Reactivar
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No hay proveedores restringidos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-truck me-2"></i>Ficha de Proveedor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido"></div>
        </div>
    </div>
</div>

<script>
function verDetalles(p) {
    const html = `
        <div class="list-group list-group-flush">
            <div class="list-group-item d-flex justify-content-between"><strong>NIT:</strong> <span>${p.No_NIT}</span></div>
            <div class="list-group-item"><strong>Razón Social:</strong><br><span class="text-primary fw-bold">${p.Nombre_Proveedor}</span></div>
            <div class="list-group-item d-flex justify-content-between"><strong>Ciudad:</strong> <span>${p.Ciudad}</span></div>
            <div class="list-group-item d-flex justify-content-between"><strong>Teléfono:</strong> <span>${p.Tel_Contacto}</span></div>
            <div class="list-group-item"><strong>Dirección:</strong><br><span>${p.Direccion}</span></div>
            <div class="list-group-item bg-light text-center"><strong>Contacto Comercial</strong></div>
            <div class="list-group-item d-flex justify-content-between"><strong>Asesor:</strong> <span>${p.Asesor_Contacto}</span></div>
            <div class="list-group-item"><strong>Línea de Productos:</strong><br><small>${p.Productos_Venta}</small></div>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <strong>Estado:</strong> <span class="badge ${p.Estado == 'Activo' ? 'bg-success' : 'bg-danger'}">${p.Estado}</span>
            </div>
        </div>`;
    document.getElementById('detalleContenido').innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalDetalles')).show();
}

function confirmarSuspension(id, nombre) {
    Swal.fire({
        title: '¿Inactivar proveedor?',
        text: `¿Desea suspender a ${nombre}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_proveedor/eliminar_proveedor.php?id=' + id; });
}

function confirmarReactivacion(id, nombre) {
    Swal.fire({
        title: '¿Reactivar proveedor?',
        text: `¿Habilitar de nuevo a ${nombre}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Sí, reactivar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_proveedor/reactivar_proveedor.php?id=' + id; });
}
</script>

<?php mysqli_close($conexion); ?>
