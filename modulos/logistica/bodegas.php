<?php

// Cargar encabezado
require_once __DIR__ . "/../../public/html/encabezado.php";

// Conexión de base de datos
include("../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// Consulta de bodegas
$res_bodega = $conexion->query("SELECT * FROM bodega");
$num_reg = $res_bodega->num_rows;

if ($num_reg == 0) {
    echo "<div class='alert alert-warning'>⚠️ No hay bodegas registradas.</div>";
    mysqli_close($conexion);
    exit();
}
?>
    <!-- Encabezado de Modulo -->
<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-warehouse me-2"></i>Módulo de Gestión de Bodegas</h2>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

    <!-- Formulario de Registro -->
    <form id="form-guardar-bodega" method="POST" action="crud_bodega/guardar_bodega.php">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="nombre" class="form-label">Nombre de Bodega</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" required>
            </div>
            <div class="col-md-4">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input type="text" class="form-control" id="ubicacion" name="ubicacion" placeholder="Ubicación física" required>
            </div>
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="Disponible">Activa</option>
                    <option value="Temporal">Inactiva</option>
                    <option value="No Conforme">Mantenimiento</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save me-2"></i>Guardar Bodega
                </button>
            </div>
        </div>
    </form>

    <!-- Tabla de Bodegas Registradas -->
    <div class="mb-4">
        <h3 class="mb-3">Lista de Bodegas Registradas</h3>
        <?php if ($num_reg === 0): ?>
            <div class="alert alert-warning">No hay bodegas registradas.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Eliminar</th>
                        <th>Modificar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $res_bodega->fetch_assoc()): ?>
                        <tr>
                            <td><?= $fila['Id_Bodega'] ?></td>
                            <td><?= htmlspecialchars($fila['Nombre_Bodega']) ?></td>
                            <td><?= htmlspecialchars($fila['Ubicacion']) ?></td>
                            <td><?= htmlspecialchars($fila['Estado']) ?></td>
                            <td>
                                <a href="crud_bodega/eliminar_bodega.php?id=<?= $fila['Id_Bodega'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta bodega?')">
                                    🗑 Eliminar
                                </a>
                            </td>
                            <td>
                                <a href="crud_bodega/editar_bodega.php?id=<?= $fila['Id_Bodega'] ?>" class="btn btn-warning btn-sm">
                                    ✏️ Modificar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- ✅ Toast de Notificación -->
<?php if (isset($_GET['mensaje'])):
    $mensajes = [
        'ok'       => ['✅ Bodega registrada correctamente.', 'success'],
        'editado'  => ['✏️ Bodega actualizada.', 'info'],
        'eliminado'=> ['🗑️ Bodega eliminada.', 'danger']
    ];
    $msg = $mensajes[$_GET['mensaje']] ?? null;
    if ($msg): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastNoti" class="toast text-bg-<?= $msg[1] ?> show" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-bold"><?= $msg[0] ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; endif; ?>

<!-- ✅ Bootstrap y JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toastEl = document.getElementById('toastNoti');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }
</script>

<?php
mysqli_close($conexion);
ob_end_flush();
?>