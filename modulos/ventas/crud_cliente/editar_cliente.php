<?php
ob_start();

// incluir encabezado.php para cargar estilos y scripts
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../../public/html/tablas.php";

// Obtener el ID desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consultar cliente
$res = mysqli_query($conexion, "SELECT * FROM cliente WHERE Id_cliente = $id");


if (!$res || mysqli_num_rows($res) == 0) {
    echo "<div class='alert alert-danger'>❌ Cliente no encontrado.</div>";
    exit();
}

// Obtener datos del cliente
$cliente = mysqli_fetch_assoc($res);

// Si ya envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identificacion = $_POST['Identificacion'];
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];

    $sql = "UPDATE cliente SET 
        No_NIT='$identificacion',
        Nombre_Cliente='$nombre',
        Direccion='$direccion',
        No_Telefono='$telefono',
        Email='$correo'
        WHERE Id_cliente = $id";

    if (mysqli_query($conexion, $sql)) {
        header("Location: ../clientes.php?msg=updated");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Error al actualizar: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<main class="container p-4">
    <h2 class="mb-4 text-primary">✏️ Editar Cliente</h2>
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label>No. Identificación</label>
                <input type="number" name="Identificacion" class="form-control" value="<?= $cliente['No_NIT'] ?>" required>
            </div>
            <div class="col-md-4">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= $cliente['Nombre_Cliente'] ?>" required>
            </div>
            <div class="col-md-4">
                <label>Dirección</label>
                <input type="text" name="direccion" class="form-control" value="<?= $cliente['Direccion'] ?>" required>
            </div>
            <div class="col-md-4">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?= $cliente['No_Telefono'] ?>" required>
            </div>
            <div class="col-md-4">
                <label>Email</label>
                <input type="email" name="correo" class="form-control" value="<?= $cliente['Email'] ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
            </div>
        </div>
    </form>
</main>

<?php mysqli_close($conexion);
ob_end_flush(); // <-- esto va al final
?>