<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-upload"></i> Subir Archivo</h4>
        </div>
        <div class="card-body">
            <form action="javascript:void(0);" id="uploadForm">
                <!-- Selección de archivo -->
                <div class="form-group mb-4">
                    <label for="archivo" class="form-label"><i class="fas fa-file"></i> Seleccionar archivo:</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="archivo" name="archivo" required>
                        <label class="custom-file-label" for="archivo">Seleccionar archivo...</label>
                    </div>
                    <small class="form-text text-muted mt-2">Sin límite de tamaño. Puedes subir archivos de cualquier peso.</small>
                </div>
                
                <!-- Información de progreso -->
                <div class="upload-info d-none mb-3" id="uploadInfo">
                    <div class="d-flex justify-content-between mb-2">
                        <span id="fileInfo">Preparando archivo...</span>
                        <span id="percentageInfo">0%</span>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             id="progressBar" role="progressbar" style="width: 0%" 
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="small text-muted" id="chunkInfo">Fragmento: 0/0</div>
                </div>
                
                <!-- Selección de carpeta destino -->
                <div class="form-group mb-3">
                    <label for="carpeta_destino" class="form-label"><i class="fas fa-folder"></i> Carpeta de destino:</label>
                    <select class="form-control" id="carpeta_destino" name="carpeta_destino" required>
                        <option value="Almacenamiento">Almacenamiento (Raíz)</option>
                        <?php
                        $carpetas = listAllDirectories(__DIR__ . '/../../../Almacenamiento');
                        $rutaRaiz = realpath(__DIR__ . '/../../../Almacenamiento');
                        
                        foreach ($carpetas as $carpeta) {
                            $rutaAbsoluta = realpath($carpeta['name']);
                            $relativa = str_replace($rutaRaiz, '', $rutaAbsoluta); // Quita la parte de la raíz
                            $relativa = ltrim(str_replace(['\\', '/'], '/', $relativa), '/'); // Normaliza y limpia
                        
                            $indent = str_repeat("&nbsp;", $carpeta['level'] * 4);
                            $displayName = $indent . ($carpeta['level'] > 0 ? '└─ ' : '') . htmlspecialchars(basename($carpeta['name']));
                            echo '<option value="' . htmlspecialchars($relativa) . '">' . $displayName . '</option>';
                        }
                        
                        ?>
                    </select>
                    <small class="form-text text-muted">Selecciona la carpeta donde deseas guardar el archivo.</small>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary" data-bs-toggle="tooltip" title="Volver al explorador de archivos"><i class="fas fa-arrow-left"></i> Volver</a>
                    <button type="submit" class="btn btn-success" id="submitBtn" data-bs-toggle="tooltip" title="Subir el archivo seleccionado">
                        <i class="fas fa-upload"></i> Subir Archivo
                    </button>
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

<!-- Incluir el script de carga por fragmentos -->
<script src="/Sistema-NASv3/Public/Js/chunked-uploader.js"></script>

<!-- JavaScript para mejorar la experiencia de usuario -->
<script>
$(document).ready(function() {
    // Actualizar el nombre del archivo seleccionado
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Manejar la subida de archivos por fragmentos
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('archivo');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Por favor, selecciona un archivo para subir.');
            return false;
        }
        
        const file = fileInput.files[0];
        
        // Mostrar información de la subida
        $('#uploadInfo').removeClass('d-none');
        $('#fileInfo').text(`Subiendo: ${file.name} (${formatFileSize(file.size)})`);
        $('#submitBtn').prop('disabled', true);
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
        
        // Crear instancia del cargador de fragmentos
        const uploader = new ChunkedUploader(file, {
            // Tamaño de fragmento: 5MB para archivos grandes
            chunkSize: file.size > 1024 * 1024 * 1000 ? 5 * 1024 * 1024 : 2 * 1024 * 1024,
            uploadUrl: '/Sistema-NASv3/Src/funciones/upload_chunk.php',
            onProgress: function(progress) {
                // Actualizar la barra de progreso
                const percentage = progress.percentage;
                $('#progressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);
                $('#percentageInfo').text(percentage + '%');
                $('#chunkInfo').text(`Fragmento: ${progress.currentChunk + 1}/${progress.totalChunks} - ${formatFileSize(progress.loaded)} de ${formatFileSize(progress.total)}`);
            },
            onComplete: function(response) {
                // Subida completada
                $('#fileInfo').text('¡Subida completada!');
                $('#percentageInfo').text('100%');
                $('#progressBar').css('width', '100%').attr('aria-valuenow', 100);
                $('#submitBtn').html('<i class="fas fa-check"></i> Completado');
                
                // Mostrar mensaje de éxito
                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-check-circle"></i> ${response.message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('#uploadForm').after(alertHtml);
                
                // Redireccionar después de 2 segundos
                setTimeout(function() {
                    window.location.href = response.redirectUrl;
                }, 2000);
            },
            onError: function(error) {
                // Error en la subida
                $('#uploadInfo').addClass('alert alert-danger');
                $('#fileInfo').text('Error en la subida: ' + error.message);
                $('#submitBtn').prop('disabled', false);
                $('#submitBtn').html('<i class="fas fa-upload"></i> Reintentar');
                
                // Mostrar mensaje de error
                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> Error: ${error.message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('#uploadForm').after(alertHtml);
            }
        });
        
        // Iniciar la subida
        uploader.start();
        
        return false;
    });
    
    // Función para formatear el tamaño del archivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
