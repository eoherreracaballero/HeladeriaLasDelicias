<?php
include(__DIR__ . "/../../../app/db/conexion.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 1. Obtener datos básicos e imagen del producto
    $sql_p = "SELECT Nombre_Producto, Ruta_Imagen FROM producto WHERE ID_Producto = $id";
    $res_p = $conexion->query($sql_p);
    $prod = $res_p->fetch_assoc();
    $img = !empty($prod['Ruta_Imagen']) ? "../../".$prod['Ruta_Imagen'] : "../../public/img/default-product.png";

    // 2. Obtener stocks por bodega
    $sql = "SELECT i.*, b.Nombre_Bodega 
            FROM inventario i 
            INNER JOIN bodega b ON i.ID_Bodega = b.Id_Bodega 
            WHERE i.ID_Producto = $id";
    $res = $conexion->query($sql);

    // 3. Diseño del contenido del Modal
    echo '<div class="row mb-3 align-items-center">';
        echo '<div class="col-md-4">';
            echo "<img src='$img' class='img-fluid rounded shadow border' style='max-height: 200px; width: 100%; object-fit: contain;' alt='Imagen Producto'>";
        echo '</div>';
        echo '<div class="col-md-8 text-start">';
            echo "<h5 class='text-secondary'>Análisis de Inventario</h5>";
            
            if ($res->num_rows > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-sm table-hover align-middle">';
                echo '<thead class="table-light"><tr><th>Bodega</th><th>Stock</th><th>Alerta</th></tr></thead>';
                echo '<tbody>';
                
                while ($row = $res->fetch_assoc()) {
                    $stock = $row['Stock'];
                    $min = $row['Stock_Minimo'];
                    $optimo = $row['Stock_Optimo'];

                    // Lógica de Alerta por Bodega
                    if ($stock <= 0) {
                        $alerta = '<span class="badge bg-danger w-100">AGOTADO</span>';
                    } elseif ($stock <= $min) {
                        $alerta = '<span class="badge bg-warning text-dark w-100">CRÍTICO (Bajo el mín)</span>';
                    } elseif ($stock >= $optimo) {
                        $alerta = '<span class="badge bg-success w-100">ÓPTIMO</span>';
                    } else {
                        $alerta = '<span class="badge bg-info text-white w-100">NORMAL</span>';
                    }

                    echo "<tr>
                            <td>{$row['Nombre_Bodega']}</td>
                            <td class='fw-bold fs-5'>$stock</td>
                            <td>$alerta</td>
                          </tr>";
                }
                echo '</tbody></table></div>';
            } else {
                echo '<div class="alert alert-warning">No hay registros en bodega para este producto.</div>';
            }
        echo '</div>';
    echo '</div>';
}
?>