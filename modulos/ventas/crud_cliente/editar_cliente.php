<?php
ob_start();
require_once __DIR__ . "/../../../public/html/encabezado.php";
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = $conexion->prepare("SELECT * FROM cliente WHERE Id_cliente = ?");
$res->bind_param("i", $id);
$res->execute();
$cliente = $res->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sql = $conexion->prepare("UPDATE cliente SET No_NIT=?, Nombre_Cliente=?, Direccion=?, No_Telefono=?, Email=? WHERE Id_cliente=?");
    $sql->bind_param("sssssi", $_POST['Identificacion'], $_POST['nombre'], $_POST['direccion'], $_POST['telefono'], $_POST['correo'], $id);
    
    if ($sql->execute()) {
        header("Location: ../clientes.php?msg=updated");
        exit();
    }
}
?>

<main class="container p-4">
    <div class="card shadow border-0">
        <div class="card-header bg-warning py-3">
            <h4 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Editar Cliente: <?= htmlspecialchars($cliente['Nombre_Cliente']) ?></h4>
        </div>
        <div class="card-body bg-light p-4">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">No. Identificación</label>
                        <input type="text" name="Identificacion" class="form-control" value="<?= $cliente['No_NIT'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre o Razón Social</label>
                        <input type="text" name="nombre" class="form-control" value="<?= $cliente['Nombre_Cliente'] ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-muted">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?= $cliente['Direccion'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= $cliente['No_Telefono'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted">Email</label>
                        <input type="email" name="correo" class="form-control" value="<?= $cliente['Email'] ?>" required>
                    </div>
                    <div class="col-12 mt-4 d-flex justify-content-between">
                        <a href="../clientes.php" class="btn btn-secondary px-4">Cancelar</a>
                        <button class="btn btn-primary px-5"><i class="fas fa-save me-2"></i>Actualizar Cliente</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
<?php ob_end_flush(); ?>