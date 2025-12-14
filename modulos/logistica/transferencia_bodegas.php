<?php
session_start();
// Asegúrate de que las rutas sean correctas desde /modulos/logistica/

require_once __DIR__ . "/../../public/html/encabezado.php";
include("../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php";

// 1. Obtener Bodegas (Origen y Destino)
$sqlBodegas = "SELECT Id_Bodega, Nombre_Bodega FROM bodega";
$resultBodegas = $conexion->query($sqlBodegas);
$bodegas = $resultBodegas ? $resultBodegas->fetch_all(MYSQLI_ASSOC) : [];

// 2. Obtener Productos disponibles
$sqlProductos = "SELECT ID_Producto, Nombre_Producto FROM producto WHERE Estado = 'Disponible'";
$resultProductos = $conexion->query($sqlProductos);
$productos = $resultProductos ? $resultProductos->fetch_all(MYSQLI_ASSOC) : [];

// 3. Obtener el Inventario actual (Stock por Producto y Bodega)
$sqlInventario = "SELECT ID_Producto, Id_Bodega, Stock FROM inventario WHERE Stock > 0";
$resultInventario = $conexion->query($sqlInventario);

$inventarioData = [];
if ($resultInventario) {
    while ($row = $resultInventario->fetch_assoc()) {
        // Estructura: inventarioData[ID_Producto][ID_Bodega] = Stock
        $inventarioData[$row['ID_Producto']][$row['Id_Bodega']] = (float)$row['Stock'];
    }
}
$inventarioJson = json_encode($inventarioData); // Se pasa a JavaScript para validación
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-primary mb-4"><i class="fas fa-truck-moving me-2"></i> Transferencia de Inventario entre Bodegas</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ Error: <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <form id="formTransferencia" method="POST" action="option_transferencias/procesar_transferencia.php"> 
        <div class="row g-3 border p-4 rounded shadow-sm">
            
            
            <div class="col-md-6">
                <label for="producto" class="form-label">Producto a Transferir</label>
                <select class="form-select" id="producto" name="producto_id" required>
                    <option value="">Seleccione el producto</option>
                    <?php foreach ($productos as $prod): ?>
                        <option value="<?= $prod['ID_Producto'] ?>"><?= htmlspecialchars($prod['Nombre_Producto']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="origen" class="form-label">Bodega de Origen</label>
                <select class="form-select" id="origen" name="bodega_origen_id" required>
                    <option value="">Seleccione origen</option>
                    <?php foreach ($bodegas as $bod): ?>
                        <option value="<?= $bod['Id_Bodega'] ?>"><?= htmlspecialchars($bod['Nombre_Bodega']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small id="stockInfo" class="text-muted d-block mt-1"></small>
            </div>

            <div class="col-md-3">
                <label for="cantidad" class="form-label">Cantidad a Mover</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
            </div>

            <div class="col-md-6">
                <label for="destino" class="form-label">Bodega de Destino</label>
                <select class="form-select" id="destino" name="bodega_destino_id" required>
                    <option value="">Seleccione destino</option>
                    <?php foreach ($bodegas as $bod): ?>
                        <option value="<?= $bod['Id_Bodega'] ?>"><?= htmlspecialchars($bod['Nombre_Bodega']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="motivo" class="form-label">Motivo/Comentario</label>
                <input type="text" class="form-control" id="motivo" name="motivo" maxlength="255">
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success" id="btnTransferir" disabled>
                    <i class="fas fa-exchange-alt me-2"></i> Realizar Transferencia
                </button>
            </div>
        </div>
    </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Datos de inventario cargados desde PHP
    const INVENTARIO = <?= $inventarioJson ?>;
    const form = document.getElementById('formTransferencia');
    const selectProducto = document.getElementById('producto');
    const selectOrigen = document.getElementById('origen');
    const selectDestino = document.getElementById('destino');
    const inputCantidad = document.getElementById('cantidad');
    const stockInfo = document.getElementById('stockInfo');
    const btnTransferir = document.getElementById('btnTransferir');

    // --- FUNCIÓN DE VALIDACIÓN Y ACTUALIZACIÓN ---
    const validarTransferencia = () => {
        const prodId = selectProducto.value;
        const origenId = selectOrigen.value;
        const destinoId = selectDestino.value;
        const cantidad = parseInt(inputCantidad.value) || 0;
        
        let stockDisponible = 0;
        let esValido = false;
        
        // 1. Verificar Stock de Origen
        if (prodId && origenId && INVENTARIO[prodId] && INVENTARIO[prodId][origenId] !== undefined) {
            stockDisponible = INVENTARIO[prodId][origenId];
            stockInfo.textContent = `Stock disponible en origen: ${stockDisponible} uds.`;
            stockInfo.classList.remove('text-danger');
        } else {
            stockInfo.textContent = 'Stock disponible en origen: 0 uds.';
            stockInfo.classList.add('text-danger');
        }

        // 2. Comprobaciones Lógicas
        const stockSuficiente = (cantidad > 0) && (cantidad <= stockDisponible);
        const bodegasDiferentes = origenId !== destinoId;
        
        if (prodId && origenId && destinoId && stockSuficiente && bodegasDiferentes) {
            esValido = true;
        }

        // 3. Mostrar errores de bodega/cantidad
        if (origenId && destinoId && !bodegasDiferentes) {
            stockInfo.textContent = '⚠️ Las bodegas de origen y destino deben ser diferentes.';
            stockInfo.classList.add('text-danger');
            esValido = false;
        }

        if (stockDisponible > 0 && cantidad > stockDisponible) {
             stockInfo.textContent = `⚠️ Cantidad excede el stock. Máximo: ${stockDisponible} uds.`;
             stockInfo.classList.add('text-danger');
             esValido = false;
        }
        
        btnTransferir.disabled = !esValido;
        return esValido;
    };

    // --- LISTENERS ---
    const inputs = [selectProducto, selectOrigen, selectDestino, inputCantidad];
    inputs.forEach(input => {
        input.addEventListener('change', validarTransferencia);
        input.addEventListener('input', validarTransferencia);
    });

    // Validar al cargar la página
    validarTransferencia();
});
</script>

<?php mysqli_close($conexion); ?>