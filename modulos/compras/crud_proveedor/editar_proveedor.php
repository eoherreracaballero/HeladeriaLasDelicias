<?php
// 1. PROCESAMIENTO DE DATOS PRIMERO 
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si se envió el formulario, procesamos la actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nit       = $_POST['nit'];
    $nom       = $_POST['nombre'];
    $ciu       = $_POST['ciudad'];
    $dir       = $_POST['direccion'];
    $tel       = $_POST['telefono'];
    $ase       = $_POST['asesor'];
    $pro       = $_POST['productos'];

    $sql_update = "UPDATE proveedor SET 
        No_NIT = '$nit',
        Nombre_Proveedor = '$nom',
        Ciudad = '$ciu',
        Direccion = '$dir',
        Tel_Contacto = '$tel',
        Asesor_Contacto = '$ase',
        Productos_Venta = '$pro'
        WHERE ID_Proveedor = $id";

    if (mysqli_query($conexion, $sql_update)) {
        // Ahora el header funcionará porque no hay HTML enviado aún
        header("Location: ../proveedores.php?msg=updated");
        exit();
    } else {
        $error_db = mysqli_error($conexion);
    }
}

// 2. CARGA DE INTERFAZ DESPUÉS
require_once __DIR__ . "/../../../public/html/encabezado.php";
require_once __DIR__ . "/../../../public/html/tablas.php";

// Consultar datos actuales para mostrar en los inputs
$res = $conexion->query("SELECT * FROM proveedor WHERE ID_Proveedor = $id");
$p = $res->fetch_assoc();

if (!$p) {
    echo "<div class='alert alert-danger m-4'>⚠️ Proveedor no encontrado.</div>";
    exit();
}
?>

<main class="container p-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if(isset($error_db)): ?>
                <div class="alert alert-danger">❌ Error: <?= $error_db ?></div>
            <?php endif; ?>

            <div class="card shadow border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-edit me-2"></i>Editar Ficha del Proveedor
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIT</label>
                                <input type="number" class="form-control" name="nit" value="<?= $p['No_NIT'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre / Razón Social</label>
                                <input type="text" class="form-control" name="nombre" value="<?= $p['Nombre_Proveedor'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ciudad</label>
                                <input type="text" class="form-control" name="ciudad" value="<?= $p['Ciudad'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono" value="<?= $p['Tel_Contacto'] ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="direccion" value="<?= $p['Direccion'] ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-primary fw-bold">Asesor de Contacto</label>
                                <input type="text" class="form-control" name="asesor" value="<?= $p['Asesor_Contacto'] ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Productos que distribuye</label>
                                <textarea class="form-control" name="productos" rows="2" required><?= $p['Productos_Venta'] ?></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-between mt-4">
                                <a href="../proveedores.php" class="btn btn-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-warning px-4 fw-bold">Actualizar Datos</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php mysqli_close($conexion); ?>