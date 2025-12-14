<?php
session_start();

// RUTA DE CONEXIÓN CORREGIDA: Asumiendo que estamos en modulos/ventas/
require_once __DIR__ . "/../../public/html/encabezado.php";
include(__DIR__ . "/../../app/db/conexion.php"); 

// Estilos para tablas
require_once __DIR__ . "/../../public/html/tablas.php";

// 1. OBTENER CLIENTES
$sqlClientes = "SELECT Id_cliente, Nombre_Cliente FROM cliente ORDER BY Nombre_Cliente ASC";
$resultClientes = $conexion->query($sqlClientes);
$clientes = [];
if ($resultClientes && $resultClientes->num_rows > 0) {
    while ($row = $resultClientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}

// 2. OBTENER BODEGAS
$sqlBodegas = "SELECT Id_Bodega, Nombre_Bodega FROM bodega";
$resultBodegas = $conexion->query($sqlBodegas);
$bodegas = [];
if ($resultBodegas && $resultBodegas->num_rows > 0) {
    while ($row = $resultBodegas->fetch_assoc()) {
        $bodegas[] = $row;
    }
}

// 3. OBTENER TODOS LOS PRODUCTOS PARA USAR COMO PLANTILLA JS
$sqlAllProducts = "SELECT ID_Producto, Nombre_Producto FROM producto WHERE Estado = 'Disponible'";
$resAllProducts = $conexion->query($sqlAllProducts);
$allProducts = [];
if ($resAllProducts) {
    while ($row = $resAllProducts->fetch_assoc()) {
        $allProducts[] = $row;
    }
}

// Tipos de Documentos de Ajuste
$tiposDocumento = ['Nota de Crédito', 'Nota de Débito'];

// Obtener mensajes de redirección (Error o Éxito)
$mensaje = $_GET['mensaje'] ?? null;
$error = $_GET['error'] ?? null;

?>

<?php if (isset($mensaje)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($mensaje) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    Error: <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <div class="mb-3 text-end">
        <a href="option_notas/consulta_notas.php" class="btn btn-primary">
            <i class="fas fa-list"></i> Ver Notas Existentes
        </a>
    </div>

    <h2 class="text-danger mb-4"><i class="fas fa-file-invoice me-2"></i> Gestión de Notas de Crédito y Débito</h2>
    
    <form id="formNota" method="POST" action="option_notas/procesar_nota.php" novalidate>

        <div class="row mb-4 bg-light p-3 rounded shadow-sm">
            
            <div class="col-md-3 mb-3">
                <label for="tipoNota" class="form-label fw-bold">Tipo de Documento:</label>
                <select class="form-select" id="tipoNota" name="tipo_nota" required>
                    <option value="">Seleccione tipo de nota</option>
                    <?php foreach ($tiposDocumento as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="cliente" class="form-label fw-bold">Cliente:</label>
                <select class="form-select" id="cliente" name="cliente" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cli): ?>
                        <option value="<?= $cli['Id_cliente'] ?>"><?= htmlspecialchars($cli['Nombre_Cliente']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="noFacturaReferencia" class="form-label fw-bold">Factura Afectada:</label>
                <select class="form-select" id="noFacturaReferencia" name="no_factura_referencia" disabled required>
                    <option value="">Seleccione una factura</option>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="fechaNota" class="form-label fw-bold">Fecha de Emisión:</label>
                <input type="date" class="form-control" id="fechaNota" name="fecha_nota" value="<?= date('Y-m-d') ?>" required>
            </div>
            
        </div>
        
        <div class="mb-3">
            <label for="motivoNota" class="form-label fw-bold">Motivo/Justificación de la Nota:</label>
            <textarea class="form-control" id="motivoNota" name="motivo" rows="2" required placeholder="Ej: Devolución total, Descuento por pronto pago, Error en el precio unitario."></textarea>
        </div>


        <h5 class="mt-4 mb-3">Detalle de Productos a Afectar (Crédito/Débito)</h5>
        <table class="table table-bordered" id="tablaNotas">
            <thead class="table-danger text-white">
                <tr>
                    <th>Producto</th>
                    <th style="width: 100px;">PVP (Crédito)</th>
                    <th style="width: 100px;">Cantidad</th>
                    <th style="width: 200px;">Bodega (Retorno)</th>
                    <th style="width: 120px;">Subtotal</th>
                    <th style="width: 60px;">Acción</th>
                </tr>
            </thead>
            <tbody id="itemsNota">
                <tr class="item-nota d-none" data-template="true">
                    <td>
                        <input type="text" class="form-control producto_nombre" readonly disabled>
                        <input type="hidden" class="producto" name="producto_id[]" disabled>
                    </td>

                    <td>
                        <input type="text" class="form-control costo_formato" readonly disabled>
                        <input type="hidden" class="costo" name="costo[]" disabled>
                    </td>

                    <td class="position-relative">
                        <input type="number" class="form-control cantidad" name="cantidad[]" min="0" value="0" required disabled>
                    </td>

                    <td>
                        <select class="form-select bodega" name="bodega_id[]" required disabled>
                            <option value="">Seleccione bodega</option>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?= $bodega['Id_Bodega'] ?>"><?= htmlspecialchars($bodega['Nombre_Bodega']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <input type="text" class="form-control subtotal_formato" readonly disabled>
                        <input type="hidden" class="subtotal" name="subtotal_item[]" disabled>
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-danger eliminar-item" title="Eliminar producto">&times;</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mb-3 text-end">
            <label class="form-label fw-bold">Subtotal:</label>
            <input type="text" id="subtotalNota" class="form-control d-inline-block w-auto text-end mb-2" readonly>
            <input type="hidden" id="subtotal_hidden" name="subtotal">

            <label class="form-label fw-bold">IVA (19%):</label>
            <input type="text" id="ivaNota" class="form-control d-inline-block w-auto text-end mb-2" readonly>
            <input type="hidden" id="iva_hidden" name="IVA">

            <label class="form-label fw-bold">Total (Afectado):</label>
            <input type="text" id="totalNota" class="form-control d-inline-block w-auto text-end" readonly>
            <input type="hidden" id="total_hidden" name="total">
        </div>

        <button type="submit" class="btn btn-danger"><i class="fas fa-save me-2"></i> Procesar Nota</button>
    </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    
    let FACTURA_SELECCIONADA = {}; 
    
    // --- FUNCIÓN DE UTILIDAD: CÁLCULO DE TOTALES ---
    const actualizarSubtotales = () => {
        let subtotalTotal = 0;
        const ivaRate = 0.19; 

        document.querySelectorAll('.item-nota:not([data-template="true"])').forEach(fila => {
            const costoInput = fila.querySelector('.costo');
            const cantidadInput = fila.querySelector('.cantidad');

            const costo = parseFloat(costoInput.value) || 0; 
            const cantidad = parseFloat(cantidadInput.value) || 0;
            
            const itemSubtotal = cantidad * costo;
            subtotalTotal += itemSubtotal;

            // Mostrar formatos
            fila.querySelector('.costo_formato').value = costo.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
            fila.querySelector('.subtotal_formato').value = itemSubtotal.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });

            // Valores ocultos para PHP
            costoInput.value = costo.toFixed(2); 
            fila.querySelector('.subtotal').value = itemSubtotal.toFixed(2);
        });

        const iva = subtotalTotal * ivaRate;
        const total = subtotalTotal + iva;

        // Mostrar totales
        document.getElementById('subtotalNota').value = subtotalTotal.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
        document.getElementById('ivaNota').value = iva.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
        document.getElementById('totalNota').value = total.toLocaleString('es-EC', { style: 'currency', currency: 'USD' });

        // Totales ocultos para PHP
        document.getElementById('subtotal_hidden').value = subtotalTotal.toFixed(2);
        document.getElementById('iva_hidden').value = iva.toFixed(2);
        document.getElementById('total_hidden').value = total.toFixed(2);
    };
 
    // --- FUNCIÓN PRINCIPAL: LLENAR LA TABLA CON DETALLE DE FACTURA ---
    const llenarDetalleFactura = (facturaId) => {
        const itemsContainer = document.getElementById('itemsNota');
        const templateRow = document.querySelector('.item-nota[data-template="true"]');

        document.querySelectorAll('.item-nota:not([data-template="true"])').forEach(row => row.remove());

        fetch(`option_notas/api_consultas.php?action=getFacturaDetalles&id=${facturaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error al cargar detalles de la factura: ' + data.message);
                    actualizarSubtotales(); 
                    return;
                }

                FACTURA_SELECCIONADA = data.data; 
                
                if (FACTURA_SELECCIONADA.detalles && FACTURA_SELECCIONADA.detalles.length > 0) {
                     FACTURA_SELECCIONADA.detalles.forEach(item => {
                        const clon = templateRow.cloneNode(true);
                        clon.classList.remove('d-none');
                        clon.removeAttribute('data-template');
                        
                        // CORRECCIÓN 2: Habilitar todos los campos para que se envíen
                        clon.querySelectorAll('input, select').forEach(field => {
                            field.removeAttribute('disabled');
                        });
                        
                        // Asignar valores
                        clon.querySelector('.producto').value = item.ID_producto; // Hidden ID
                        clon.querySelector('.producto_nombre').value = item.Nombre_Producto; // Readonly Name
                        
                        // Deshabilitar el campo de nombre para que no se envíe DOS VECES (solo se envía el hidden ID)
                        clon.querySelector('.producto_nombre').disabled = true; 

                        clon.querySelector('.bodega').value = item.ID_Bodega; 
                        clon.querySelector('.cantidad').value = item.Cantidad; 
                        clon.querySelector('.cantidad').setAttribute('max', item.Cantidad); 
                        clon.querySelector('.costo').value = item.PVP; 
                        
                        itemsContainer.appendChild(clon);
                    });
                } 

                actualizarSubtotales();

            }) 
            .catch(error => {
                console.error('Error fetching factura details:', error);
                alert('No se pudo conectar para obtener los detalles de la factura.');
                actualizarSubtotales();
            });
    }; 
    
    // --- EVENTOS CASACADA ---
    document.getElementById('cliente').addEventListener('change', function () {
        const clienteId = this.value;
        const selectFactura = document.getElementById('noFacturaReferencia');
        selectFactura.innerHTML = '<option value="">Cargando facturas...</option>';
        selectFactura.disabled = true;
        
        if (!clienteId) {
            selectFactura.innerHTML = '<option value="">Seleccione una factura</option>';
            document.querySelectorAll('.item-nota:not([data-template="true"])').forEach(row => row.remove());
            actualizarSubtotales();
            return;
        }

        fetch(`option_notas/api_consultas.php?action=getVentas&cliente_id=${clienteId}`)
            .then(response => response.json())
            .then(data => { 
                selectFactura.innerHTML = '<option value="">Seleccione una factura</option>';
                selectFactura.disabled = false;

                if (data.error) {
                    alert('Error al cargar facturas: ' + data.message); 
                    selectFactura.innerHTML = '<option value="">Error al cargar facturas</option>';
                    return;
                }

                const facturas = data.data; 
                
                if (facturas.length === 0) {
                     selectFactura.innerHTML = '<option value="">No hay facturas disponibles para este cliente</option>';
                     return;
                }

                facturas.forEach(f => {
                    const option = document.createElement('option');
                    option.value = f.ID_Egreso;
                    const totalFormateado = parseFloat(f.Total_Egreso).toLocaleString('es-EC', { style: 'currency', currency: 'USD' });
                    option.textContent = `Factura #${f.ID_Egreso} - Total: ${totalFormateado} (${f.Fecha_Egreso})`;
                    selectFactura.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching ventas:', error);
                alert('Error de conexión con la API o respuesta JSON inválida.');
                selectFactura.innerHTML = '<option value="">Error de conexión</option>';
            });
    });

    document.getElementById('noFacturaReferencia').addEventListener('change', function () {
        const facturaId = this.value;
        if (facturaId) {
            llenarDetalleFactura(facturaId);
        } else {
            document.querySelectorAll('.item-nota:not([data-template="true"])').forEach(row => row.remove());
            actualizarSubtotales();
        }
    });


    // --- MANEJO DE EVENTOS DE DETALLE ---
    const itemsContainer = document.getElementById('itemsNota');

    itemsContainer.addEventListener('input', actualizarSubtotales);
    itemsContainer.addEventListener('change', actualizarSubtotales);

    itemsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('eliminar-item')) {
            const filas = document.querySelectorAll('.item-nota:not([data-template="true"])');
            if (filas.length > 0) { 
                e.target.closest('.item-nota').remove();
                actualizarSubtotales();
            }
        }
    });

    // Ejecutar actualización inicial
    actualizarSubtotales();
});
</script>