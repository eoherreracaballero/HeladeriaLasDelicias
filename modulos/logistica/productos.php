<?php
ob_start(); // Iniciar buffer de salida
session_start(); // Asegurar la sesión si es necesario para el encabezado

// Cargar encabezado: RUTA CORREGIDA a DOS NIVELES (../..)
require_once __DIR__ . "/../../public/html/encabezado.php";

// Conexión de base de datos
include(__DIR__ . "/../../app/db/conexion.php");
global $conexion;

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// 1. CONSULTA DE PRODUCTOS CON JOINS para mostrar Nombres en lugar de IDs
$sql_prod = "SELECT 
    p.*,
    pr.Nombre_Proveedor,
    b.Nombre_Bodega
FROM producto p
LEFT JOIN proveedor pr ON p.ID_Proveedor = pr.ID_Proveedor
LEFT JOIN bodega b ON p.ID_Bodega = b.Id_Bodega";

$res_prod = $conexion->query($sql_prod);

if ($res_prod === FALSE) {
    echo "<div class='alert alert-danger'>❌ Error en la consulta de productos: " . $conexion->error . "</div>";
    mysqli_close($conexion);
    ob_end_flush();
    exit();
}
$num_reg = $res_prod->num_rows;

// Consultas para obtener datos de SELECT fuera del bucle principal
$res_bod = $conexion->query("SELECT Id_Bodega, Nombre_Bodega FROM bodega");
$bodegas = $res_bod->fetch_all(MYSQLI_ASSOC);

$res_prov = $conexion->query("SELECT ID_Proveedor, Nombre_Proveedor FROM proveedor");
$proveedores = $res_prov->fetch_all(MYSQLI_ASSOC);

?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-boxes me-2"></i> Módulo de Gestión de Productos</h2>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php endif; ?>

    <form id="form-guardar-producto" method="POST" action="crud_producto/guardar_producto.php" enctype="multipart/form-data">
        <div class="row g-3 border p-3 rounded shadow-sm mb-5">
            
            <div class="col-md-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del producto" required>
            </div>

            <div class="col-md-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <?php
                    $enumTipo = $conexion->query("SHOW COLUMNS FROM producto LIKE 'Tipo'");
                    $rowTipo = $enumTipo->fetch_assoc();
                    preg_match("/^enum\('(.*)'\)$/", $rowTipo['Type'], $matchesTipo);
                    $valoresTipo = explode("','", $matchesTipo[1]);
                    foreach ($valoresTipo as $valor) {
                        echo "<option value='$valor'>$valor</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <?php
                    $enumCat = $conexion->query("SHOW COLUMNS FROM producto LIKE 'Categoria'");
                    $rowCat = $enumCat->fetch_assoc();
                    preg_match("/^enum\('(.*)'\)$/", $rowCat['Type'], $matchesCat);
                    $valoresCat = explode("','", $matchesCat[1]);
                    foreach ($valoresCat as $valor) {
                        echo "<option value='$valor'>$valor</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="und_empaque" class="form-label">Und. de Empaque</label>
                <select class="form-select" id="und_empaque" name="und_empaque" required>
                    <?php
                    $enumUnd = $conexion->query("SHOW COLUMNS FROM producto LIKE 'Und_Empaque'");
                    $rowUnd = $enumUnd->fetch_assoc();
                    preg_match("/^enum\('(.*)'\)$/", $rowUnd['Type'], $matchesUnd);
                    $valoresUnd = explode("','", $matchesUnd[1]);
                    foreach ($valoresUnd as $valor) {
                        echo "<option value='$valor'>$valor</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="pvp" class="form-label">PVP</label>
                <input type="number" step="0.01" class="form-control" id="pvp" name="pvp" required>
            </div>

            <div class="col-md-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <?php
                    $enumEst = $conexion->query("SHOW COLUMNS FROM producto LIKE 'Estado'");
                    $rowEst = $enumEst->fetch_assoc();
                    preg_match("/^enum\('(.*)'\)$/", $rowEst['Type'], $matchesEst);
                    $valoresEst = explode("','", $matchesEst[1]);
                    foreach ($valoresEst as $valor) {
                        echo "<option value='$valor'>$valor</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="bodega" class="form-label">Bodega</label>
                <select class="form-select" id="bodega" name="bodega" required>
                    <option value="">Seleccione una bodega</option>
                    <?php foreach ($bodegas as $bod): ?>
                        <option value='<?= $bod['Id_Bodega']; ?>'><?= htmlspecialchars($bod['Nombre_Bodega']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="marca" class="form-label">Marca</label>
                <input type="text" class="form-control" id="marca" name="marca" required>
            </div>

            <div class="col-md-4">
                <label for="proveedor" class="form-label">Proveedor</label>
                <select class="form-select" id="proveedor" name="proveedor" required>
                    <option value="">Seleccione un proveedor</option>
                    <?php foreach ($proveedores as $prov): ?>
                        <option value='<?= $prov['ID_Proveedor']; ?>'><?= htmlspecialchars($prov['Nombre_Proveedor']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="imagen_producto" class="form-label">Imagen (Opcional)</label>
                <input type="file" class="form-control" id="imagen_producto" name="imagen_producto" accept="image/jpeg, image/png">
            </div>

            <div class="col-md-4 text-center">
                <label class="form-label d-block">Previsualización</label>
                <img id="imagen_preview" src="../../public/img/default-product.png" 
                     alt="Previsualización de la imagen" style="max-width: 100px; height: auto; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Guardar Producto
                </button>
            </div>
        </div>
    </form>


    <h4 class="text-secondary mt-5">Lista de Productos Registrados</h4>
    <?php if ($num_reg > 0): ?>
    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered align-middle tabla-datatable">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Proveedor</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Categoría</th>
                    <th>Und. Empaque</th>
                    <th>PVP</th>
                    <th>Bodega</th>
                    <th>Estado</th>
                    <th>Marca</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $res_prod->fetch_assoc()) { ?>
                    <tr class="text-center">
                        <td><?= $fila['ID_Producto']; ?></td>
                        <td>
                            <?php 
                                $rutaDB = $fila['Ruta_Imagen'] ?? '';
                                // RUTA DE VISUALIZACIÓN CORREGIDA
                                // Añadimos "../../" a la ruta guardada en DB para que el navegador la encuentre desde /modulos/logistica/
                                if (!empty($rutaDB)) {
                                    $imagenPath = "../../" . htmlspecialchars($rutaDB); 
                                } else {
                                    $imagenPath = "../../public/img/default-product.png";
                                }
                            ?>
                            <img src="<?= $imagenPath; ?>" alt="Producto" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><?= htmlspecialchars($fila['Nombre_Proveedor']); ?></td>
                        <td><?= htmlspecialchars($fila['Nombre_Producto']); ?></td>
                        <td><?= htmlspecialchars($fila['Tipo']); ?></td>
                        <td><?= htmlspecialchars($fila['Categoria']); ?></td>
                        <td><?= htmlspecialchars($fila['Und_Empaque']); ?></td>
                        <td>$<?= number_format($fila['PVP'], 2); ?></td>
                        <td><?= htmlspecialchars($fila['Nombre_Bodega']); ?></td>
                        <td><?= htmlspecialchars($fila['Estado']); ?></td>
                        <td><?= htmlspecialchars($fila['Marca']); ?></td>
                        <td>
                            <a href="crud_producto/editar_producto.php?ID_Producto=<?= $fila['ID_Producto'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                        <td>
                            <a href="crud_producto/eliminar_producto.php?ID_Producto=<?= $fila['ID_Producto'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este producto?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class='alert alert-warning mt-3'>⚠️ No hay productos registrados.</div>
    <?php endif; ?>
</main>

<script>
document.getElementById('imagen_producto').addEventListener('change', function(event) {
    const preview = document.getElementById('imagen_preview');
    const file = event.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(file);
    } else {
        // RUTA DE IMAGEN POR DEFECTO CORREGIDA en JS
        preview.src = '../../public/img/default-product.png';
    }
});
</script>

<?php 
$conexion->close();
ob_end_flush();
?>