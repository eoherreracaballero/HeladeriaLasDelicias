<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Rutas de Inclusión
require_once __DIR__ . "/../../public/html/encabezado.php"; 
include(__DIR__ . "/../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php"; 

global $conexion;

// 1. Consultas para las tablas
$sql_activos = "SELECT u.*, p.nombre_perfil FROM usuario u INNER JOIN perfiles p ON u.id_perfil = p.id_perfil WHERE u.Estado = 'Activo' ORDER BY u.id_usuario ASC";
$res_activos = $conexion->query($sql_activos);

$sql_suspendidos = "SELECT u.*, p.nombre_perfil FROM usuario u INNER JOIN perfiles p ON u.id_perfil = p.id_perfil WHERE u.Estado = 'Suspendido' ORDER BY u.id_usuario ASC";
$res_suspendidos = $conexion->query($sql_suspendidos);

$res_perfiles = $conexion->query("SELECT id_perfil, nombre_perfil FROM perfiles");
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="container-fluid p-4 fade-in" id="contenido">
    
    <?php if (isset($_GET['msg'])): ?>
        <script>
            const msgs = {
                'updated': ['¡Actualizado!', 'Usuario modificado con éxito', 'success'],
                'suspended': ['Suspendido', 'El acceso ha sido inhabilitado', 'warning'],
                'reactivated': ['¡Activado!', 'El usuario puede ingresar de nuevo', 'success']
            };
            const m = msgs['<?= $_GET['msg'] ?>'];
            if(m) Swal.fire(m[0], m[1], m[2]);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <script>Swal.fire('¡Registrado!', 'Nuevo usuario creado correctamente', 'success');</script>
    <?php endif; ?>

    <h2 class="text-primary mb-4"><i class="fas fa-users-cog me-2"></i>Gestión de Usuarios</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registrar Nuevo Personal</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="crud_usuario/guardar_usuario.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">No. Identificación</label>
                        <input type="number" class="form-control" name="Identificacion" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" class="form-control" name="ciudad" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teléfono</label>
                        <input type="number" class="form-control" name="telefono" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cargo</label>
                        <input type="text" class="form-control" name="cargo" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Perfil / Rol</label>
                        <select class="form-select" name="id_perfil" required>
                            <option value="">Seleccione...</option>
                            <?php while ($p = $res_perfiles->fetch_assoc()): ?>
                                <option value="<?= $p['id_perfil'] ?>"><?= $p['nombre_perfil'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contraseña inicial</label>
                        <input type="password" class="form-control" name="clave" required>
                    </div>
                    <div class="col-md-9 d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-success px-4"><i class="fas fa-save me-2"></i>Guardar Usuario</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Personal Activo</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover tabla-datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Perfil</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $res_activos->fetch_assoc()): ?>
                            <tr>
                                <td><?= $fila['id_usuario'] ?></td>
                                <td><strong><?= htmlspecialchars($fila['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($fila['cargo']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['nombre_perfil']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white" onclick='verDetalles(<?= json_encode($fila) ?>)' title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="crud_usuario/editar_usuario.php?id=<?= $fila['id_usuario'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarSuspension(<?= $fila['id_usuario'] ?>, '<?= $fila['nombre'] ?>')" title="Suspender">
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
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0 text-danger"><i class="fas fa-user-lock me-2"></i>Acceso Restringido (Suspendidos)</h5>
        </div>
        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_suspendidos->num_rows > 0): ?>
                            <?php while ($sus = $res_suspendidos->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $sus['id_usuario'] ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($sus['nombre']) ?></td>
                                    <td><?= htmlspecialchars($sus['cargo']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info text-white" onclick='verDetalles(<?= json_encode($sus) ?>)'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" onclick="confirmarReactivacion(<?= $sus['id_usuario'] ?>)">
                                            <i class="fas fa-user-plus me-1"></i> Reactivar
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No hay cuentas suspendidas.</td></tr>
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
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Información del Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                </div>
        </div>
    </div>
</div>

<script>
function verDetalles(u) {
    const html = `
        <div class="list-group list-group-flush">
            <div class="list-group-item"><strong>Identificación:</strong> <span class="float-end">${u.no_identificacion}</span></div>
            <div class="list-group-item"><strong>Nombre:</strong> <span class="float-end">${u.nombre}</span></div>
            <div class="list-group-item"><strong>Email:</strong> <span class="float-end text-primary">${u.email}</span></div>
            <div class="list-group-item"><strong>Teléfono:</strong> <span class="float-end">${u.telefono}</span></div>
            <div class="list-group-item"><strong>Ciudad:</strong> <span class="float-end">${u.ciudad}</span></div>
            <div class="list-group-item"><strong>Dirección:</strong> <span class="float-end text-truncate" style="max-width: 200px;">${u.direccion}</span></div>
            <div class="list-group-item"><strong>Cargo:</strong> <span class="float-end">${u.cargo}</span></div>
            <div class="list-group-item"><strong>Perfil:</strong> <span class="badge bg-secondary float-end">${u.nombre_perfil}</span></div>
            <div class="list-group-item"><strong>Estado:</strong> <span class="badge ${u.Estado == 'Activo' ? 'bg-success' : 'bg-danger'} float-end">${u.Estado}</span></div>
        </div>`;
    document.getElementById('detalleContenido').innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalDetalles')).show();
}

function confirmarSuspension(id, nombre) {
    Swal.fire({
        title: '¿Suspender a ' + nombre + '?',
        text: "Perderá acceso al sistema inmediatamente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, suspender',
        cancelButtonText: 'Cancelar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_usuario/eliminar_usuario.php?id=' + id; });
}

function confirmarReactivacion(id) {
    Swal.fire({
        title: '¿Reactivar acceso?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Sí, reactivar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_usuario/reactivar_usuario.php?id=' + id; });
}
</script>

<?php mysqli_close($conexion); ?>