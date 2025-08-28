    <?php
session_start();
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// Obtener clientes, productos y bodegas
$sqlClientes = "SELECT Id_cliente, Nombre_Cliente, No_NIT, No_Telefono, Email FROM cliente";
$resultClientes = $conexion->query($sqlClientes);
$clientes = [];
if ($resultClientes && $resultClientes->num_rows > 0) {
    while ($row = $resultClientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}

$sqlProductos = "SELECT ID_Producto, Nombre_Producto, PVP FROM producto WHERE Estado = 'Disponible'";
$resultProductos = $conexion->query($sqlProductos);
$productos = [];
if ($resultProductos && $resultProductos->num_rows > 0) {
    while ($row = $resultProductos->fetch_assoc()) {
        $productos[] = $row;
    }
}

$sqlBodegas = "SELECT Id_Bodega, Nombre_Bodega FROM bodega";
$resultBodegas = $conexion->query($sqlBodegas);
$bodegas = [];
if ($resultBodegas && $resultBodegas->num_rows > 0) {
    while ($row = $resultBodegas->fetch_assoc()) {
        $bodegas[] = $row;
    }
}

$tiposEgreso = ['Factura', 'Ajuste'];
?>

<?php if (isset($_GET['mensaje'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_GET['mensaje']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <div class="mb-3 text-end">
        <a href="option_egreso/consulta_egresos.php" class="btn btn-primary">
            <i class="fas fa-list"></i> Ver Egresos
        </a>
    </div>

    <h2 class="text-primary mb-4"><i class="fas fa-boxes me-2"></i> Facturación y Egresos</h2>

    <form id="formFactura" method="POST" action="option_egreso/procesar_egreso.php" novalidate>

        <!-- Tipo de Egreso -->
        <div class="mb-3">
            <label for="tipoEgreso" class="form-label">Tipo de Egreso</label>
            <select class="form-select" id="tipoEgreso" name="tipo_egreso" required>
                <option value="">Seleccione tipo de egreso</option>
                <?php foreach ($tiposEgreso as $tipo): ?>
                    <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Bodega -->
        <div class="mb-3">
            <label for="bodega" class="form-label">Seleccione Bodega</label>
            <select class="form-select" id="bodega" name="bodega" required>
                <option value="">Seleccione una bodega</option>
                <?php foreach ($bodegas as $bodega): ?>
                    <option value="<?= $bodega['Id_Bodega'] ?>"><?= htmlspecialchars($bodega['Nombre_Bodega']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Cliente -->
        <div class="mb-3">
            <label for="cliente" class="form-label">Cliente</label>
            <select class="form-select" id="cliente" name="cliente" required>
                <option value="">Seleccione un cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option 
                        value="<?= $cliente['Id_cliente'] ?>"
                        data-nit="<?= htmlspecialchars($cliente['No_NIT']) ?>"
                        data-telefono="<?= htmlspecialchars($cliente['No_Telefono']) ?>"
                        data-email="<?= htmlspecialchars($cliente['Email']) ?>"
                    >
                        <?= htmlspecialchars($cliente['Nombre_Cliente']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="clienteNit" class="form-label">No. Identificación (NIT)</label>
                <input type="text" class="form-control" id="clienteNit" readonly>
            </div>
            <div class="col-md-4">
                <label for="clienteTelefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="clienteTelefono" readonly>
            </div>
            <div class="col-md-4">
                <label for="clienteEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="clienteEmail" readonly>
            </div>
        </div>

        <!-- Tabla de productos -->
        <table class="table table-bordered" id="tablaFactura">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th style="width: 100px;">PVP</th>
                    <th style="width: 100px;">Cantidad</th>
                    <th style="width: 200px;">Bodega</th>
                    <th style="width: 120px;">Subtotal</th>
                    <th style="width: 60px;">Acción</th>
                </tr>
            </thead>
            <tbody id="itemsFactura">
                <tr class="item-factura">
                    <td>
                        <select class="form-select producto" name="producto_id[]" required>
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $prod): ?>
                                <option value="<?= $prod['ID_Producto'] ?>" data-precio="<?= $prod['PVP'] ?>">
                                    <?= htmlspecialchars($prod['Nombre_Producto']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <input type="text" class="form-control pvp_formato" readonly>
                        <input type="hidden" class="pvp" name="pvp[]">
                    </td>

                    <td>
                        <input type="number" class="form-control cantidad" name="cantidad[]" min="1" value="1" required>
                    </td>

                    <td>
                        <select class="form-select bodega" name="bodega_id[]" required>
                            <option value="">Seleccione bodega</option>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?= $bodega['Id_Bodega'] ?>"><?= htmlspecialchars($bodega['Nombre_Bodega']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <input type="text" class="form-control subtotal_formato" readonly>
                        <input type="hidden" class="subtotal" name="subtotal_item[]">
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-danger eliminar-item" title="Eliminar producto">&times;</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary mb-3" id="agregarItem">Agregar Producto</button>

        <!-- Totales -->
        <div class="mb-3 text-end">
            <label class="form-label fw-bold">Subtotal:</label>
            <input type="text" id="subtotalFactura" class="form-control d-inline-block w-auto text-end mb-2" readonly>
            <input type="hidden" id="subtotal_hidden" name="subtotal">

            <label class="form-label fw-bold">IVA (19%):</label>
            <input type="text" id="ivaFactura" class="form-control d-inline-block w-auto text-end mb-2" readonly>
            <input type="hidden" id="iva_hidden" name="IVA">

            <label class="form-label fw-bold">Total a Pagar:</label>
            <input type="text" id="totalFactura" class="form-control d-inline-block w-auto text-end" readonly>
            <input type="hidden" id="total_hidden" name="total">
        </div>

        <button type="submit" class="btn btn-success">Guardar Factura</button>
    </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const actualizarSubtotales = () => {
        let subtotalTotal = 0;

        document.querySelectorAll('.item-factura').forEach(fila => {
            const select = fila.querySelector('.producto');
            const cantidad = parseFloat(fila.querySelector('.cantidad').value) || 0;
            const precio = parseFloat(select.options[select.selectedIndex]?.dataset?.precio || 0);

            const itemSubtotal = cantidad * precio;
            subtotalTotal += itemSubtotal;

            fila.querySelector('.pvp_formato').value = precio.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
            fila.querySelector('.subtotal_formato').value = itemSubtotal.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });

            fila.querySelector('.pvp').value = precio.toFixed(2);
            fila.querySelector('.subtotal').value = itemSubtotal.toFixed(2);
        });

        const iva = subtotalTotal * 0.19;
        const total = subtotalTotal + iva;

        document.getElementById('subtotalFactura').value = subtotalTotal.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
        document.getElementById('ivaFactura').value = iva.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
        document.getElementById('totalFactura').value = total.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });

        document.getElementById('subtotal_hidden').value = subtotalTotal.toFixed(2);
        document.getElementById('iva_hidden').value = iva.toFixed(2);
        document.getElementById('total_hidden').value = total.toFixed(2);
    };

    document.getElementById('itemsFactura').addEventListener('input', actualizarSubtotales);
    document.getElementById('itemsFactura').addEventListener('change', actualizarSubtotales);

    document.getElementById('agregarItem').addEventListener('click', () => {
        const original = document.querySelector('.item-factura');
        const clon = original.cloneNode(true);
        clon.querySelectorAll('input, select').forEach(el => { if(el.type !== 'button') el.value = ''; });
        document.getElementById('itemsFactura').appendChild(clon);
        actualizarSubtotales();
    });

    document.getElementById('itemsFactura').addEventListener('click', (e) => {
        if (e.target.classList.contains('eliminar-item')) {
            const filas = document.querySelectorAll('.item-factura');
            if (filas.length > 1) {
                e.target.closest('.item-factura').remove();
                actualizarSubtotales();
            }
        }
    });

    document.getElementById('cliente').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('clienteNit').value = selected.dataset.nit || '';
        document.getElementById('clienteTelefono').value = selected.dataset.telefono || '';
        document.getElementById('clienteEmail').value = selected.dataset.email || '';
    });

    // Validación antes de enviar
    document.getElementById('formFactura').addEventListener('submit', function(e) {
        const tipoEgreso = this.tipo_egreso.value.trim();
        const bodega = this.bodega.value.trim();
        const cliente = this.cliente.value.trim();

        if (!tipoEgreso || !bodega || !cliente) {
            alert('Seleccione tipo de egreso, bodega y cliente.');
            e.preventDefault();
            return;
        }

        const productos = this.querySelectorAll('select.producto');
        const cantidades = this.querySelectorAll('input.cantidad');

        for(let i=0; i<productos.length; i++){
            if(!productos[i].value){ alert('Seleccione un producto válido en todos los ítems.'); e.preventDefault(); return; }
            if(!cantidades[i].value || cantidades[i].value <= 0){ alert('Ingrese una cantidad válida en todos los ítems.'); e.preventDefault(); return; }
        }
    });

    actualizarSubtotales();
});
</script>
