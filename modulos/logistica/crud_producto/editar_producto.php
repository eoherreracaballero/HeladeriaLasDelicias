    <?php
    ob_start();

    // incluir encabezado.php para cargar estilos y scripts
    require_once __DIR__ . "/../../../public/html/encabezado.php";

    // Conexión a la base de datos
    include(__DIR__ . "/../../../app/db/conexion.php");

    // Estilos para tablas
    require_once __DIR__ . "/../../../public/html/tablas.php";

    global $conexion;

    // Ruta base para la carpeta de imágenes subidas (usada por PHP para mover archivos)
    $RUTA_BASE_UPLOADS = "/../../../public/img/productos/";

    // 1. VALIDAR Y OBTENER ID
    $id = isset($_GET['ID_Producto']) ? intval($_GET['ID_Producto']) : 0;

    // Consulta inicial para cargar datos del producto
    $stmt_select = $conexion->prepare("SELECT * FROM producto WHERE ID_Producto = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $res = $stmt_select->get_result();

    if (!$res || $res->num_rows == 0) {
        echo "<div class='alert alert-danger m-4'>❌ Producto no encontrado.</div>";
        exit();
    }
    $producto = $res->fetch_assoc();
    $stmt_select->close();


    // 2. GUARDAR CAMBIOS (POST)
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        
        // Recibir y sanear variables
        $nombre = trim($_POST['nombre']);
        $tipo = trim($_POST['tipo']);
        $categoria = trim($_POST['categoria']);
        $und_empaque = trim($_POST['und_empaque']);
        $pvp = floatval($_POST['pvp']);
        $estado = trim($_POST['estado']);
        $bodega = intval($_POST['bodega']);
        $marca = trim($_POST['marca']);
        
        // 3. PROCESAMIENTO DE IMAGEN (Solo si se sube una nueva)
        $actualizarImagen = false;
        $rutaImagenDB = '';

        if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] === UPLOAD_ERR_OK) {
            $imagen = $_FILES['imagen_producto'];
            $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
            $nuevoNombre = uniqid('prod_', true) . '.' . $extension;
            
            $carpetaDestino = __DIR__ . $RUTA_BASE_UPLOADS;
            // Ruta que se guardará en la DB (Ej: public/img/productos/...)
            $rutaImagenDB = "public/img/productos/" . $nuevoNombre;
            
            $permitidos = ['jpg', 'jpeg', 'png'];
            if (in_array($extension, $permitidos) && $imagen['size'] < 5000000) { // Límite de 5MB
                if (move_uploaded_file($imagen['tmp_name'], $carpetaDestino . $nuevoNombre)) {
                    $actualizarImagen = true;
                    
                    // CRÍTICO: Eliminar la imagen antigua del servidor si existe
                    if (!empty($producto['Ruta_Imagen'])) {
                        $rutaAntigua = __DIR__ . "/../../../" . $producto['Ruta_Imagen'];
                        if (file_exists($rutaAntigua)) {
                            unlink($rutaAntigua); // Elimina el archivo físico
                        }
                    }
                } else {
                    error_log("Fallo al mover archivo de imagen a: " . $carpetaDestino);
                }
            }
        }

        // 4. CONSTRUCCIÓN DEL SQL DE UPDATE (Sentencia Preparada)
        $sql = "UPDATE producto SET 
            Nombre_Producto = ?,
            Tipo = ?,
            Categoria = ?,
            Und_Empaque = ?,
            PVP = ?,
            Estado = ?,
            ID_Bodega = ?,
            Marca = ?";
        
        // Parámetros base: s, s, s, s, d, s, i, s
        $tipos = "ssssdsis";
        // Lista de valores que se enlazarán
        $parametros = [$nombre, $tipo, $categoria, $und_empaque, $pvp, $estado, $bodega, $marca];
        
        // Si la imagen se actualizó, añadir Ruta_Imagen a la sentencia y a los parámetros
        if ($actualizarImagen) {
            $sql .= ", Ruta_Imagen = ?";
            $tipos .= "s";
            $parametros[] = $rutaImagenDB;
        }
        
        // Cláusula WHERE y ID final
        $sql .= " WHERE ID_Producto = ?";
        $tipos .= "i";
        $parametros[] = $id;

        // 5. EJECUCIÓN DEL UPDATE PREPARADO
        $stmt = $conexion->prepare($sql);
        
        // Usamos call_user_func_array para manejar el número dinámico de parámetros de bind_param
        array_unshift($parametros, $tipos); // Pone el string de tipos al inicio del array de parámetros
        
        call_user_func_array([$stmt, 'bind_param'], $parametros);

        if ($stmt->execute()) {
            header("Location: ../productos.php?msg=Producto Actualizado con éxito"); // Dos niveles atrás para ir a productos.php
            exit();
        } else {
            echo "<div class='alert alert-danger m-4'>❌ Error al actualizar: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }

    // 6. FORMULARIO HTML
    ?>

    <main class="container p-4">
        <h2 class="text-primary mb-4">✏️ Editar Producto: <?= htmlspecialchars($producto['Nombre_Producto']) ?></h2>
        
        <form method="POST" enctype="multipart/form-data"> 
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['Nombre_Producto']) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label for="tipo" class="form-label">Tipo</label>
                    <input type="text" name="tipo" class="form-control" value="<?= htmlspecialchars($producto['Tipo']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="categoria" class="form-label">Categoría</label>
                    <input type="text" name="categoria" class="form-control" value="<?= htmlspecialchars($producto['Categoria']) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label for="und_empaque" class="form-label">Und. Empaque</label>
                    <select name="und_empaque" class="form-select" required>
                        <?php
                        $enumQuery = $conexion->query("SHOW COLUMNS FROM producto LIKE 'Und_Empaque'");
                        $enumRow = $enumQuery->fetch_assoc();
                        preg_match("/^enum\('(.*)'\)$/", $enumRow['Type'], $matches);
                        $enumValues = explode("','", $matches[1]);
                        
                        foreach ($enumValues as $valor) {
                            $selected = ($producto['Und_Empaque'] === $valor) ? "selected" : "";
                            echo "<option value='$valor' $selected>$valor</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="pvp" class="form-label">PVP</label>
                    <input type="number" step="0.01" name="pvp" class="form-control" value="<?= htmlspecialchars($producto['PVP']) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($producto['Estado']) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label for="bodega" class="form-label">ID Bodega</label>
                    <input type="text" name="bodega" class="form-control" value="<?= htmlspecialchars($producto['ID_Bodega']) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($producto['Marca']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="imagen_producto" class="form-label">Cargar Nueva Imagen (Opcional)</label>
                    <input type="file" class="form-control" id="imagen_producto" name="imagen_producto" accept="image/jpeg, image/png">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Imagen Actual</label><br>
                    <?php 
                    // CORRECCIÓN CLAVE: RUTA RELATIVA AL NAVEGADOR
                    $rutaImagenDB = $producto['Ruta_Imagen'] ?? '';
                    $imagenActualSrc = !empty($rutaImagenDB) 
                        ? "../../../" . $rutaImagenDB // TRES NIVELES (../../../)
                        : "../../../public/img/default-product.png"; // TRES NIVELES (../../../)
                    ?>
                    <img id="imagen_preview_edit" src="<?= htmlspecialchars($imagenActualSrc) ?>" 
                        alt="Actual" style="max-width: 150px; height: auto; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
    // Script de previsualización para la edición
    document.getElementById('imagen_producto').addEventListener('change', function(event) {
        const preview = document.getElementById('imagen_preview_edit');
        const file = event.target.files[0];
        
        // Si se selecciona un archivo, muestra la previsualización
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        } 
    });
    </script>

    <?php 
    mysqli_close($conexion);
    ob_end_flush();
    ?>