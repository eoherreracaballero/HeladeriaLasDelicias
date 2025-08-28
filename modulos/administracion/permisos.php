<?php
ob_start();
session_start();

// Cargar encabezado y conexión
require_once __DIR__ . "/../../public/html/encabezado.php";
include(__DIR__ . "/../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// Obtener todos los perfiles
$res_perfiles = $conexion->query("SELECT * FROM perfiles ORDER BY nombre_perfil");

// Obtener todos los permisos
$res_permisos = $conexion->query("SELECT * FROM permisos ORDER BY modulo, accion");

// Obtener ID de perfil seleccionado si viene por GET
$perfil_id = isset($_GET['perfil_id']) ? intval($_GET['perfil_id']) : 0;

// Obtener permisos asignados a este perfil
$permisos_asignados = [];
if ($perfil_id > 0) {
    $stmt = $conexion->prepare("SELECT id_permiso FROM perfil_permiso WHERE id_perfil = ?");
    $stmt->bind_param("i", $perfil_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $permisos_asignados[] = $row['id_permiso'];
    }
    $stmt->close();
}
?>

<main class="container py-4">
    <h2 class="mb-4 text-primary">⚙️ Gestión de Permisos por Perfil</h2>

    <!-- Selección de perfil -->
    <form method="GET" class="mb-4 col-md-4">
        <label class="form-label">Seleccionar Perfil</label>
        <select name="perfil_id" class="form-select" onchange="this.form.submit()">
            <option value="">-- Seleccione un perfil --</option>
            <?php while ($perfil = $res_perfiles->fetch_assoc()): ?>
                <option value="<?= $perfil['id_perfil'] ?>" <?= ($perfil_id == $perfil['id_perfil']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($perfil['nombre_perfil']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($perfil_id > 0): ?>
        <form method="POST" action="crud_permisos/guardar_permisos.php">
            <input type="hidden" name="perfil_id" value="<?= $perfil_id ?>">

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Asignado</th>
                            <th>Módulo</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res_permisos->data_seek(0); // Reiniciar puntero
                        while ($permiso = $res_permisos->fetch_assoc()):
                        ?>
                        <tr class="text-center">
                            <td>
                                <input type="checkbox" name="permisos[]" value="<?= $permiso['id_permiso'] ?>"
                                    <?= in_array($permiso['id_permiso'], $permisos_asignados) ? 'checked' : '' ?>>
                            </td>
                            <td><?= htmlspecialchars($permiso['modulo']) ?></td>
                            <td><?= htmlspecialchars($permiso['accion']) ?></td>
                            <td><?= htmlspecialchars($permiso['descripcion']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-3 mt-3">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save me-2"></i> Guardar Permisos
                </button>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php
mysqli_close($conexion);
ob_end_flush();
?>

