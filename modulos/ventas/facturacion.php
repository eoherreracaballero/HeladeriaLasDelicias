<?php
session_start();
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// ----------------------------------------------------------------------
// 1. CARGA DE DATOS PRINCIPALES (CLIENTES, PRODUCTOS, BODEGAS)
// ----------------------------------------------------------------------

// Obtener clientes
$sqlClientes = "SELECT Id_cliente, Nombre_Cliente, No_NIT, No_Telefono, Email FROM cliente";
$resultClientes = $conexion->query($sqlClientes);
$clientes = $resultClientes ? $resultClientes->fetch_all(MYSQLI_ASSOC) : [];

// OBTENER PRODUCTOS: CRÍTICO - Traer Ruta_Imagen
$sqlProductos = "SELECT ID_Producto, Nombre_Producto, PVP, Ruta_Imagen FROM producto WHERE Estado = 'Disponible'";
$resultProductos = $conexion->query($sqlProductos);
$productos = $resultProductos ? $resultProductos->fetch_all(MYSQLI_ASSOC) : [];

// Obtener Bodegas
$sqlBodegas = "SELECT Id_Bodega, Nombre_Bodega FROM bodega";
$resultBodegas = $conexion->query($sqlBodegas);
$bodegas = $resultBodegas ? $resultBodegas->fetch_all(MYSQLI_ASSOC) : [];

// ----------------------------------------------------------------------
// 2. CARGA DE INVENTARIO DETALLADO POR BODEGA (PARA JS)
// ----------------------------------------------------------------------
$sqlInventario = "
    SELECT 
        i.ID_Producto, 
        i.Id_Bodega, 
        i.Stock,
        b.Nombre_Bodega
    FROM inventario i
    INNER JOIN bodega b ON i.Id_Bodega = b.Id_Bodega
    WHERE i.Stock > 0
";
$resultInventario = $conexion->query($sqlInventario);

$inventarioData = [];
if ($resultInventario) {
    while ($row = $resultInventario->fetch_assoc()) {
        $productoId = $row['ID_Producto'];
        $bodegaId = $row['Id_Bodega'];

        if (!isset($inventarioData[$productoId])) {
            $inventarioData[$productoId] = [];
        }
        
        $inventarioData[$productoId][$bodegaId] = [
            'stock' => (float)$row['Stock'],
            'nombre' => $row['Nombre_Bodega']
        ];
    }
}
$inventarioJson = json_encode($inventarioData); 
$tiposEgreso = ['Factura', 'Ajuste'];
?>

<?php if (isset($_GET['mensaje'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_GET['mensaje']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    Error: <?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    
    <div class="mb-3 text-end">
        <a href="option_egreso/consulta_egresos.php" class="btn btn-primary me-2"> 
            <i class="fas fa-list"></i> Ver Egresos
        </a>

        <a href="crud_cliente/crear_cliente.php" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Crear Cliente
        </a>
    </div>

    <h2 class="text-primary mb-4"><i class="fas fa-boxes me-2"></i> Facturación y Egresos</h2>

    <form id="formFactura" method="POST" action="option_egreso/procesar_egreso.php" novalidate>

        <div class="mb-3">
            <label for="tipoEgreso" class="form-label">Tipo de Egreso</label>
            <select class="form-select" id="tipoEgreso" name="tipo_egreso" required>
                <option value="">Seleccione tipo de egreso</option>
                <?php foreach ($tiposEgreso as $tipo): ?>
                    <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
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
        <table class="table table-bordered" id="tablaFactura">
            <thead class="table-light">
                <tr>
                    <th style="width: 250px;">Producto</th> 
                    <th style="width: 50px;">Img</th> 
                    <th style="width: 150px;">Stock Global</th> 
                    <th style="width: 130px;">PVP</th>
                    <th style="width: 80px;">Cantidad</th>
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
                            <?php foreach ($productos as $prod): 
                                $rutaImagen = $prod['Ruta_Imagen'] ?? 'public/img/default-product.png';
                                $rutaDisplay = "../../" . htmlspecialchars($rutaImagen); 
                                ?>
                                <option 
                                    value="<?= $prod['ID_Producto'] ?>" 
                                    data-precio="<?= $prod['PVP'] ?>"
                                    data-imagen="<?= $rutaDisplay ?>" 
                                >
                                    <?= htmlspecialchars($prod['Nombre_Producto']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    
                    <td class="image-cell text-center">
                        <img class="producto-miniatura" src="../../public/img/default-product.png" 
                             alt="Producto" style="width: 40px; height: 40px; object-fit: cover;">
                    </td>
                    
                    <td class="stock-display-cell">
                        <small class="text-muted stock-info">Seleccione un producto</small>
                    </td>

                    <td>
                        <input type="text" class="form-control pvp_formato" readonly>
                        <input type="hidden" class="pvp" name="pvp[]">
                    </td>

                    <td class="position-relative">
                        <input type="number" class="form-control cantidad" name="cantidad[]" min="1" value="1" required>
                        <span class="stock-icon-warning position-absolute top-50 end-0 translate-middle-y me-2 text-danger d-none" 
                                data-bs-toggle="tooltip" data-bs-placement="top" title="">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
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

        <div class="mb-3 text-end">
            
            <label class="form-label fw-bold">Subtotal Bruto:</label>
            <input type="text" id="subtotalFacturaBruto" class="form-control d-inline-block w-auto text-end mb-2" readonly>
            <input type="hidden" id="subtotal_hidden_bruto" name="subtotal_bruto">
            
            <label for="descuentoInput" class="form-label fw-bold text-danger">Descuento (% o Valor):</label>
            <input type="number" id="descuentoInput" name="descuento_valor" class="form-control d-inline-block w-auto text-end mb-2 text-danger" value="0" min="0" step="0.01">
            <input type="hidden" id="descuento_hidden" name="descuento_aplicado">

            <label class="form-label fw-bold">Subtotal Neto:</label>
            <input type="text" id="subtotalFacturaNeto" class="form-control d-inline-block w-auto text-end mb-2" readonly>
            
            <label class="form-label fw-bold">IVA (19%):</label>
            <input type="text" id="ivaFactura" class="form-control d-inline-block w-auto text-end mb-2" readonly>
            <input type="hidden" id="iva_hidden" name="IVA">

            <label class="form-label fw-bold">Total a Pagar:</label>
            <input type="text" id="totalFactura" class="form-control d-inline-block w-auto text-end" readonly>
            <input type="hidden" id="total_hidden" name="total">
        </div>
        <div id="alertasFactura" class="alert alert-warning d-none" role="alert">
            Hay problemas de stock en los ítems marcados. No puede guardar la factura hasta corregirlos.
        </div>

        <button type="submit" class="btn btn-success" id="btnGuardarFactura">Guardar Factura</button>
    </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    
    // ----------------------------------------------------------------------
    // MÓDULO 1: Inicialización y Funciones de Utilidad (JavaScript)
    // ----------------------------------------------------------------------
    const INVENTARIO = <?= $inventarioJson ?>;
    const IVA_RATE = 0.19;
    const inputDescuento = document.getElementById('descuentoInput');
    
    // Inicializar Tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        }
    });
    
    // --- FUNCIÓN DE IMAGEN: Muestra la miniatura ---
    const updateProductImage = (fila) => {
        const selectProd = fila.querySelector('.producto');
        const imgElement = fila.querySelector('.producto-miniatura');
        
        if (!selectProd || !imgElement) return;

        const selectedOption = selectProd.options[selectProd.selectedIndex];
        const defaultImgPath = '../../public/img/default-product.png';

        if (selectedOption && selectedOption.value) {
            const imgPath = selectedOption.dataset.imagen; 
            imgElement.src = imgPath || defaultImgPath; 
        } else {
            imgElement.src = defaultImgPath;
        }
    };


    // --- FUNCIÓN DE UTILIDAD: MUESTRA EL STOCK DETALLADO ---
    const displayStockInfo = (fila, productoId) => {
        const displayCell = fila.querySelector('.stock-display-cell');
        displayCell.innerHTML = ''; 

        if (!productoId || !INVENTARIO[productoId]) {
            displayCell.innerHTML = '<small class="text-muted">Sin stock o no disponible.</small>';
            return;
        }

        const stockData = INVENTARIO[productoId];
        let htmlContent = '<ul class="list-unstyled small mb-0">';
        let totalGlobal = 0;
        let hayStock = false;
        
        for (const bodegaId in stockData) {
            const data = stockData[bodegaId];
            if (data.stock > 0) {
                htmlContent += `
                    <li class="d-flex justify-content-between">
                        <span class="fw-bold">${data.nombre}:</span>
                        <span class="text-success">${data.stock} uds</span>
                    </li>
                `;
                totalGlobal += data.stock;
                hayStock = true;
            }
        }
        
        if (!hayStock) {
            displayCell.innerHTML = '<small class="text-danger">Agotado en todas las bodegas.</small>';
        } else {
            let totalHtml = `<div class="fw-bold mb-1 border-bottom">Total Global: ${totalGlobal} uds</div>`;
            displayCell.innerHTML = totalHtml + htmlContent + '</ul>';
        }
    };
    

    // ----------------------------------------------------------------------
    // MÓDULO 2: Validación de Stock y Cálculo de Totales
    // ----------------------------------------------------------------------

    // --- FUNCIÓN DE VALIDACIÓN DE STOCK EN TIEMPO REAL (checkStock) ---
    const checkStock = (fila) => {
        const selectProd = fila.querySelector('.producto');
        const inputCantidad = fila.querySelector('.cantidad');
        const selectBodega = fila.querySelector('.bodega');
        const iconElement = fila.querySelector('.stock-icon-warning');

        const productoId = selectProd.value;
        const bodegaId = selectBodega.value;
        const cantidadRequerida = parseFloat(inputCantidad.value) || 0;
        
        iconElement.classList.add('d-none');
        fila.classList.remove('table-danger'); 
        
        if (!productoId || !bodegaId || cantidadRequerida <= 0 || !INVENTARIO[productoId]) {
            return { isValid: true, stock: 0 };
        }

        const stockDisponible = INVENTARIO[productoId][bodegaId]?.stock || 0;
        
        if (cantidadRequerida > stockDisponible) {
            iconElement.classList.remove('d-none');
            const nombreBodega = INVENTARIO[productoId][bodegaId]?.nombre || 'esta bodega';
            iconElement.setAttribute('title', `¡Stock insuficiente en ${nombreBodega}! Disponible: ${stockDisponible}`); 
            fila.classList.add('table-danger'); 
            
            const tooltipInstance = bootstrap.Tooltip.getInstance(iconElement);
            if (tooltipInstance) {
                tooltipInstance.setContent({ '.tooltip-inner': `¡Stock insuficiente! Disponible: ${stockDisponible}` });
            }

            return { isValid: false, stock: stockDisponible };
        }

        return { isValid: true, stock: stockDisponible };
    };

    // --- FUNCIÓN DE CÁLCULO DE TOTALES Y BLOQUEO ---
    const actualizarSubtotales = () => {
        let subtotalTotalBruto = 0;
        let hayErrorStock = false;

        document.querySelectorAll('.item-factura').forEach(fila => {
            const select = fila.querySelector('.producto');
            const inputCantidad = fila.querySelector('.cantidad');
            
            // 1. Ejecutar validación de stock y visualización
            const stockStatus = checkStock(fila);
            if (!stockStatus.isValid) {
                hayErrorStock = true;
            }
            displayStockInfo(fila, select.value); 
            
            // 2. Cálculo de Subtotal Bruto por ítem
            const cantidad = parseFloat(inputCantidad.value) || 0;
            const precio = parseFloat(select.options[select.selectedIndex]?.dataset?.precio || 0);

            const itemSubtotal = cantidad * precio;
            subtotalTotalBruto += itemSubtotal;

            // Mostrar formatos y ocultos para PHP
            fila.querySelector('.pvp_formato').value = precio.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
            fila.querySelector('.subtotal_formato').value = itemSubtotal.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
            fila.querySelector('.pvp').value = precio.toFixed(2);
            fila.querySelector('.subtotal').value = itemSubtotal.toFixed(2);
        });

        // -----------------------------------------------------------
        // 🟢 CÁLCULO DEL DESCUENTO
        // -----------------------------------------------------------
        let valorDescuentoAplicado = 0;
        let descuento = parseFloat(inputDescuento.value) || 0;
        
        if (descuento > 0) {
            // Asumir porcentaje si es <= 100, valor fijo si es > 100 (o si se fuerza)
            if (descuento <= 100) { 
                 valorDescuentoAplicado = subtotalTotalBruto * (descuento / 100);
            } else {
                 valorDescuentoAplicado = descuento;
            }
        }
        
        // Limitar descuento para que no sea mayor al subtotal bruto
        valorDescuentoAplicado = Math.min(valorDescuentoAplicado, subtotalTotalBruto);
        
        const subtotalNeto = subtotalTotalBruto - valorDescuentoAplicado;
        // -----------------------------------------------------------

        // 3. Control de Totales y Bloqueo
        const iva = subtotalNeto * IVA_RATE;
        const total = subtotalNeto + iva;
        
        // Actualizar Inputs de Totales
        document.getElementById('subtotalFacturaBruto').value = subtotalTotalBruto.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
        document.getElementById('subtotalFacturaNeto').value = subtotalNeto.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
        document.getElementById('ivaFactura').value = iva.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
        document.getElementById('totalFactura').value = total.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });

        // Actualizar Inputs Ocultos para PHP
        document.getElementById('subtotal_hidden_bruto').value = subtotalTotalBruto.toFixed(2);
        document.getElementById('descuento_hidden').value = valorDescuentoAplicado.toFixed(2);
        document.getElementById('iva_hidden').value = iva.toFixed(2);
        document.getElementById('total_hidden').value = total.toFixed(2);

        // 4. Bloqueo del formulario
        const btnGuardar = document.getElementById('btnGuardarFactura');
        const alerta = document.getElementById('alertasFactura');
        const hayItems = document.querySelectorAll('.item-factura').length > 0 && subtotalTotalBruto > 0;
        
        if (hayErrorStock || !hayItems) {
             btnGuardar.disabled = true;
        } else {
             btnGuardar.disabled = false;
        }
        
        if (hayErrorStock) {
            alerta.classList.remove('d-none');
        } else {
            alerta.classList.add('d-none');
        }
    };

    // ----------------------------------------------------------------------
    // MÓDULO 3: Eventos y Listeners
    // ----------------------------------------------------------------------
    
    // 🟢 1. Listener para el Descuento
    document.getElementById('descuentoInput').addEventListener('input', actualizarSubtotales);

    // 2. Listener para cargar datos del cliente (Sin cambios)
    document.getElementById('cliente').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('clienteNit').value = selected.dataset.nit || '';
        document.getElementById('clienteTelefono').value = selected.dataset.telefono || '';
        document.getElementById('clienteEmail').value = selected.dataset.email || '';
    });
    
    // 3. Listeners para actualizar subtotales, stock Y LA IMAGEN
    document.getElementById('itemsFactura').addEventListener('input', actualizarSubtotales);
    
    document.getElementById('itemsFactura').addEventListener('change', (e) => {
        if (e.target.classList.contains('producto')) {
            const fila = e.target.closest('.item-factura');
            updateProductImage(fila);
        }
        actualizarSubtotales(); 
    });

    // 4. Agregar ítem
    document.getElementById('agregarItem').addEventListener('click', () => {
        const original = document.querySelector('.item-factura');
        const clon = original.cloneNode(true);
        
        clon.querySelectorAll('input, select').forEach(el => { 
            if(el.type !== 'button') el.value = ''; 
            if(el.classList.contains('cantidad')) el.value = 1;
            if(el.classList.contains('producto') || el.classList.contains('bodega')) el.value = '';
            if(el.classList.contains('pvp_formato') || el.classList.contains('subtotal_formato')) el.value = '';
        });
        
        // Limpiar estado de error y visualización
        clon.querySelector('.stock-icon-warning').classList.add('d-none');
        clon.classList.remove('table-danger');
        clon.querySelector('.stock-display-cell').innerHTML = '<small class="text-muted">Seleccione un producto</small>';
        clon.querySelector('.producto-miniatura').src = '../../public/img/default-product.png';
        
        document.getElementById('itemsFactura').appendChild(clon);
        actualizarSubtotales();
        
        // Reinicializar tooltips
        const iconElement = clon.querySelector('.stock-icon-warning');
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(iconElement);
        }
    });

    // 5. Eliminar ítem
    document.getElementById('itemsFactura').addEventListener('click', (e) => {
        if (e.target.classList.contains('eliminar-item')) {
            const filas = document.querySelectorAll('.item-factura');
            if (filas.length > 1) {
                e.target.closest('.item-factura').remove();
                actualizarSubtotales();
            }
        }
    });

    // 6. Validación final al enviar (Sin cambios)
    document.getElementById('formFactura').addEventListener('submit', function(e) {
        if (document.getElementById('btnGuardarFactura').disabled) {
            e.preventDefault();
            document.getElementById('alertasFactura').classList.remove('d-none');
            return;
        }

        const tipoEgreso = this.tipo_egreso.value.trim();
        const cliente = this.cliente.value.trim();    

        if (!tipoEgreso || !cliente) {
            alert('Seleccione tipo de egreso y cliente.');
            e.preventDefault();
            return;
        }

        const productos = this.querySelectorAll('select.producto');
        const cantidades = this.querySelectorAll('input.cantidad');
        const bodegasItem = this.querySelectorAll('select.bodega');

        for(let i=0; i<productos.length; i++){
            if(!productos[i].value){ alert('Seleccione un producto válido en todos los ítems.'); e.preventDefault(); return; }
            if(!cantidades[i].value || cantidades[i].value <= 0){ alert('Ingrese una cantidad válida en todos los ítems.'); e.preventDefault(); return; }
            if(!bodegasItem[i].value){ alert('Seleccione la bodega de despacho para todos los ítems.'); e.preventDefault(); return; }
        }
    });

    // Ejecutar al cargar para inicializar subtotales, stock y las imágenes
    document.querySelectorAll('.item-factura').forEach(fila => {
        updateProductImage(fila); 
    });
    actualizarSubtotales();
});
</script>