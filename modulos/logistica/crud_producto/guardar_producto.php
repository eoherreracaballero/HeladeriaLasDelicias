<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
require_once __DIR__ . "/../../../app/db/conexion.php";

// Verificamos que los datos hayan sido enviados por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo "⚠️ Acceso no autorizado.";
        exit;
    }

// Recibimos y limpiamos los datos del formulario
$campos = ['nombre', 'tipo', 'categoria', 'und_empaque', 'pvp', 'estado', 'bodega', 'marca', 'proveedor'];
foreach ($campos as $c) {
    if (!isset($_POST[$c]) || $_POST[$c] === '') {
        echo "⚠️ Falta el campo: $c";
        exit;
    }
}

$nombre      = trim($_POST['nombre']);
$tipo        = trim($_POST['tipo']);
$categoria   = trim($_POST['categoria']);
$undEmpaque  = trim($_POST['und_empaque']); // ENUM → string
$pvp         = floatval($_POST['pvp']);
$estado      = trim($_POST['estado']);
$bodega      = intval($_POST['bodega']);    // FK int
$marca       = trim($_POST['marca']);
$idProv      = intval($_POST['proveedor']); // FK int

$sql = "INSERT INTO producto
        (ID_Proveedor, Nombre_Producto, Tipo, Categoria,
         Und_Empaque, PVP, Estado, ID_Bodega, Marca)
        VALUES (?,?,?,?,?,?,?,?,?)";


$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("❌ Error prepare: " . $conexion->error);
}

$stmt->bind_param(
    "issssdiss",   // mapeo correcto
    $idProv, $nombre, $tipo, $categoria,
    $undEmpaque, $pvp, $estado, $bodega, $marca
);

if ($stmt->execute()) {

    /* ─── 5. Redirigir (¡con ; en la línea anterior!) ─── */
    header("Location: ../productos.php?msg=Producto Grabado con exito");
    exit;
} else {
    echo "❌ Error al guardar: " . $stmt->error;
}

$stmt->close();
mysqli_close($conexion);
?>
