<?php
/**
 * Versión AJAX del explorador para actualizaciones en tiempo real
 * Sistema NAS v3
 */

/**
 * Función para calcular el tamaño de una carpeta
 */
function getFolderSize($path) {
    $total_size = 0;
    $file_count = 0;
    $dir_count = 0;
    
    if (!is_dir($path)) {
        return array('size' => 0, 'files' => 0, 'dirs' => 0);
    }
    
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filepath = $path . '/' . $file;
            
            if (is_dir($filepath)) {
                $dir_count++;
                $subdir_info = getFolderSize($filepath);
                $total_size += $subdir_info['size'];
                $file_count += $subdir_info['files'];
                $dir_count += $subdir_info['dirs'];
            } else {
                $total_size += filesize($filepath);
                $file_count++;
            }
        }
    }
    
    return array('size' => $total_size, 'files' => $file_count, 'dirs' => $dir_count);
}

/**
 * Función para formatear el tamaño en bytes a un formato legible
 */
function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Definir la ruta base de Almacenamiento - AJUSTA ESTA RUTA
$rutaBase = __DIR__ . '../../../../Almacenamiento';

// Obtener la carpeta actual desde la URL
$carpetaActual = isset($_GET['carpeta']) ? $_GET['carpeta'] : '';
$rutaActual = $rutaBase;

// Si hay una carpeta específica, construir la ruta completa
if (!empty($carpetaActual)) {
    $carpetaActual = str_replace('..', '', $carpetaActual);
    $rutaActual = $rutaBase . '/' . $carpetaActual;
}

// Verificar que la ruta exista y sea un directorio
if (!is_dir($rutaActual)) {
    echo '<div class="alert alert-danger">La carpeta solicitada no existe.</div>';
    exit;
}

// Obtener todas las carpetas en este directorio
$carpetas = array_filter(scandir($rutaActual), function($item) use ($rutaActual) {
    return $item != '.' && $item != '..' && is_dir($rutaActual . '/' . $item);
});

// Obtener todos los archivos en este directorio
$archivos = array_filter(scandir($rutaActual), function($item) use ($rutaActual) {
    return $item != '.' && $item != '..' && !is_dir($rutaActual . '/' . $item);
});

// Ordenar carpetas y archivos alfabéticamente
sort($carpetas);
sort($archivos);

// Construir la ruta relativa para las URLs
$rutaRelativa = empty($carpetaActual) ? '' : $carpetaActual;

// Generar el HTML
// Primero mostrar todas las carpetas
foreach ($carpetas as $carpeta) {
    $rutaCarpetaActual = $rutaRelativa . '/' . $carpeta;
    $rutaCarpetaActual = ltrim($rutaCarpetaActual, '/');
    
    if (empty($rutaCarpetaActual)) {
        $rutaCarpetaActual = $carpeta;
    }
    
    $carpetaPath = $rutaActual . '/' . $carpeta;
    $folderInfo = getFolderSize($carpetaPath);
    $folderSize = formatSize($folderInfo['size']);
    $fileCount = $folderInfo['files'];
    $dirCount = $folderInfo['dirs'];
    $totalItems = $fileCount + $dirCount;
    $fechaCreacion = date('d/m/Y H:i', filemtime($carpetaPath));
    
    echo '<div class="item carpeta" data-ruta="' . htmlspecialchars($rutaCarpetaActual) . '" data-nombre="' . htmlspecialchars($carpeta) . '">';
    echo '<div class="item-icon"><i class="fas fa-folder"></i></div>';
    echo '<div class="item-name">' . htmlspecialchars($carpeta) . '</div>';
    echo '<div class="item-info">';
    echo '<div class="item-size">' . $folderSize . '</div>';
    echo '<div class="item-count">' . $totalItems . ' elementos</div>';
    echo '<div class="item-date">' . $fechaCreacion . '</div>';
    echo '</div>';
    echo '<a href="index.php?modulo=explorador_windows&carpeta=' . urlencode($rutaCarpetaActual) . '" class="d-none">' . htmlspecialchars($carpeta) . '</a>';
    
    echo '<div class="item-actions">';
    $idBoton = 'btn_editar_' . str_replace('/', '_', $rutaCarpetaActual);
    $formId = 'form_editar_' . str_replace('/', '_', $rutaCarpetaActual);
    echo '<button type="button" id="' . $idBoton . '" class="btn btn-sm btn-outline-primary btn-editar-carpeta" data-ruta="' . htmlspecialchars($rutaCarpetaActual) . '" data-nombre="' . htmlspecialchars($carpeta) . '" data-tipo="carpeta" data-form-id="' . $formId . '" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Editar">';
    echo '<i class="fas fa-edit"></i>';
    echo '</button>';
    
    echo '<button type="button" class="btn btn-sm btn-danger btn-eliminar" data-ruta="' . htmlspecialchars($rutaCarpetaActual) . '" data-nombre="' . htmlspecialchars($carpeta) . '" data-tipo="carpeta" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Eliminar" onclick="confirmarEliminar(\'' . htmlspecialchars($rutaCarpetaActual) . '\', \'' . htmlspecialchars($carpeta) . '\', \'carpeta\')">';
    echo '<i class="fas fa-trash"></i>';
    echo '</button>';
    
    echo '</div>';
    echo '</div>';
}

// Luego mostrar todos los archivos
foreach ($archivos as $archivo) {
    $rutaArchivoActual = $rutaRelativa . '/' . $archivo;
    $rutaArchivoActual = ltrim($rutaArchivoActual, '/');
    $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    
    $archivoPath = $rutaActual . '/' . $archivo;
    $fileSize = filesize($archivoPath);
    $formattedSize = formatSize($fileSize);
    $fechaCreacion = date('d/m/Y H:i', filemtime($archivoPath));
    
    $iconoArchivo = 'fa-file';
    if (in_array($extension, ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md'])) {
        $iconoArchivo = 'fa-file-alt';
    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'])) {
        $iconoArchivo = 'fa-file-image';
    } elseif (in_array($extension, ['pdf'])) {
        $iconoArchivo = 'fa-file-pdf';
    } elseif (in_array($extension, ['doc', 'docx'])) {
        $iconoArchivo = 'fa-file-word';
    } elseif (in_array($extension, ['xls', 'xlsx'])) {
        $iconoArchivo = 'fa-file-excel';
    } elseif (in_array($extension, ['ppt', 'pptx'])) {
        $iconoArchivo = 'fa-file-powerpoint';
    } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
        $iconoArchivo = 'fa-file-archive';
    } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
        $iconoArchivo = 'fa-file-audio';
    } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'wmv'])) {
        $iconoArchivo = 'fa-file-video';
    }
    
    echo '<div class="item archivo" data-ruta="' . htmlspecialchars($rutaArchivoActual) . '" data-nombre="' . htmlspecialchars($archivo) . '">';
    echo '<div class="item-icon"><i class="fas ' . $iconoArchivo . '"></i></div>';
    echo '<div class="item-name">' . htmlspecialchars($archivo) . '</div>';
    echo '<div class="item-info">';
    echo '<div class="item-size"><i class="fas fa-weight-hanging"></i> ' . $formattedSize . '</div>';
    echo '<div class="item-date"><i class="fas fa-calendar-alt"></i> ' . $fechaCreacion . '</div>';
    echo '</div>';
    echo '<a href="index.php?archivo=' . urlencode($rutaArchivoActual) . '" class="d-none">' . htmlspecialchars($archivo) . '</a>';
    
    echo '<div class="item-actions">';
    echo '<button type="button" class="btn btn-sm btn-outline-primary btn-editar-archivo renombrar-archivo-btn" data-ruta="' . htmlspecialchars($rutaArchivoActual) . '" data-nombre="' . htmlspecialchars($archivo) . '" data-tipo="archivo" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Renombrar" onclick="abrirModalRenombrarArchivo(\'' . htmlspecialchars($rutaArchivoActual) . '\', \'' . htmlspecialchars($archivo) . '\')">';
    echo '<i class="fas fa-edit"></i>';
    echo '</button>';
    
    echo '<button type="button" class="btn btn-sm btn-danger btn-eliminar" data-ruta="' . htmlspecialchars($rutaArchivoActual) . '" data-nombre="' . htmlspecialchars($archivo) . '" data-tipo="archivo" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Eliminar" onclick="confirmarEliminar(\'' . htmlspecialchars($rutaArchivoActual) . '\', \'' . htmlspecialchars($archivo) . '\', \'archivo\')">';
    echo '<i class="fas fa-trash"></i>';
    echo '</button>';
    
    $extensionesTexto = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'doc', 'docx', 'rtf', 'odt', 'tex'];
    $esArchivoTexto = in_array($extension, $extensionesTexto);
    if ($esArchivoTexto) {
        echo '<a href="index.php?modulo=editar_archivo&archivo=' . urlencode($rutaArchivoActual) . '" class="btn btn-sm btn-outline-info" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Editar contenido">';
        echo '<i class="fas fa-file-alt"></i>';
        echo '</a>';
    }
    
    echo '</div>';
    echo '</div>';
}
?>