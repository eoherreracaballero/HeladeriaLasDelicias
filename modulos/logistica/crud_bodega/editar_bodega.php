<?php
ob_start();
require_once __DIR__ . "/../../../public/html/encabezado.php";
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = $conexion->query("SELECT * FROM bodega WHERE Id_Bodega = $id");
$bodega = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre    = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'];
    $estado    = $_POST['estado'];

    $upd = $conexion->prepare("UPDATE bodega SET Nombre_Bodega=?, Ubicacion=?, Estado=? WHERE Id_Bodega=?");
    $upd->bind_param("sssi", $nombre, $ubicacion, $estado, $id);

    if ($upd->execute()) {
        header("Location: ../bodegas.php?mensaje=editado");
        exit();
    }
}
?>

<main class="container p-4">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Bodega: <?= htmlspecialchars($bodega['Nombre_Bodega']) ?></h5>
        </div>
        <div class="card-body bg-light">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($bodega['Nombre_Bodega']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ubicación</label>
                        <input type="text" name="ubicacion" class="form-control" value="<?= htmlspecialchars($bodega['Ubicacion']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="Disponible" <?= $bodega['Estado']=='Disponible'?'selected':'' ?>>Disponible</option>
                            <option value="No Conforme" <?= $bodega['Estado']=='No Conforme'?'selected':'' ?>>Mantenimiento</option>
                            <option value="Inactiva" <?= $bodega['Estado']=='Inactiva'?'selected':'' ?>>Inactiva (Suspender)</option>
                        </select>
                    </div>
                    <div class="col-12 text-end border-top pt-3 mt-4">
                        <a href="../bodegas.php" class="btn btn-secondary shadow-sm me-2">Cancelar</a>
                        <button class="btn btn-primary shadow-sm px-4">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php ob_end_flush(); ?>