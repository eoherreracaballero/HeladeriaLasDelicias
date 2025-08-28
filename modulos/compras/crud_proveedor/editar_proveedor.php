<?php
ob_start();

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// incluir encabezado.php para cargar estilos y scripts
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Estilos para tablas
require_once __DIR__ . "/../../../public/html/tablas.php";

// Validar y obtener el ID del proveedor desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consultar proveedor por ID
$consulta = "SELECT * FROM proveedor WHERE ID_Proveedor = $id";
$resultado = mysqli_query($conexion, $consulta);

// Verificar si se encontró el proveedor
if (!$resultado || mysqli_num_rows($resultado) === 0) {
    echo "<div class='alert alert-danger'>⚠️ Proveedor no encontrado.</div>";
    exit();
}

// Obtener datos del proveedor
$proveedor = mysqli_fetch_assoc($resultado);

// Si se envió el formulario con método POST, actualizar proveedor
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nit       = $_POST['Identificacion'];
    $nombre    = $_POST['nombre'];
    $ciudad    = $_POST['ciudad'];
    $direccion = $_POST['Direccion'];
    $telefono  = $_POST['telefono'];
    $asesor    = $_POST['asesor'];
    $productos = $_POST['productos'];

    $sql_update = "UPDATE proveedor SET 
        No_NIT = '$nit',
        Nombre_Proveedor = '$nombre',
        Ciudad = '$ciudad',
        Direccion = '$direccion',
        Tel_Contacto = '$telefono',
        Asesor_Contacto = '$asesor',
        Productos_Venta = '$productos'
        WHERE ID_Proveedor = $id";

    if (mysqli_query($conexion, $sql_update)) {
        header("Location: ../proveedores.php?msg=updated");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Error al actualizar: " . mysqli_error($conexion) . "</div>";
    }
} 
?>

<main class="container p-4">
    <h2 class="text-primary mb-4"><i class="fas fa-edit me-2"></i>Editar Proveedor</h2>
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="Identificacion" class="form-label">No. NIT</label>
                <input type="number" class="form-control" name="Identificacion" id="Identificacion"
                       value="<?= $proveedor['No_NIT'] ?>" required>
            </div>
            <div class="col-md-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre"
                       value="<?= $proveedor['Nombre_Proveedor'] ?>" required>
            </div>
            <div class="col-md-4">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" id="ciudad"
                       value="<?= $proveedor['Ciudad'] ?>" required>
            </div>
            <div class="col-md-4">
                <label for="Direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" name="Direccion" id="Direccion"
                       value="<?= $proveedor['Direccion'] ?>" required>
            </div>
            <div class="col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono"
                       value="<?= $proveedor['Tel_Contacto'] ?>" required>
            </div>
            <div class="col-md-4">
                <label for="asesor" class="form-label">Asesor</label>
                <input type="text" class="form-control" name="asesor" id="asesor"
                       value="<?= $proveedor['Asesor_Contacto'] ?>" required>
            </div>
            <div class="col-md-8">
                <label for="productos" class="form-label">Productos</label>
                <input type="text" class="form-control" name="productos" id="productos"
                       value="<?= $proveedor['Productos_Venta'] ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</main>
<?php mysqli_close($conexion); ?>
