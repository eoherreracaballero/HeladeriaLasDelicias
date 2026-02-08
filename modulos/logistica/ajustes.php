<?php
session_start();

// 1. CARGA DE COMPONENTES
require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php";

// 2. CONSULTAS DINÁMICAS
$productos = $conexion->query("SELECT ID_Producto, Nombre_Producto, Stock, Costo_Unitario FROM producto WHERE Estado = 'Activo'");
$bodegas = $conexion->query("SELECT Id_Bodega, Nombre_Bodega FROM bodega WHERE Estado = 'Activa'");

// 3. PREPARACIÓN DE SELECTS PARA JS (Diseño moderno: Incluimos stock actual y costo)
$selectProductos = '<option value="" disabled selected>Seleccione un producto...</option>';
while ($prod = $productos->fetch_assoc()) {
    $selectProductos .= "<option value='{$prod['ID_Producto']}' data-stock='{$prod['Stock']}' data-costo='{$prod['Costo_Unitario']}'>{$prod['Nombre_Producto']}</option>";
}

$selectBodegas = '<option value="" disabled selected>Seleccione bodega...</option>';
while ($bod = $bodegas->fetch_assoc()) {
    $selectBodegas .= "<option value='{$bod['Id_Bodega']}'>{$bod['Nombre_Bodega']}</option>";
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root { --primary-dark: #2c3e50; --accent-blue: #3498db; }
    .card-header-custom { background: var(--primary-dark); color: white; border-radius: 8px 8px 0 0 !important; }
    .table-modern thead { background: #f8f9fa; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
    .input-ajuste { border: 1px solid #dce1e6; border-radius: 6px; padding: 8px; transition: all 0.3s; }
    .input-ajuste:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2); }
    .badge-input { font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; }
</style>

<main class="container-fluid p-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary fw-bold mb-0"><i class="fas fa-sliders-h me-2"></i>Ajustes Técnicos de Inventario</h2>
            <p class="text-muted small mb-0">Gestión de mermas, daños y correcciones de stock físico.</p>
        </div>
        <div class="btn-group shadow-sm">
            <a href="crud_ajustes/consulta_ajustes.php" class="btn btn-outline-primary"><i class="fas fa-history me-1"></i> Historial</a>
            <button type="submit" form="formAjuste" class="btn btn-primary"><i class="fas fa-save me-1"></i> Procesar Ajuste</button>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <i class="fas fa-info-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="crud_ajustes/guardar_ajuste.php" id="formAjuste">
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Tipo de Ajuste</label>
                        <select name="tipo_ajuste" id="tipo_ajuste" class="form-select input-ajuste" required onchange="actualizarInterfaz()">
                            <option value="ENTRADA">🟢 INGRESO (Sobrante/Hallazgo)</option>
                            <option value="SALIDA" selected>🔴 EGRESO (Merma/Daño/Perdida)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Fecha Efectiva</label>
                        <input type="date" name="fecha" class="form-control input-ajuste" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-muted">Justificativo General del Movimiento</label>
                        <input type="text" name="referencia" class="form-control input-ajuste" placeholder="Ej: Ajuste mensual tras toma física de bodega norte" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Detalle de Artículos</h6>
                <button type="button" class="btn btn-success btn-sm px-3" onclick="agregarFila()">
                    <i class="fas fa-plus me-1"></i> Añadir Línea
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0" id="tablaDetalle">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Producto</th>
                            <th style="width: 20%;">Bodega</th>
                            <th style="width: 15%;" class="text-center">Stock Sistema</th>
                            <th style="width: 15%;" class="text-center">Cantidad Ajuste</th>
                            <th style="width: 10%;" class="text-center">Nuevo Stock</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyDetalle">
                        </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-top-0 py-4">
                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Ítems a Ajustar:</span>
                            <span class="fw-bold" id="totalItems">0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Impacto Estimado Valorizado:</span>
                            <span class="fw-bold text-primary" id="valorTotal">$ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const selectProductos = `<?= $selectProductos ?>`;
const selectBodegas = `<?= $selectBodegas ?>`;

function agregarFila() {
    const tbody = document.getElementById("tbodyDetalle");
    const tr = document.createElement("tr");
    tr.className = "border-bottom";

    tr.innerHTML = `
        <td class="p-3">
            <select name="id_producto[]" class="form-select input-ajuste select-prod" required onchange="cargarInfoProducto(this)">
                ${selectProductos}
            </select>
        </td>
        <td>
            <select name="id_bodega[]" class="form-select input-ajuste" required>
                ${selectBodegas}
            </select>
        </td>
        <td class="text-center">
            <span class="badge bg-light text-dark stock-actual border">0.00</span>
        </td>
        <td>
            <input type="number" name="cantidad[]" step="any" min="0.01" class="form-control input-ajuste text-center fw-bold" 
                   placeholder="0.00" required oninput="calcularFila(this)">
        </td>
        <td class="text-center">
            <span class="fw-bold text-primary stock-final">0.00</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-link text-danger" onclick="eliminarFila(this)">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
        <input type="hidden" class="costo-oculto" value="0">
    `;

    tbody.appendChild(tr);
}

function cargarInfoProducto(select) {
    const option = select.options[select.selectedIndex];
    const fila = select.closest("tr");
    const stockActual = parseFloat(option.getAttribute('data-stock')) || 0;
    const costo = parseFloat(option.getAttribute('data-costo')) || 0;

    fila.querySelector(".stock-actual").textContent = stockActual.toFixed(2);
    fila.querySelector(".costo-oculto").value = costo;
    calcularFila(fila.querySelector("input[name='cantidad[]']"));
}

function calcularFila(input) {
    const fila = input.closest("tr");
    const tipo = document.getElementById("tipo_ajuste").value;
    const stockActual = parseFloat(fila.querySelector(".stock-actual").textContent) || 0;
    const cantAjuste = parseFloat(input.value) || 0;
    
    let nuevoStock = (tipo === "ENTRADA") ? (stockActual + cantAjuste) : (stockActual - cantAjuste);
    
    const spanFinal = fila.querySelector(".stock-final");
    spanFinal.textContent = nuevoStock.toFixed(2);
    
    // Alerta visual si el stock queda en negativo en una salida
    if(nuevoStock < 0) {
        spanFinal.className = "fw-bold text-danger stock-final";
    } else {
        spanFinal.className = "fw-bold text-primary stock-final";
    }

    resumenGeneral();
}

function resumenGeneral() {
    const filas = document.querySelectorAll("#tbodyDetalle tr");
    let totalItems = 0;
    let valorTotal = 0;

    filas.forEach(f => {
        const cant = parseFloat(f.querySelector("input[name='cantidad[]']").value) || 0;
        const costo = parseFloat(f.querySelector(".costo-oculto").value) || 0;
        totalItems += cant;
        valorTotal += (cant * costo);
    });

    document.getElementById("totalItems").textContent = totalItems.toFixed(2);
    document.getElementById("valorTotal").textContent = "$ " + valorTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
}

function eliminarFila(btn) {
    btn.closest("tr").remove();
    resumenGeneral();
}

function actualizarInterfaz() {
    // Recalcular todas las filas si cambian de entrada a salida
    const inputs = document.querySelectorAll("input[name='cantidad[]']");
    inputs.forEach(i => calcularFila(i));
}

// Inicializar con una fila vacía
window.onload = agregarFila;
</script>