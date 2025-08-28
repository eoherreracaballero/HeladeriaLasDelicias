<?php

// Cargar encabezado
require_once __DIR__ . "/../../public/html/encabezado.php";

// Conexión de base de datos
include("../../app/db/conexion.php");

// Consulta la tabla productos
$res_prod = $conexion->query("SELECT * FROM producto");
$num_reg = $res_prod->num_rows;

if ($num_reg == 0) {
    echo "<div class='alert alert-warning'>⚠️ No hay productos registrados.</div>";
    mysqli_close($conexion);
    exit();
}
// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-boxes me-2"></i> Módulo de Gestión de Productos</h2>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

    <!-- Formulario de Registro -->
    <form id="form-guardar-producto" method="POST" action="crud_producto/guardar_producto.php">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del producto" required>
        </div>

        <div class="col-md-4">
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

        <div class="col-md-4">
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

        <div class="col-md-4">
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

        <div class="col-md-4">
            <label for="pvp" class="form-label">PVP</label>
            <input type="number" step="0.01" class="form-control" id="pvp" name="pvp" required>
        </div>

        <div class="col-md-4">
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

        <div class="col-md-4">
            <label for="bodega" class="form-label">Bodega</label>
            <select class="form-select" id="bodega" name="bodega" required>
                <option value="">Seleccione una bodega</option>
                <?php
                $res_bod = $conexion->query("SELECT Id_Bodega, Nombre_Bodega FROM bodega");
                while ($bod = $res_bod->fetch_assoc()) {
                    echo "<option value='{$bod['Id_Bodega']}'>{$bod['Nombre_Bodega']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" class="form-control" id="marca" name="marca" required>
        </div>

        <div class="col-md-4">
            <label for="proveedor" class="form-label">Proveedor</label>
            <select class="form-select" id="proveedor" name="proveedor" required>
                <option value="">Seleccione un proveedor</option>
                <?php
                $res_prov = $conexion->query("SELECT ID_Proveedor, Nombre_Proveedor FROM proveedor");
                while ($prov = $res_prov->fetch_assoc()) {
                    echo "<option value='{$prov['ID_Proveedor']}'>{$prov['Nombre_Proveedor']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-2"></i> Guardar Producto
            </button>
        </div>
    </div>
</form>


    <!-- Tabla de Productos Registrados-->

        <h4 class="text-secondary mt-5">Lista de Productos Registrados</h4>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID Producto</th>
                        <th>Proveedor</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Und. Empaque</th>
                        <th>Stock</th>
                        <th>Stock Min</th>
                        <th>Stock Óptimo</th>
                        <th>Costo Unitario</th>
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
                            <td><?= $fila['ID_Proveedor']; ?></td>
                            <td><?= $fila['Nombre_Producto']; ?></td>
                            <td><?= $fila['Tipo']; ?></td>
                            <td><?= $fila['Categoria']; ?></td>
                            <td><?= $fila['Und_Empaque']; ?></td>
                            <td><?= $fila['Stock']; ?></td>
                            <td><?= $fila['Stock_Minimo']; ?></td>
                            <td><?= $fila['Stock_Optimo']; ?></td>
                            <td><?= $fila['Costo_Unitario']; ?></td>
                            <td><?= $fila['PVP']; ?></td>
                            <td><?= $fila['ID_Bodega']; ?></td>
                            <td><?= $fila['Estado']; ?></td>
                            <td><?= $fila['Marca']; ?></td>
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
    </div>
</main>

<?php mysqli_close($conexion);
ob_end_flush();
 ?>
 