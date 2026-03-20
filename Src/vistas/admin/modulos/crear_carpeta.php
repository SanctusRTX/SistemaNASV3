<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-folder-plus"></i> Crear Nueva Carpeta</h4>
        </div>
        <div class="card-body">
            <form action="funciones/crear.php" method="POST">
                <!-- Seleccionar la ubicación -->
                <div class="form-group mb-3">
                    <label for="directorio" class="form-label"><i class="fas fa-sitemap"></i> Selecciona la carpeta base:</label>
                    <select id="directorio" name="directorio" class="form-control" required>
                        <option value="Almacenamiento">Almacenamiento (Raíz)</option>
            <?php
            // Función recursiva para listar todas las subcarpetas
            function listarSubcarpetas($directorioBase, $nivel = 0, $rutaRelativa = 'Almacenamiento') {
                if (!is_dir($directorioBase)) {
                    return;
                }
                
                // Obtener todas las carpetas en este directorio
                $carpetas = array_filter(scandir($directorioBase), function($item) use ($directorioBase) {
                    return $item != '.' && $item != '..' && is_dir($directorioBase . '/' . $item);
                });
                
                // Ordenar carpetas alfabéticamente
                sort($carpetas);
                
                foreach ($carpetas as $carpeta) {
                    // Calcular indentación y ruta
                    $indentacion = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $nivel);
                    $nombreMostrar = $indentacion . ($nivel > 0 ? '↳ ' : '') . $carpeta;
                    $rutaCarpetaActual = $rutaRelativa . '/' . $carpeta;
                    $rutaValor = str_replace('/', '\\', $rutaCarpetaActual); // Normalizar ruta para Windows
                    
                    // Mostrar esta carpeta como opción
                    echo "<option value=\"$rutaValor\">$nombreMostrar</option>";
                    
                    // Llamada recursiva para subcarpetas
                    listarSubcarpetas($directorioBase . '/' . $carpeta, $nivel + 1, $rutaCarpetaActual);
                }
            }

            // Llamada a la función con la carpeta base "Almacenamiento"
            listarSubcarpetas(realpath(__DIR__ . '/../../../Almacenamiento'));
            ?>
                    </select>
                </div>

                <!-- Escribir el nombre de la nueva carpeta -->
                <div class="form-group mb-3">
                    <label for="nombreCarpeta" class="form-label"><i class="fas fa-keyboard"></i> Nombre de la nueva carpeta:</label>
                    <input type="text" id="nombreCarpeta" name="nombreCarpeta" class="form-control" placeholder="Escribe el nombre de la carpeta" required>
                    <small class="form-text text-muted">No uses caracteres especiales como: / \ : * ? " < > |</small>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Crear Carpeta</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Mensaje de éxito o error -->
    <?php
    if (isset($_GET['mensaje'])) {
        $tipo = isset($_GET['tipo']) && $_GET['tipo'] === 'exito' ? 'success' : 'danger';
        $icono = $tipo === 'success' ? 'check-circle' : 'exclamation-triangle';
        echo "<div class='alert alert-$tipo alert-dismissible fade show' role='alert'>";
        echo "<i class='fas fa-$icono'></i> " . htmlspecialchars($_GET['mensaje']);
        echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
        echo "<span aria-hidden='true'>&times;</span>";
        echo "</button>";
        echo "</div>";
    }
    ?>
</div>