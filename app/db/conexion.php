
<?php

define("BASE_PATH", dirname(__DIR__));

//nombre del servidor, usuario, contraseña y base de datos

$servidor= "localhost";
$usuario= "root";
$clave= ""; // Cambia la contraseña si es necesario
$bd= "heladeria"; // Verifica el nombre de la base de datos

// crear la conexión
$conexion = new mysqli($servidor, $usuario, $clave, $bd);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
// echo "Conexión establecida correctamente";
?>