<?php
// Obtener información del archivo a descargar
$archivo = isset($_GET['archivo']) ? $_GET['archivo'] : '';
$rutaBase = realpath(__DIR__ . '/../../../Almacenamiento');
$rutaCompleta = realpath($rutaBase . DIRECTORY_SEPARATOR . ltrim($archivo, '/\\'));

// Verificar que el archivo existe y está dentro del directorio de almacenamiento
if (!$rutaCompleta || !file_exists($rutaCompleta) || !is_file($rutaCompleta) || strpos($rutaCompleta, $rutaBase) !== 0) {
    echo '<div class="alert alert-danger">El archivo solicitado no existe o no se puede acceder a él.</div>';
    echo '<a href="index.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Volver</a>';
    return;
}

// Obtener información del archivo
$nombreArchivo = basename($rutaCompleta);
$tamanoArchivo = filesize($rutaCompleta);
$tamanoFormateado = formatearTamano($tamanoArchivo);
$extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

// Función para formatear el tamaño del archivo
function formatearTamano($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($unidades) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $unidades[$i];
}

// Determinar el icono según la extensión
$iconoClase = 'fa-file';
$extensionesDocumento = ['doc', 'docx', 'odt', 'rtf', 'txt', 'pdf'];
$extensionesHoja = ['xls', 'xlsx', 'ods', 'csv'];
$extensionesImagen = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
$extensionesVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
$extensionesAudio = ['mp3', 'wav', 'ogg', 'flac', 'aac'];

if (in_array(strtolower($extension), $extensionesDocumento)) {
    $iconoClase = 'fa-file-alt';
} elseif (in_array(strtolower($extension), $extensionesHoja)) {
    $iconoClase = 'fa-file-excel';
} elseif (in_array(strtolower($extension), $extensionesImagen)) {
    $iconoClase = 'fa-file-image';
} elseif (in_array(strtolower($extension), $extensionesVideo)) {
    $iconoClase = 'fa-file-video';
} elseif (in_array(strtolower($extension), $extensionesAudio)) {
    $iconoClase = 'fa-file-audio';
} elseif (strtolower($extension) === 'pdf') {
    $iconoClase = 'fa-file-pdf';
}
?>

<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-download"></i> Descargar Archivo</h4>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="fas <?php echo $iconoClase; ?> fa-5x text-primary mb-3"></i>
                <h5 class="mb-3"><?php echo htmlspecialchars($nombreArchivo); ?></h5>
                <p class="text-muted mb-1">Tamaño: <?php echo $tamanoFormateado; ?></p>
                <p class="text-muted">Tipo: <?php echo strtoupper($extension); ?></p>
            </div>
            
            <!-- Barra de progreso (inicialmente oculta) -->
            <div class="progress mb-4 d-none" id="progressBar">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php<?php echo isset($_GET['carpeta']) ? '?carpeta=' . urlencode($_GET['carpeta']) : ''; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="funciones/descargar.php?archivo=<?php echo urlencode($archivo); ?>" class="btn btn-primary" id="btnDescargar">
                    <i class="fas fa-download"></i> Iniciar Descarga
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Iniciar descarga automáticamente después de 1 segundo
    setTimeout(function() {
        iniciarDescarga();
    }, 1000);
    
    // Manejar clic en el botón de descarga
    $('#btnDescargar').on('click', function(e) {
        e.preventDefault();
        iniciarDescarga();
    });
    
    // Función para iniciar la descarga y mostrar la barra de progreso
    function iniciarDescarga() {
        // Mostrar la barra de progreso
        $('#progressBar').removeClass('d-none');
        
        // Deshabilitar el botón de descarga
        $('#btnDescargar').addClass('disabled').prop('disabled', true);
        $('#btnDescargar').html('<i class="fas fa-spinner fa-spin"></i> Descargando...');
        
        // Simular progreso de descarga
        var progress = 0;
        var interval = setInterval(function() {
            progress += Math.floor(Math.random() * 10) + 1;
            if (progress > 100) progress = 100;
            
            $('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
            
            if (progress >= 100) {
                clearInterval(interval);
                
                // Redirigir a la descarga real
                window.location.href = 'funciones/descargar.php?archivo=<?php echo urlencode($archivo); ?>';
                
                // Restaurar el botón después de 2 segundos
                setTimeout(function() {
                    $('#btnDescargar').removeClass('disabled').prop('disabled', false);
                    $('#btnDescargar').html('<i class="fas fa-download"></i> Descargar de nuevo');
                }, 2000);
            }
        }, 200);
    }
});
</script>
