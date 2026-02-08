<?php
ob_start(); 
session_start(); 

// 1. INCLUSIÓN DE COMPONENTES Y CONEXIÓN
require_once __DIR__ . "/../../public/html/encabezado.php";
include(__DIR__ . "/../../app/db/conexion.php");
require_once __DIR__ . "/../../public/html/tablas.php";
global $conexion;

// 2. CONSULTA MAESTRA CORREGIDA (Cálculo real de Stocks)
$sql_activos = "SELECT p.*, pr.Nombre_Proveedor, 
                (SELECT SUM(i.Stock) FROM inventario i WHERE i.ID_Producto = p.ID_Producto) as Stock_Total
                FROM producto p 
                LEFT JOIN proveedor pr ON p.ID_Proveedor = pr.ID_Proveedor 
                WHERE p.Estado = 'Activo' OR p.Estado IS NULL OR p.Estado = ''
                ORDER BY p.ID_Producto ASC";
$res_activos = $conexion->query($sql_activos);

$sql_suspendidos = "SELECT p.* FROM producto p WHERE p.Estado = 'Inactivo' ORDER BY p.ID_Producto ASC";
$res_suspendidos = $conexion->query($sql_suspendidos);

// 3. DATOS PARA SELECTORES
$bodegas = $conexion->query("SELECT Id_Bodega, Nombre_Bodega FROM bodega");
$proveedores = $conexion->query("SELECT ID_Proveedor, Nombre_Proveedor FROM proveedor");
$lista_bodegas = $bodegas->fetch_all(MYSQLI_ASSOC);
$lista_proveedores = $proveedores->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .img-tabla { object-fit: cover; transition: transform 0.2s; cursor: pointer; }
    .img-tabla:hover { transform: scale(1.1); }
    .badge-fs-5 { font-size: 1.1rem !important; min-width: 80px; }
</style>

<main class="container-fluid p-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary m-0 fw-bold"><i class="fas fa-boxes me-2"></i>Gestión de Inventario</h2>
        <span class="badge bg-primary px-3 py-2 shadow-sm fs-6">Productos Activos: <?= $res_activos->num_rows ?></span>
    </div>

    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 small text-uppercase fw-bold"><i class="fas fa-plus-circle me-2"></i>Nuevo Producto</h5>
        </div>
        <div class="card-body bg-light border">
            <form method="POST" action="crud_producto/guardar_producto.php" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="nombre" required placeholder="Nombre del producto">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Marca</label>
                        <input type="text" class="form-control form-control-sm" name="marca" required placeholder="Marca">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Empaque</label>
                        <select class="form-select form-select-sm" name="und_empaque">
                            <option value="Unidad">Unidad</option>
                            <option value="x10">Pack x10</option>
                            <option value="x12">Pack x12</option>
                            <option value="Caja">Caja</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold small">Costo</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="costo_unitario" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold small">PVP</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="pvp" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Proveedor</label>
                        <select class="form-select form-select-sm" name="id_proveedor" required>
                            <?php foreach ($lista_proveedores as $prov): ?>
                                <option value="<?= $prov['ID_Proveedor'] ?>"><?= $prov['Nombre_Proveedor'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-primary">Bodega Inicial</label>
                        <select class="form-select form-select-sm border-primary" name="id_bodega" required>
                            <?php foreach ($lista_bodegas as $b): ?>
                                <option value="<?= $b['Id_Bodega'] ?>"><?= $b['Nombre_Bodega'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-secondary">Mínimo</label>
                        <input type="number" class="form-control form-control-sm" name="stock_min" value="10" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-secondary">Máximo</label>
                        <input type="number" class="form-control form-control-sm" name="stock_max" value="50" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Estado Inicial</label>
                        <select class="form-select form-select-sm" name="estado">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i>GUARDAR PRODUCTO E INVENTARIO
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th style="width: 80px;">ID</th>
                            <th>Imagen</th>
                            <th class="text-start">Producto / Proveedor</th>
                            <th>Marca</th>
                            <th>Empaque</th>
                            <th>Stock Total</th>
                            <th>Costo</th>
                            <th>PVP</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $res_activos->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border badge-fs-5 px-3 py-2 shadow-sm">#<?= $fila['ID_Producto'] ?></span></td>
                                
                                <td>
                                    <?php $img = !empty($fila['Ruta_Imagen']) ? "../../".$fila['Ruta_Imagen'] : "../../public/img/default-product.png"; ?>
                                    <img src="<?= $img ?>" width="50" height="50" class="rounded border img-tabla shadow-sm" onclick='verKardex(<?= $fila['ID_Producto'] ?>, "<?= htmlspecialchars($fila['Nombre_Producto']) ?>")'>
                                </td>

                                <td class="text-start">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($fila['Nombre_Producto']) ?></div>
                                    <div class="text-primary small small-caps"><i class="fas fa-truck me-1"></i><?= htmlspecialchars($fila['Nombre_Proveedor']) ?></div>
                                </td>

                                <td><?= htmlspecialchars($fila['Marca']) ?></td>
                                <td><span class="text-muted"><?= htmlspecialchars($fila['Und_Empaque']) ?></span></td>

                                <td>
                                    <span class="badge <?= ($fila['Stock_Total'] > 0) ? 'bg-info' : 'bg-danger' ?> badge-fs-5 px-3 py-2 shadow-sm">
                                        <?= $fila['Stock_Total'] ?? 0 ?>
                                    </span>
                                </td>

                                <td class="small text-muted fw-bold">$<?= number_format($fila['Costo_Unitario'], 2) ?></td>
                                <td class="fw-bold text-success fs-6">$<?= number_format($fila['PVP'], 2) ?></td>

                                <td><span class="badge bg-success-subtle text-success border border-success px-2"><?= $fila['Estado'] ?></span></td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-info text-white shadow-sm" onclick='verKardex(<?= $fila['ID_Producto'] ?>, "<?= htmlspecialchars($fila['Nombre_Producto']) ?>")' title="Ver Bodegas">
                                            <i class="fas fa-warehouse"></i>
                                        </button>
                                        <a href="crud_producto/editar_producto.php?id=<?= $fila['ID_Producto'] ?>" class="btn btn-sm btn-warning shadow-sm"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-danger shadow-sm text-white" onclick="confirmarSuspension(<?= $fila['ID_Producto'] ?>, '<?= $fila['Nombre_Producto'] ?>')" title="Suspender">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if($res_suspendidos->num_rows > 0): ?>
    <div class="card shadow-sm border-0 border-top border-danger border-4 mb-4">
        <div class="card-header bg-white text-danger py-2">
            <h6 class="mb-0 fw-bold small text-uppercase"><i class="fas fa-eye-slash me-2"></i>Archivo de Inactivos</h6>
        </div>
        <div class="card-body p-0 bg-light">
            <table class="table table-sm table-borderless align-middle mb-0">
                <tbody>
                    <?php while ($sus = $res_suspendidos->fetch_assoc()): ?>
                        <tr class="border-bottom">
                            <td class="ps-3 text-muted" style="width: 100px;">ID: <?= $sus['ID_Producto'] ?></td>
                            <td class="fw-bold text-muted text-start"><?= htmlspecialchars($sus['Nombre_Producto']) ?></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-link btn-sm text-success text-decoration-none fw-bold" onclick="confirmarReactivacion(<?= $sus['ID_Producto'] ?>)">
                                    <i class="fas fa-undo-alt me-1"></i> Reactivar
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</main>

<div class="modal fade" id="modalKardex" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-search-plus me-2 text-info"></i>Detalle de Stock por Bodega
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-white p-4">
                <h3 id="kardexTitulo" class="text-primary fw-bold text-uppercase mb-4 border-bottom pb-2"></h3>
                <div id="kardexContenido">
                    </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary fw-bold shadow-sm" data-bs-dismiss="modal">CERRAR VENTANA</button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para Suspender
function confirmarSuspension(id, nombre) {
    Swal.fire({
        title: '¿Suspender Producto?',
        text: `"${nombre}" no se podrá usar en ventas hasta que se reactive.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, suspender',
        cancelButtonText: 'Cancelar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_producto/suspender_producto.php?id=' + id; });
}

// Función para Reactivar
function confirmarReactivacion(id) {
    Swal.fire({
        title: '¿Reactivar Producto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Sí, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((r) => { if (r.isConfirmed) window.location.href = 'crud_producto/reactivar_producto.php?id=' + id; });
}

// Función para abrir el Modal y cargar obtener_kardex.php
function verKardex(id, nombre) {
    document.getElementById('kardexTitulo').innerText = nombre;
    document.getElementById('kardexContenido').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-2 text-muted fw-bold">Analizando existencias en tiempo real...</p>
        </div>`;
    
    // Iniciar Modal de Bootstrap
    var modalElement = document.getElementById('modalKardex');
    var instance = bootstrap.Modal.getOrCreateInstance(modalElement);
    instance.show();

    // Llamada AJAX al servidor
    fetch(`crud_producto/obtener_kardex.php?id=${id}`)
        .then(response => {
            if (!response.ok) throw new Error('Error 404: Archivo no encontrado');
            return response.text();
        })
        .then(data => {
            document.getElementById('kardexContenido').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('kardexContenido').innerHTML = `
                <div class="alert alert-danger shadow-sm">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error: No se pudo cargar el archivo <strong>obtener_kardex.php</strong>. 
                    Verifica que esté dentro de la carpeta <strong>crud_producto</strong>.
                </div>`;
        });
}
</script>

<?php 
mysqli_close($conexion); 
ob_end_flush(); 
?>