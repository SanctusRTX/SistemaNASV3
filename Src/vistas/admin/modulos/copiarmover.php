<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-copy"></i> Mover o Copiar Archivos</h4>
        </div>
        <div class="card-body">
            <form action="/Sistema-NASv3/Src/funciones/copiar_mover.php" method="POST">
                <!-- Selección de carpeta de origen -->
                <div class="form-group mb-3">
                    <label for="carpetaOrigen" class="form-label"><i class="fas fa-folder-open"></i> Carpeta de Origen:</label>
                    <select id="carpetaOrigen" name="carpetaOrigen" class="form-control" required>
                        <option value="" disabled selected>Seleccione una carpeta de origen</option>
            <?php
            // Definir la ruta absoluta a la carpeta Almacenamiento
            $rutaAlmacenamiento = __DIR__ . '/../../../Almacenamiento';
            
            // Función para listar carpetas y subcarpetas recursivamente
            function listarCarpetasSelect($directorio, $nivel = 0, $rutaRelativa = 'Almacenamiento') {
                if (!is_dir($directorio)) {
                    return;
                }
                
                // Obtener todas las carpetas en este directorio
                $carpetas = array_filter(scandir($directorio), function($item) use ($directorio) {
                    return $item != '.' && $item != '..' && is_dir($directorio . '/' . $item);
                });
                
                // Ordenar carpetas alfabéticamente
                sort($carpetas);
                
                foreach ($carpetas as $carpeta) {
                    // Calcular indentación y ruta
                    $indentacion = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $nivel);
                    $nombreMostrar = $indentacion . ($nivel > 0 ? '↳ ' : '') . $carpeta;
                    $rutaCarpeta = $rutaRelativa . '/' . $carpeta;
                    
                    // Mostrar esta carpeta como opción
                    echo "<option value='$rutaCarpeta'>$nombreMostrar</option>";
                    
                    // Llamada recursiva para subcarpetas
                    listarCarpetasSelect($directorio . '/' . $carpeta, $nivel + 1, $rutaCarpeta);
                }
            }
            
            // Opción para la carpeta raíz
            echo "<option value='Almacenamiento'>Almacenamiento (Raíz)</option>";
            
            // Listar todas las subcarpetas recursivamente
            if (is_dir($rutaAlmacenamiento)) {
                listarCarpetasSelect($rutaAlmacenamiento);
            } else {
                echo "<option value='' disabled>Error: Directorio no encontrado</option>";
            }
            ?>
        </select>

                <!-- Selección de carpeta de destino -->
                <div class="form-group mb-3">
                    <label for="carpetaDestino" class="form-label"><i class="fas fa-folder"></i> Carpeta de Destino:</label>
                    <select id="carpetaDestino" name="carpetaDestino" class="form-control" required>
                        <option value="" disabled selected>Seleccione una carpeta de destino</option>
                        <?php
                        // Opción para la carpeta raíz
                        echo "<option value='Almacenamiento'>Almacenamiento (Raíz)</option>";
                        
                        // Listar todas las subcarpetas recursivamente
                        if (is_dir($rutaAlmacenamiento)) {
                            listarCarpetasSelect($rutaAlmacenamiento);
                        } else {
                            echo "<option value='' disabled>Error: Directorio no encontrado</option>";
                        }
                        ?>
                    </select>
                    <small class="form-text text-muted">Selecciona la carpeta donde deseas copiar o mover los archivos.</small>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                    <div>
                        <button type="submit" name="accion" value="copiar" class="btn btn-primary"><i class="fas fa-copy"></i> Copiar</button>
                        <button type="submit" name="accion" value="mover" class="btn btn-warning"><i class="fas fa-cut"></i> Mover</button>
                    </div>
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

<!-- JavaScript para mejorar la experiencia de usuario -->
<script>
$(document).ready(function() {
    // Deshabilitar la opción de seleccionar la misma carpeta como origen y destino
    $('#carpetaOrigen').on('change', function() {
        var origen = $(this).val();
        $('#carpetaDestino option').prop('disabled', false);
        $('#carpetaDestino option[value="' + origen + '"]').prop('disabled', true);
        
        // Si el destino seleccionado es igual al origen, resetear el destino
        if ($('#carpetaDestino').val() === origen) {
            $('#carpetaDestino').val('');
        }
    });
});
</script>