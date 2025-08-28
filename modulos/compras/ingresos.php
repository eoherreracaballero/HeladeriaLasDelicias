<?php
session_start();

// Cargar encabezado
require_once __DIR__ . "/../../public/html/encabezado.php";

// Conexión de base de datos
include("../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// Consultas del SQL Requeridas

$proveedores = $conexion->query("SELECT ID_Proveedor, Nombre_Proveedor FROM proveedor");
$productos = $conexion->query("SELECT ID_Producto, Nombre_Producto FROM producto");
$bodegas = $conexion->query("SELECT ID_Bodega, Nombre_Bodega FROM bodega");

// Generar las opciones como texto para usarlas dentro del JS

$selectProductos = ''; $productos->data_seek(0);
while ($prod = $productos->fetch_assoc()) {
    $selectProductos .= '<option value="' . $prod['ID_Producto'] . '">' . $prod['Nombre_Producto'] . '</option>';
}

$selectBodegas = ''; $bodegas->data_seek(0);
while ($bod = $bodegas->fetch_assoc()) {
    $selectBodegas .= '<option value="' . $bod['ID_Bodega'] . '">' . $bod['Nombre_Bodega'] . '</option>';
}
?>

     <!--  Titulo de Modulo-->

<main class="p-4 flex-grow-1 fade-in" id="contenido">
        <div class="mb-3 text-end">
            <a href="option_ingresos/consulta_ingresos.php" class="btn btn-primary">
            <i class="fas fa-list"></i> Ver Ingresos
            </a>
         </div>
    <h2 class="text-primary mb-4"><i class="fas fa-boxes me-2"></i>Ingreso de Compras y Ajustes</h2>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

    <form method="POST" action="option_ingresos/guardar_ingreso.php" id="formIngreso">
        <!-- Datos Generales -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="tipoIngreso" class="form-label">Tipo de Ingreso:</label>
                <select name="tipoIngreso" id="tipoIngreso" class="form-select" required>
                    <option value="Orden de Compra">Orden de Compra</option>
                    <option value="Ajuste x Inventario">Ajuste x Inventario</option>
                    <option value="Ajuste x Produccion">Ajuste x Producción</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="idProveedor" class="form-label">Proveedor:</label>
                <select name="idProveedor" id="idProveedor" class="form-select" required>
                    <?php while ($fila = $proveedores->fetch_assoc()) { ?>
                        <option value="<?= $fila['ID_Proveedor']; ?>"><?= $fila['Nombre_Proveedor']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="noDocProveedor" class="form-label">No. Documento Proveedor:</label>
                <input type="text" name="noDocProveedor" id="noDocProveedor" class="form-control">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label for="fechaDocumento" class="form-label">Fecha Documento:</label>
                <input type="date" name="fechaDocumento" id="fechaDocumento" class="form-control" required>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label for="fechaIngreso" class="form-label">Fecha Ingreso:</label>
                <input type="date" name="fechaIngreso" id="fechaIngreso" class="form-control" required>
            </div>
        </div>

        <!-- Tabla Detalle -->
        <h5 class="mb-3">Detalle de Productos</h5>
        <button type="button" class="btn btn-success mb-3" onclick="agregarDetalle()">Agregar Producto</button>

        <table id="detalleIngreso" class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Bodega</th>
                    <th>Cantidad</th>
                    <th>Costo Unitario</th>
                    <th>Costo Total</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Totales -->
        <div class="row mb-4" id="totalesContainer" style="display:none;">
        <div class="col-md-4 offset-md-7 text-end">
            <div><label class="form-label">Subtotal: $<span id="subtotal">0.00</span></label></div>
            <div><label class="form-label">IVA (19%): $<span id="iva">0.00</span></label></div>
            <div><label class="form-label"><strong>Total: $<span id="total">0.00</span></strong></label></div>
        </div>
        </div>

        <!-- Botón Guardar -->
        <button type="submit" class="btn btn-primary">Guardar Ingreso</button>
    </form>
</div>

<script>
const productoSelect = `<?= $selectProductos ?>`;
const bodegaSelect = `<?= $selectBodegas ?>`;

function agregarDetalle() {
    const tbody = document.querySelector("#detalleIngreso tbody");
    const tr = document.createElement("tr");

    tr.innerHTML = `
        <td><select name="producto[]" class="form-select" required>${productoSelect}</select></td>
        <td><select name="bodega[]" class="form-select" required>${bodegaSelect}</select></td>
        <td><input type="number" name="cantidad[]" min="1" step="any" class="form-control" required oninput="calcularTotales()"></td>
        <td><input type="number" name="costo[]" min="0" step="any" class="form-control" required oninput="calcularTotales()"></td>
        <td class="costoTotal">0.00</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); calcularTotales();">X</button></td>
    `;

    tbody.appendChild(tr);
    document.getElementById('totalesContainer').style.display = 'block';
    calcularTotales();
}

function calcularTotales() {
    const cantidades = document.querySelectorAll("input[name='cantidad[]']");
    const costos = document.querySelectorAll("input[name='costo[]']");
    const celdasTotal = document.querySelectorAll(".costoTotal");
    let subtotal = 0;

    for (let i = 0; i < cantidades.length; i++) {
        const cantidad = parseFloat(cantidades[i].value) || 0;
        const costo = parseFloat(costos[i].value) || 0;
        const totalFila = cantidad * costo;
        subtotal += totalFila;
    celdasTotal[i].textContent = totalFila.toFixed(2);
    }

    const iva = subtotal * 0.19;
    const total = subtotal + iva;

    document.getElementById("subtotal").textContent = subtotal.toFixed(2);
    document.getElementById("iva").textContent = iva.toFixed(2);
    document.getElementById("total").textContent = total.toFixed(2);
   }
</script>
