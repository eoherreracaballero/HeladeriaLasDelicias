<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
require_once __DIR__ . "/../../../app/db/conexion.php";
global $conexion;

// Ruta de destino de la imagen (Ajustada según tu indicación)
$RUTA_BASE_UPLOADS = "/../../../public/img/productos/";

// Verificamos que los datos hayan sido enviados por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../productos.php?msg=Acceso no autorizado.");
    exit;
}

// 1. Recibimos y limpiamos los datos del formulario
$campos = ['nombre', 'tipo', 'categoria', 'und_empaque', 'pvp', 'estado', 'bodega', 'marca', 'proveedor'];
foreach ($campos as $c) {
    if (!isset($_POST[$c]) || $_POST[$c] === '') {
        header("Location: ../productos.php?msg=Faltan campos obligatorios.");
        exit;
    }
}

$nombre      = trim($_POST['nombre']);
$tipo        = trim($_POST['tipo']);
$categoria   = trim($_POST['categoria']);
$undEmpaque  = trim($_POST['und_empaque']);
$pvp         = floatval($_POST['pvp']);
$estado      = trim($_POST['estado']);
$bodega      = intval($_POST['bodega']);
$marca       = trim($_POST['marca']);
$idProv      = intval($_POST['proveedor']);
$rutaImagen  = ''; // Valor por defecto

// 2. PROCESAMIENTO DE LA IMAGEN
if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] === UPLOAD_ERR_OK) {
    $imagen = $_FILES['imagen_producto'];
    $nombreArchivo = $imagen['name'];
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    $nuevoNombre = uniqid('prod_', true) . '.' . $extension; // Generar nombre único

    $carpetaDestino = __DIR__ . $RUTA_BASE_UPLOADS;
    
    // Ruta que se guardará en la DB (Ruta relativa desde la raíz del proyecto)
    $rutaImagenDB = "public/img/productos/" . $nuevoNombre; 

    // Validar tipo y mover archivo
    $permitidos = ['jpg', 'jpeg', 'png'];
    if (in_array($extension, $permitidos) && $imagen['size'] < 5000000) { // Límite de 5MB
        if (move_uploaded_file($imagen['tmp_name'], $carpetaDestino . $nuevoNombre)) {
            $rutaImagen = $rutaImagenDB;
        } else {
            error_log("Fallo al mover archivo de imagen a: " . $carpetaDestino);
        }
    } else {
         header("Location: ../productos.php?msg=El archivo es muy grande o no es JPG/PNG.");
         exit;
    }
}

// 3. ACTUALIZACIÓN DEL SQL: Añadir la columna Ruta_Imagen
$sql = "INSERT INTO producto
        (ID_Proveedor, Nombre_Producto, Tipo, Categoria,
         Und_Empaque, PVP, Estado, ID_Bodega, Marca, Ruta_Imagen)
        VALUES (?,?,?,?,?,?,?,?,?,?)";


$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("❌ Error prepare: " . $conexion->error);
}

// 4. BIND PARAM: 10 Columnas. (i, s, s, s, s, d, s, i, s, s)
// Asumiendo: ID_Proveedor(i), Nombre(s), Tipo(s), Categoria(s), Und_Empaque(s), PVP(d), Estado(s), ID_Bodega(i), Marca(s), Ruta_Imagen(s)
$stmt->bind_param(
    "issssdssis", 
    $idProv, $nombre, $tipo, $categoria,
    $undEmpaque, $pvp, $estado, $bodega, $marca, $rutaImagen 
);

if ($stmt->execute()) {
    header("Location: ../productos.php?msg=Producto Grabado con exito");
    exit;
} else {
    echo "❌ Error al guardar: " . $stmt->error;
    error_log("SQL Error: " . $stmt->error);
}

$stmt->close();
mysqli_close($conexion);
?>