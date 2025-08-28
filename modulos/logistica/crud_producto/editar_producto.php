<?php
ob_start();

// incluir encabezado.php para cargar estilos y scripts
require_once __DIR__ . "/../../../public/html/encabezado.php";

// Conexión a la base de datos
include(__DIR__ . "/../../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../../public/html/tablas.php";

// Validar ID del producto
$id = isset($_GET['ID_Producto']) ? intval($_GET['ID_Producto']) : 0;
$res = mysqli_query($conexion, "SELECT * FROM producto WHERE ID_Producto = $id");

if (!$res || mysqli_num_rows($res) == 0) {
    echo "<div class='alert alert-danger m-4'>❌ Producto no encontrado.</div>";
    exit();
}

$producto = mysqli_fetch_assoc($res);

// Guardar cambios
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria'];
    $und_empaque = $_POST['und_empaque'];
    $pvp = $_POST['pvp'];
    $estado = $_POST['estado'];
    $bodega = $_POST['bodega'];
    $marca = $_POST['marca'];

    $sql = "UPDATE producto SET 
        Nombre_Producto = '$nombre',
        Tipo = '$tipo',
        Categoria = '$categoria',
        Und_Empaque = '$und_empaque',
        PVP = '$pvp',
        Estado = '$estado',
        ID_Bodega = '$bodega',
        Marca = '$marca'
        WHERE ID_Producto = $id";

    if (mysqli_query($conexion, $sql)) {
        header("Location: ../productos.php?msg=updated");
        exit();
    } else {
        echo "<div class='alert alert-danger m-4'>❌ Error al actualizar: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<main class="container p-4">
    <h2 class="text-primary mb-4">✏️ Editar Producto</h2>
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['Nombre_Producto']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" name="tipo" class="form-control" value="<?= htmlspecialchars($producto['Tipo']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="categoria" class="form-label">Categoría</label>
                <input type="text" name="categoria" class="form-control" value="<?= htmlspecialchars($producto['Categoria']) ?>" required>
            </div>
           <div class="col-md-4">
                <label for="und_empaque" class="form-label">Und. Empaque</label>
                <select name="und_empaque" class="form-select" required>
                 <?php

        // Obtener las opciones del ENUM desde la base de datos
        $enumQuery = mysqli_query($conexion, "SHOW COLUMNS FROM producto LIKE 'Und_Empaque'");
        $enumRow = mysqli_fetch_assoc($enumQuery);

        // Extraer los valores del ENUM
        preg_match("/^enum\('(.*)'\)$/", $enumRow['Type'], $matches);
        $enumValues = explode("','", $matches[1]);

        // Generar opciones
            foreach ($enumValues as $valor) {
            $selected = ($producto['Und_Empaque'] === $valor) ? "selected" : "";
            echo "<option value='$valor' $selected>$valor</option>";
        }
        ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="pvp" class="form-label">PVP</label>
                <input type="number" step="0.01" name="pvp" class="form-control" value="<?= htmlspecialchars($producto['PVP']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($producto['Estado']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="bodega" class="form-label">ID Bodega</label>
                <input type="text" name="bodega" class="form-control" value="<?= htmlspecialchars($producto['ID_Bodega']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="marca" class="form-label">Marca</label>
                <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($producto['Marca']) ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</main>

<?php mysqli_close($conexion); ?>
