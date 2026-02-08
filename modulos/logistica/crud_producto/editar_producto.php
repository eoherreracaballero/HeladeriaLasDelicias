<?php
ob_start();
session_start();
include(__DIR__ . "/../../../app/db/conexion.php");

// --- PROCESAR ACTUALIZACIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recogemos los datos asegurándonos de que coincidan con los 'name' del formulario
    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $pvp = $_POST['pvp'];
    $costo = $_POST['costo_unitario'];
    $tipo = $_POST['tipo']; // Antes podía estar faltando en el HTML
    $categoria = $_POST['categoria'];
    $empaque = $_POST['und_empaque'];
    $marca = $_POST['marca'];
    $proveedor = intval($_POST['proveedor']); // Aseguramos que sea entero para la FK
    $bodega = intval($_POST['bodega']);

    $update_img = "";
    if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == 0) {
        $nombre_img = "prod_" . time() . ".jpg";
        $ruta_fisica = "../../../public/img/productos/" . $nombre_img;
        if (move_uploaded_file($_FILES['imagen_producto']['tmp_name'], $ruta_fisica)) {
            $ruta_db = "public/img/productos/" . $nombre_img;
            $update_img = ", Ruta_Imagen = '$ruta_db'";
        }
    }

    // Consulta SQL robusta
    $sql = "UPDATE producto SET 
            Nombre_Producto = '$nombre', 
            PVP = '$pvp', 
            Costo_Unitario = '$costo',
            Tipo = '$tipo', 
            Categoria = '$categoria', 
            Und_Empaque = '$empaque',
            Marca = '$marca', 
            ID_Proveedor = '$proveedor', 
            ID_Bodega = '$bodega' 
            $update_img 
            WHERE ID_Producto = $id";

    if ($conexion->query($sql)) {
        header("Location: ../productos.php?msg=updated");
        exit();
    } else {
        $error_db = "Error en base de datos: " . $conexion->error;
    }
}

// --- MOSTRAR FORMULARIO ---
require_once __DIR__ . "/../../../public/html/encabezado.php";
$id = intval($_GET['ID_Producto'] ?? $_GET['id']);
$res = $conexion->query("SELECT * FROM producto WHERE ID_Producto = $id");
$p = $res->fetch_assoc();

// Cargar listas para los selects
$bodegas = $conexion->query("SELECT Id_Bodega, Nombre_Bodega FROM bodega");
$proveedores = $conexion->query("SELECT ID_Proveedor, Nombre_Proveedor FROM proveedor");
?>

<main class="container p-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <?php if(isset($error_db)): ?>
                <div class="alert alert-danger"><?= $error_db ?></div>
            <?php endif; ?>

            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-pen-square me-2"></i>Edición Técnica: <?= htmlspecialchars($p['Nombre_Producto']) ?></h5>
                    <span class="badge bg-dark">ID: #<?= $p['ID_Producto'] ?></span>
                </div>
                <div class="card-body">
                    <form action="editar_producto.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $p['ID_Producto'] ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre del Producto</label>
                                <input type="text" class="form-control" name="nombre" value="<?= $p['Nombre_Producto'] ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Marca</label>
                                <input type="text" class="form-control" name="marca" value="<?= $p['Marca'] ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tipo</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="Mercancia" <?= $p['Tipo'] == 'Mercancia' ? 'selected' : '' ?>>Mercancía</option>
                                    <option value="Insumos" <?= $p['Tipo'] == 'Insumos' ? 'selected' : '' ?>>Insumos</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Categoría</label>
                                <select class="form-select" name="categoria">
                                    <option value="Helados" <?= $p['Categoria'] == 'Helados' ? 'selected' : '' ?>>Helados</option>
                                    <option value="Lacteos" <?= $p['Lacteos'] == 'Lacteos' ? 'selected' : '' ?>>Lácteos</option>
                                    <option value="Yogurt" <?= $p['Categoria'] == 'Yogurt' ? 'selected' : '' ?>>Yogurt</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Proveedor</label>
                                <select class="form-select" name="proveedor" required>
                                    <?php while($prov = $proveedores->fetch_assoc()): ?>
                                        <option value="<?= $prov['ID_Proveedor'] ?>" <?= $p['ID_Proveedor'] == $prov['ID_Proveedor'] ? 'selected' : '' ?>>
                                            <?= $prov['Nombre_Proveedor'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bodega Principal</label>
                                <select class="form-select" name="bodega" required>
                                    <?php while($bod = $bodegas->fetch_assoc()): ?>
                                        <option value="<?= $bod['Id_Bodega'] ?>" <?= $p['ID_Bodega'] == $bod['Id_Bodega'] ? 'selected' : '' ?>>
                                            <?= $bod['Nombre_Bodega'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Costo Unitario</label>
                                <input type="number" step="0.01" class="form-control" name="costo_unitario" value="<?= $p['Costo_Unitario'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">PVP (Venta)</label>
                                <input type="number" step="0.01" class="form-control" name="pvp" value="<?= $p['PVP'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Empaque</label>
                                <select class="form-select" name="und_empaque">
                                    <option value="Unidad" <?= $p['Und_Empaque'] == 'Unidad' ? 'selected' : '' ?>>Unidad</option>
                                    <option value="x10" <?= $p['Und_Empaque'] == 'x10' ? 'selected' : '' ?>>Pack x10</option>
                                    <option value="x12" <?= $p['Und_Empaque'] == 'x12' ? 'selected' : '' ?>>Pack x12</option>
                                    <option value="x24" <?= $p['Und_Empaque'] == 'x24' ? 'selected' : '' ?>>Pack x24</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Imagen del Producto</label>
                                <input type="file" class="form-control" name="imagen_producto" id="edit_img">
                            </div>
                            <div class="col-md-4 text-center">
                                <img id="img_preview" src="../../<?= $p['Ruta_Imagen'] ?: 'public/img/default-product.png' ?>" class="rounded border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="../productos.php" class="btn btn-secondary px-4 me-2">Cancelar</a>
                            <button type="submit" class="btn btn-warning px-5 fw-bold">Actualizar Información</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>