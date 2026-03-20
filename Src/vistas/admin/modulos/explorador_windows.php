<?php
/**
 * Vista de explorador estilo Windows
 * Sistema NAS v3
 * 
 * Implementa una vista en cuadrícula y en lista similar al explorador de Windows
 * Con información de tamaño, cantidad de archivos y fecha de creación
 */

/**
 * Función para calcular el tamaño de una carpeta
 * @param string $path Ruta de la carpeta
 * @return array Array con el tamaño total y la cantidad de archivos
 */
function getFolderSize($path) {
    $total_size = 0;
    $file_count = 0;
    $dir_count = 0;
    
    // Verificar si la ruta existe y es un directorio
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
 * @param int $bytes Tamaño en bytes
 * @return string Tamaño formateado
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

// Definir la ruta base de Almacenamiento
$rutaBase = __DIR__ . '/../../../Almacenamiento';

// Obtener la carpeta actual desde la URL
$carpetaActual = isset($_GET['carpeta']) ? $_GET['carpeta'] : '';
$rutaActual = $rutaBase;

// Si hay una carpeta específica, construir la ruta completa
if (!empty($carpetaActual)) {
    // Limpiar la ruta para evitar ataques de directorio transversal
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
?>

<div class="container">
    <h2>Explorador de Archivos</h2>
    
    <!-- Controles de vista (cuadrícula/lista) -->
    <div class="vista-controles">
        <button id="vista-cuadricula-btn" class="vista-btn active"><i class="fas fa-th"></i> Cuadrícula</button>
        <button id="vista-lista-btn" class="vista-btn"><i class="fas fa-list"></i> Lista</button>
    </div>
    
    <!-- Contenedor principal para la vista de explorador -->
    <div id="vista-explorador" class="vista-cuadricula">
        <?php
        // Primero mostrar todas las carpetas
        foreach ($carpetas as $carpeta) {
            $rutaCarpetaActual = $rutaRelativa . '/' . $carpeta;
            $rutaCarpetaActual = ltrim($rutaCarpetaActual, '/');
            
            // Asegurarse de que la ruta no esté vacía
            if (empty($rutaCarpetaActual)) {
                $rutaCarpetaActual = $carpeta; // Si está vacía, usar al menos el nombre de la carpeta
            }
            
            // Obtener información de la carpeta
            $carpetaPath = $rutaActual . '/' . $carpeta;
            $folderInfo = getFolderSize($carpetaPath);
            $folderSize = formatSize($folderInfo['size']);
            $fileCount = $folderInfo['files'];
            $dirCount = $folderInfo['dirs'];
            $totalItems = $fileCount + $dirCount;
            
            // Obtener fecha de creación
            $fechaCreacion = date('d/m/Y H:i', filemtime($carpetaPath));
            
            // Crear elemento de carpeta
            echo '<div class="item carpeta" data-ruta="' . htmlspecialchars($rutaCarpetaActual) . '" data-nombre="' . htmlspecialchars($carpeta) . '">';
            echo '<div class="item-icon"><i class="fas fa-folder"></i></div>';
            echo '<div class="item-name">' . htmlspecialchars($carpeta) . '</div>';
            echo '<div class="item-info">';
            echo '<div class="item-size">' . $folderSize . '</div>';
            echo '<div class="item-count">' . $totalItems . ' elementos</div>';
            echo '<div class="item-date">' . $fechaCreacion . '</div>';
            echo '</div>';
            echo '<a href="index.php?modulo=explorador_windows&carpeta=' . urlencode($rutaCarpetaActual) . '" class="d-none">' . htmlspecialchars($carpeta) . '</a>';
            
            // Botones de acción
            echo '<div class="item-actions">';
            
            // Botón para editar carpeta
            $idBoton = 'btn_editar_' . str_replace('/', '_', $rutaCarpetaActual);
            $formId = 'form_editar_' . str_replace('/', '_', $rutaCarpetaActual);
            echo '<button type="button" id="' . $idBoton . '" class="btn btn-sm btn-outline-primary btn-editar-carpeta" data-ruta="' . htmlspecialchars($rutaCarpetaActual) . '" data-nombre="' . htmlspecialchars($carpeta) . '" data-tipo="carpeta" data-form-id="' . $formId . '" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Editar">';
            echo '<i class="fas fa-edit"></i>';
            echo '</button>';
            
            // Botón para eliminar carpeta
            echo '<button type="button" class="btn btn-sm btn-danger btn-eliminar" data-ruta="' . htmlspecialchars($rutaCarpetaActual) . '" data-nombre="' . htmlspecialchars($carpeta) . '" data-tipo="carpeta" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Eliminar" onclick="confirmarEliminar(\'' . htmlspecialchars($rutaCarpetaActual) . '\', \'' . htmlspecialchars($carpeta) . '\', \'carpeta\')">';
            echo '<i class="fas fa-trash"></i>';
            echo '</button>';
            
            echo '</div>'; // Fin de item-actions
            echo '</div>'; // Fin de item carpeta
        }
        
        // Luego mostrar todos los archivos
        foreach ($archivos as $archivo) {
            $rutaArchivoActual = $rutaRelativa . '/' . $archivo;
            $rutaArchivoActual = ltrim($rutaArchivoActual, '/');
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            
            // Obtener información del archivo
            $archivoPath = $rutaActual . '/' . $archivo;
            $fileSize = filesize($archivoPath);
            $formattedSize = formatSize($fileSize);
            
            // Obtener fecha de creación/modificación
            $fechaCreacion = date('d/m/Y H:i', filemtime($archivoPath));
            
            // Determinar el icono según la extensión
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
            
            // Crear elemento de archivo
            echo '<div class="item archivo" data-ruta="' . htmlspecialchars($rutaArchivoActual) . '" data-nombre="' . htmlspecialchars($archivo) . '">';
            echo '<div class="item-icon"><i class="fas ' . $iconoArchivo . '"></i></div>';
            echo '<div class="item-name">' . htmlspecialchars($archivo) . '</div>';
            echo '<div class="item-info">';
            echo '<div class="item-size"><i class="fas fa-weight-hanging"></i> ' . $formattedSize . '</div>';
            echo '<div class="item-date"><i class="fas fa-calendar-alt"></i> ' . $fechaCreacion . '</div>';
            echo '</div>';
            echo '<a href="index.php?archivo=' . urlencode($rutaArchivoActual) . '" class="d-none">' . htmlspecialchars($archivo) . '</a>';
            
            // Botones de acción
            echo '<div class="item-actions">';
            
            // Botón para renombrar archivo
            echo '<button type="button" class="btn btn-sm btn-outline-primary btn-editar-archivo renombrar-archivo-btn" data-ruta="' . htmlspecialchars($rutaArchivoActual) . '" data-nombre="' . htmlspecialchars($archivo) . '" data-tipo="archivo" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Renombrar" onclick="abrirModalRenombrarArchivo(\'' . htmlspecialchars($rutaArchivoActual) . '\', \'' . htmlspecialchars($archivo) . '\')">';
            echo '<i class="fas fa-edit"></i>';
            echo '</button>';
            
            // Botón para eliminar archivo
            echo '<button type="button" class="btn btn-sm btn-danger btn-eliminar" data-ruta="' . htmlspecialchars($rutaArchivoActual) . '" data-nombre="' . htmlspecialchars($archivo) . '" data-tipo="archivo" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Eliminar" onclick="confirmarEliminar(\'' . htmlspecialchars($rutaArchivoActual) . '\', \'' . htmlspecialchars($archivo) . '\', \'archivo\')">';
            echo '<i class="fas fa-trash"></i>';
            echo '</button>';
            
            // Botón para editar contenido si es archivo de texto
            $extensionesTexto = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'doc', 'docx', 'rtf', 'odt', 'tex'];
            $esArchivoTexto = in_array($extension, $extensionesTexto);
            if ($esArchivoTexto) {
                echo '<a href="index.php?modulo=editar_archivo&archivo=' . urlencode($rutaArchivoActual) . '" class="btn btn-sm btn-outline-info" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Editar contenido">';
                echo '<i class="fas fa-file-alt"></i>';
                echo '</a>';
            }
            
            echo '</div>'; // Fin de item-actions
            echo '</div>'; // Fin de item archivo
        }
        ?>
    </div>
            <!-- Agrega un botón para pausar/reanudar la actualización -->
        <div class="text-right mb-2">
            <button id="toggle-actualizacion" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-pause"></i> Pausar actualización
            </button>
        <span class="ml-2 text-muted" id="ultima-actualizacion"></span>
    <!-- Menú contextual -->
    <div id="context-menu" class="context-menu">
        <!-- El contenido se genera dinámicamente mediante JavaScript -->
        <div class="context-menu-item default-item">
            <i class="fas fa-spinner fa-spin"></i> Cargando opciones...
        </div>
    </div>
    
    <!-- Script para depurar menú contextual -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Explorador cargado. Menú contextual:', document.getElementById('context-menu'));
    });
    </script>
    <script>
// Sistema de actualización en tiempo real
let intervaloActualizacion;
let actualizacionActiva = true;
let carpetaActual = document.getElementById('vista-explorador').getAttribute('data-carpeta-actual') || '';

function cargarExplorador() {
    if (!actualizacionActiva) return;
    
    fetch('vistas/admin/modulos/explorador_windows_ajax.php?carpeta=' + encodeURIComponent(carpetaActual), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('vista-explorador').innerHTML = html;
        document.getElementById('ultima-actualizacion').innerHTML = 'Última actualización: ' + new Date().toLocaleTimeString();
        
        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-tooltip]').tooltip();
        }
    })
    .catch(error => {
        console.error('Error al actualizar:', error);
    });
}

function iniciarActualizacion() {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
    }
    cargarExplorador();
    intervaloActualizacion = setInterval(cargarExplorador, 3000);
}

function detenerActualizacion() {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
        intervaloActualizacion = null;
    }
}

document.getElementById('toggle-actualizacion').addEventListener('click', function() {
    actualizacionActiva = !actualizacionActiva;
    
    if (actualizacionActiva) {
        this.innerHTML = '<i class="fas fa-pause"></i> Pausar actualización';
        this.classList.remove('btn-outline-danger');
        this.classList.add('btn-outline-secondary');
        iniciarActualizacion();
    } else {
        this.innerHTML = '<i class="fas fa-play"></i> Reanudar actualización';
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-outline-danger');
        detenerActualizacion();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    iniciarActualizacion();
});

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        detenerActualizacion();
    } else {
        if (actualizacionActiva) {
            iniciarActualizacion();
        }
    }
});

// Navegación entre carpetas sin recargar
document.addEventListener('click', function(e) {
    const carpeta = e.target.closest('.carpeta');
    if (carpeta) {
        e.preventDefault();
        const nuevaRuta = carpeta.getAttribute('data-ruta');
        if (nuevaRuta) {
            carpetaActual = nuevaRuta;
            document.getElementById('vista-explorador').setAttribute('data-carpeta-actual', nuevaRuta);
            
            const url = new URL(window.location);
            url.searchParams.set('carpeta', nuevaRuta);
            window.history.pushState({}, '', url);
            
            cargarExplorador();
        }
    }
});

window.addEventListener('popstate', function() {
    const urlParams = new URLSearchParams(window.location.search);
    carpetaActual = urlParams.get('carpeta') || '';
    document.getElementById('vista-explorador').setAttribute('data-carpeta-actual', carpetaActual);
    cargarExplorador();
});
</script>
</div>
