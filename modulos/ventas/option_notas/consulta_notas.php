<?php
// CORRECCIÓN FINAL: Usamos TRES NIVELES (../../../) para todas las inclusiones.

// 1. INCLUSIÓN DE ESTRUCTURA (ENCABEZADO)
require_once __DIR__ . "/../../../public/html/encabezado.php";

// 2. INCLUSIÓN DE CONEXIÓN (Mantenemos la ruta funcional)
include(__DIR__ . "/../../../app/db/conexion.php");

// 3. INCLUSIÓN DE ESTILOS (TABLAS)
require_once __DIR__ . "/../../../public/html/tablas.php"; 

// Consulta para obtener todas las Notas de Crédito y Débito
$sqlNotas = "SELECT 
                ep.ID_Egreso, 
                ep.Num_Documento,
                ep.Fecha_Egreso, 
                ep.Total_Egreso, 
                ep.Tipo_Egreso,
                c.Nombre_Cliente,
                ep.Factura_Referencia_Id
             FROM egreso_producto ep
             JOIN cliente c ON ep.Id_cliente = c.Id_cliente
             WHERE ep.Tipo_Egreso IN ('Nota de Crédito', 'Nota de Débito') 
                OR ep.Num_Documento IS NOT NULL -- Esto forzará a que aparezcan las notas grabadas
             ORDER BY ep.ID_Egreso DESC";

$resultNotas = $conexion->query($sqlNotas);

// Manejo de resultados: Asegura que el query fue exitoso antes de usar fetch_all
if ($resultNotas === FALSE) {
    die("Error en la consulta de notas: " . $conexion->error);
}

$notas = $resultNotas->fetch_all(MYSQLI_ASSOC);
$conexion->close();
?>

<main class="p-4 flex-grow-1 fade-in" id="contenido">
    <h2 class="text-danger mb-4"><i class="fas fa-clipboard-list me-2"></i> Listado de Notas de Crédito y Débito</h2>
    
    <div class="mb-3 text-end">
        <a href="../notas.php" class="btn btn-danger">
            <i class="fas fa-file-invoice"></i> Crear Nueva Nota
        </a>
    </div>

    <table class="table table-striped table-bordered tabla-datatable">
        <thead class="table-dark">
            <tr>
                <th>Tipo Doc.</th>
                <th>No. Documento</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Factura Ref.</th>
                <th>Total Afectado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notas as $nota): ?>
            <tr>
                <td><?= htmlspecialchars($nota['Tipo_Egreso']) ?></td>
                <td><?= htmlspecialchars($nota['Num_Documento']) ?></td>
                <td><?= htmlspecialchars($nota['Fecha_Egreso']) ?></td>
                <td><?= htmlspecialchars($nota['Nombre_Cliente']) ?></td>
                <td>#<?= htmlspecialchars($nota['Factura_Referencia_Id']) ?></td>
                <td><?= number_format($nota['Total_Egreso'], 2) ?></td>
                <td class="text-center">
                    <a href="detalle_nota.php?id=<?= $nota['ID_Egreso'] ?>" class="btn btn-sm btn-info text-white" title="Ver Detalle">
                        <i class="fas fa-search"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php 
