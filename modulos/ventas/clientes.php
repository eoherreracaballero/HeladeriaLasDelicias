<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Rutas de Inclusión
require_once __DIR__ . "/../../public/html/encabezado.php"; 
include(__DIR__ . "/../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php"; 

global $conexion;

// 1. Consultas para las tablas (Activos y Suspendidos)
// Asegúrate de que los nombres de columna (Ciudad, Estado, etc.) coincidan con tu DB
$res_activos = $conexion->query("SELECT * FROM cliente WHERE Estado = 'Activo' ORDER BY Id_cliente ASC");
$res_suspendidos = $conexion->query("SELECT * FROM cliente WHERE Estado = 'Suspendido' ORDER BY Id_cliente ASC");
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="container-fluid p-4 fade-in" id="contenido">
    
    <?php if (isset($_GET['msg'])): ?>
        <script>
            const msgs = {
                'updated': ['¡Actualizado!', 'Datos del cliente modificados con éxito', 'success'],
                'suspended': ['Inactivado', 'El cliente ha sido movido a la lista de restricción', 'warning'],
                'reactivated': ['¡Reactivado!', 'El cliente está disponible para ventas de nuevo', 'success']
            };
            const m = msgs['<?= $_GET['msg'] ?>'];
            if(m) Swal.fire(m[0], m[1], m[2]);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <script>Swal.fire('¡Registrado!', 'Nuevo cliente creado correctamente', 'success');</script>
    <?php endif; ?>

    <h2 class="text-primary mb-4"><i class="fas fa-user-tie me-2"></i>Gestión de Clientes</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Cliente</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="crud_cliente/guardar_cliente.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">No. Identificación</label>
                        <input type="number" class="form-control" name="Identificacion" placeholder="NIT o CC" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Nombre o Razón Social</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Nombre completo" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">E-mail</label>
                        <input type="email" class="form-control" name="correo" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ciudad</label>
                        <input type="text" class="form-control" name="ciudad" placeholder="Ciudad de origen" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Dirección</label>
                        <input type="text" class="form-control" name="direccion" placeholder="Dirección completa" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" placeholder="Número de contacto" required>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-5 shadow-sm">
                            <i class="fas fa-save me-2"></i>Guardar Cliente
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Clientes Activos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover tabla-datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NIT / CC</th>
                            <th>Nombre</th>
                            <th>Ciudad</th>
                            <th>Email</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $res_activos->fetch_assoc()): ?>
                            <tr>
                                <td><?= $fila['Id_cliente'] ?></td>
                                <td><?= $fila['No_NIT'] ?></td>
                                <td><strong><?= htmlspecialchars($fila['Nombre_Cliente']) ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['Ciudad'] ?? 'N/A') ?></span></td>
                                <td><?= htmlspecialchars($fila['Email']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white" onclick='verDetalles(<?= json_encode($fila) ?>)' title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="crud_cliente/editar_cliente.php?id=<?= $fila['Id_cliente'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarSuspension(<?= $fila['Id_cliente'] ?>, '<?= addslashes($fila['Nombre_Cliente']) ?>')" title="Inactivar">
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
            <h5 class="mb-0 text-danger"><i class="fas fa-user-lock me-2"></i>Acceso Restringido (Suspendidos)</h5>
            <span class="badge bg-danger"><?= $res_suspendidos->num_rows ?> Inactivos</span>
        </div>
        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Ciudad</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_suspendidos->num_rows > 0): ?>
                            <?php while ($sus = $res_suspendidos->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $sus['Id_cliente'] ?></td>
                                    <td class="text-muted fw-bold"><?= htmlspecialchars($sus['Nombre_Cliente']) ?></td>
                                    <td><?= htmlspecialchars($sus['Ciudad'] ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info text-white" onclick='verDetalles(<?= json_encode($sus) ?>)' title="Ver Ficha">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success ms-1" onclick="confirmarReactivacion(<?= $sus['Id_cliente'] ?>, '<?= addslashes($sus['Nombre_Cliente']) ?>')" title="Reactivar">
                                            <i class="fas fa-undo-alt me-1"></i> Reactivar
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-info-circle me-2"></i>No hay clientes en lista de restricción.</td></tr>
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
                <h5 class="modal-title"><i class="fas fa-address-card me-2"></i>Ficha del Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido"></div>
        </div>
    </div>
</div>

<script>
// Buscador global 
document.getElementById('searchGlobal')?.addEventListener('input', function () {
    const text = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(text) ? '' : 'none';
    });
});

function verDetalles(c) {
    const html = `
        <div class="list-group list-group-flush">
            <div class="list-group-item d-flex justify-content-between"><strong>ID Registro:</strong> <span>#${c.Id_cliente}</span></div>
            <div class="list-group-item d-flex justify-content-between"><strong>NIT / CC:</strong> <span>${c.No_NIT}</span></div>
            <div class="list-group-item"><strong>Nombre:</strong><br><span class="text-primary fw-bold">${c.Nombre_Cliente}</span></div>
            <div class="list-group-item d-flex justify-content-between"><strong>Email:</strong> <span class="text-lowercase">${c.Email}</span></div>
            <div class="list-group-item d-flex justify-content-between"><strong>Ciudad:</strong> <span>${c.Ciudad || 'No especificada'}</span></div>
            <div class="list-group-item d-flex justify-content-between"><strong>Teléfono:</strong> <span>${c.No_Telefono}</span></div>
            <div class="list-group-item"><strong>Dirección:</strong><br><small class="text-muted">${c.Direccion}</small></div>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <strong>Estado:</strong> <span class="badge ${c.Estado == 'Activo' ? 'bg-success' : 'bg-danger'}">${c.Estado}</span>
            </div>
        </div>`;
    document.getElementById('detalleContenido').innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalDetalles')).show();
}

function confirmarSuspension(id, nombre) {
    Swal.fire({
        title: '¿Inactivar a ' + nombre + '?',
        text: "No aparecerá en el módulo de facturación.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_cliente/eliminar_cliente.php?id=' + id; });
}

function confirmarReactivacion(id, nombre) {
    Swal.fire({
        title: '¿Reactivar cliente?',
        text: '¿Habilitar de nuevo a ' + nombre + '?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Sí, reactivar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_cliente/reactivar_cliente.php?id=' + id; });
}
</script>

<?php mysqli_close($conexion); ?>