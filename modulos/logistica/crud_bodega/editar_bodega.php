<?php
ob_start();

// incluir encabezado.php para cargar estilos y scripts
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../../public/html/tablas.php";

/* 1️⃣ Validar que llegue el ID */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conexion->prepare("SELECT * FROM bodega WHERE Id_Bodega = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo "<div class='alert alert-danger'>❌ Bodega no encontrada.</div>";
    exit();
}

$bodega = $res->fetch_assoc();
$stmt->close();

/* 2️⃣ Procesar actualización */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre    = trim($_POST['nombre']);
    $ubicacion = trim($_POST['ubicacion']);
    $estado    = trim($_POST['estado']);

    if ($nombre === '' || $ubicacion === '' || $estado === '') {
        echo "<div class='alert alert-danger'>❌ Todos los campos son obligatorios.</div>";
    } else {
        $sql = "UPDATE bodega 
                SET Nombre_Bodega = ?, Ubicacion = ?, Estado = ? 
                WHERE Id_Bodega = ?";

        $upd = $conexion->prepare($sql);
        $upd->bind_param("sssi", $nombre, $ubicacion, $estado, $id);

        if ($upd->execute()) {
            header("Location: ../bodegas.php?mensaje=editado");
            exit();
        } else {
            echo "<div class='alert alert-danger'>❌ Error al actualizar: " . $upd->error . "</div>";
        }
        $upd->close();
    }
}
?>

<main class="container p-4">
    <h2 class="mb-4 text-primary">✏️ Editar Bodega</h2>
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nombre de la Bodega</label>
                <input type="text" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($bodega['Nombre_Bodega']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control"
                       value="<?= htmlspecialchars($bodega['Ubicacion']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select" required>
                    <option value="Disponible" <?= $bodega['Estado'] === 'Disponible' ? 'selected' : '' ?>>Activa</option>
                    <option value="Temporal" <?= $bodega['Estado'] === 'Temporal' ? 'selected' : '' ?>>Inactiva</option>
                    <option value="No Conforme" <?= $bodega['Estado'] === 'No Conforme' ? 'selected' : '' ?>>Mantenimiento</option>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</main>

<?php
mysqli_close($conexion);
ob_end_flush();
?>

