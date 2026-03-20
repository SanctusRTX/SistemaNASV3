<?php
/**
 * Funciones de búsqueda para el Sistema NAS
 * Este archivo contiene las funciones necesarias para buscar archivos y carpetas
 * con opciones avanzadas y optimizadas para mejor rendimiento
 */

/**
 * Busca archivos y carpetas que coincidan con los criterios de búsqueda
 * @param string $directorio Directorio base donde buscar
 * @param string $termino Término de búsqueda
 * @param array $opciones Opciones adicionales de búsqueda
 * @return array Array con los resultados de la búsqueda
 */
function buscarArchivos($directorio, $termino, $opciones = []) {
    $resultados = array();
    $contador = 0;
    $limite = isset($opciones['limite']) ? $opciones['limite'] : 500; // Límite de resultados para evitar sobrecarga
    $buscarContenido = isset($opciones['contenido']) ? $opciones['contenido'] : false; // Buscar dentro del contenido de archivos
    $extensiones = isset($opciones['extensiones']) ? $opciones['extensiones'] : []; // Filtrar por extensiones
    $soloArchivos = isset($opciones['solo_archivos']) ? $opciones['solo_archivos'] : false; // Buscar solo archivos
    $soloCarpetas = isset($opciones['solo_carpetas']) ? $opciones['solo_carpetas'] : false; // Buscar solo carpetas
    $profundidadMaxima = isset($opciones['profundidad']) ? $opciones['profundidad'] : -1; // Profundidad máxima de búsqueda (-1 = sin límite)
    
    // Verificar que el directorio exista
    if (!is_dir($directorio)) {
        return $resultados;
    }
    
    // Preparar el término de búsqueda para comparaciones
    $termino = trim(strtolower($termino));
    
    // Si el término está vacío, devolver array vacío
    if (empty($termino)) {
        return $resultados;
    }
    
    // Crear un iterador recursivo con opciones de profundidad
    $directoryIterator = new RecursiveDirectoryIterator($directorio, RecursiveDirectoryIterator::SKIP_DOTS);
    
    if ($profundidadMaxima >= 0) {
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);
        $iterator->setMaxDepth($profundidadMaxima);
    } else {
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);
    }
    
    // Lista de extensiones de texto para buscar contenido
    $extensionesTexto = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log'];
    
    // Buscar en todos los archivos y carpetas
    foreach ($iterator as $item) {
        // Verificar si hemos alcanzado el límite de resultados
        if ($contador >= $limite) {
            break;
        }
        
        $esDirectorio = $item->isDir();
        $nombre = $item->getFilename();
        $ruta = $item->getPathname();
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        
        // Aplicar filtros de tipo (archivo/carpeta)
        if (($soloArchivos && $esDirectorio) || ($soloCarpetas && !$esDirectorio)) {
            continue;
        }
        
        // Aplicar filtro de extensiones si está configurado
        if (!empty($extensiones) && !$esDirectorio && !in_array($extension, $extensiones)) {
            continue;
        }
        
        $coincide = false;
        
        // Buscar en el nombre del archivo/carpeta
        if (stripos($nombre, $termino) !== false) {
            $coincide = true;
        }
        
        // Buscar en el contenido del archivo si está habilitado
        if (!$coincide && $buscarContenido && !$esDirectorio && in_array($extension, $extensionesTexto)) {
            // Limitar la búsqueda a archivos pequeños (menos de 5MB)
            if (filesize($ruta) < 5 * 1024 * 1024) {
                try {
                    $contenido = file_get_contents($ruta);
                    if (stripos($contenido, $termino) !== false) {
                        $coincide = true;
                    }
                } catch (Exception $e) {
                    // Ignorar errores al leer archivos
                }
            }
        }
        
        // Si hay coincidencia, agregar a los resultados
        if ($coincide) {
            $resultados[] = array(
                'name' => $nombre,
                'path' => $ruta,
                'is_dir' => $esDirectorio,
                'size' => $esDirectorio ? 0 : filesize($ruta),
                'modified' => filemtime($ruta),
                'extension' => $esDirectorio ? '' : $extension
            );
            $contador++;
        }
    }
    
    return $resultados;
}

/**
 * Busca carpetas y archivos dentro de un directorio específico con información detallada
 * @param string $directorio Directorio donde buscar
 * @param array $opciones Opciones adicionales
 * @return array Array con las carpetas y archivos encontrados incluyendo rutas completas
 */
function listarDirectorioDetallado($directorio, $opciones = []) {
    $items = array();
    $ordenarPor = isset($opciones['ordenar']) ? $opciones['ordenar'] : 'nombre_asc';
    $filtroTipo = isset($opciones['tipo']) ? $opciones['tipo'] : 'todos';
    
    // Verificar que el directorio exista
    if (!is_dir($directorio)) {
        return $items;
    }
    
    // Obtener todos los archivos y carpetas en el directorio
    $archivos = scandir($directorio);
    
    foreach ($archivos as $archivo) {
        // Ignorar . y ..
        if ($archivo != "." && $archivo != "..") {
            $ruta = $directorio . DIRECTORY_SEPARATOR . $archivo;
            $esDirectorio = is_dir($ruta);
            $extension = $esDirectorio ? '' : strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            
            // Aplicar filtro de tipo si está configurado
            if ($filtroTipo !== 'todos') {
                if ($filtroTipo === 'carpetas' && !$esDirectorio) {
                    continue;
                } elseif ($filtroTipo === 'archivos' && $esDirectorio) {
                    continue;
                } elseif (!$esDirectorio) {
                    // Filtrar por categoría de archivo
                    $tiposArchivo = [
                        'documentos' => ['txt', 'doc', 'docx', 'pdf', 'rtf', 'odt'],
                        'imagenes' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'],
                        'videos' => ['mp4', 'avi', 'mov', 'wmv', 'mkv'],
                        'audio' => ['mp3', 'wav', 'ogg', 'flac'],
                        'comprimidos' => ['zip', 'rar', '7z', 'tar', 'gz']
                    ];
                    
                    if (isset($tiposArchivo[$filtroTipo]) && !in_array($extension, $tiposArchivo[$filtroTipo])) {
                        continue;
                    }
                }
            }
            
            $items[] = array(
                'name' => $archivo,
                'path' => $ruta,
                'is_dir' => $esDirectorio,
                'size' => $esDirectorio ? 0 : filesize($ruta),
                'modified' => filemtime($ruta),
                'extension' => $extension
            );
        }
    }
    
    // Ordenar resultados
    usort($items, function($a, $b) use ($ordenarPor) {
        switch ($ordenarPor) {
            case 'nombre_asc':
                return strcasecmp($a['name'], $b['name']);
            case 'nombre_desc':
                return strcasecmp($b['name'], $a['name']);
            case 'tipo_asc':
                $aIsDir = $a['is_dir'] ? 0 : 1;
                $bIsDir = $b['is_dir'] ? 0 : 1;
                if ($aIsDir === $bIsDir) {
                    return strcasecmp($a['name'], $b['name']);
                }
                return $aIsDir - $bIsDir;
            case 'tipo_desc':
                $aIsDir = $a['is_dir'] ? 1 : 0;
                $bIsDir = $b['is_dir'] ? 1 : 0;
                if ($aIsDir === $bIsDir) {
                    return strcasecmp($a['name'], $b['name']);
                }
                return $aIsDir - $bIsDir;
            case 'fecha_asc':
                return $a['modified'] - $b['modified'];
            case 'fecha_desc':
                return $b['modified'] - $a['modified'];
            case 'tamanio_asc':
                return $a['size'] - $b['size'];
            case 'tamanio_desc':
                return $b['size'] - $a['size'];
            default:
                return strcasecmp($a['name'], $b['name']);
        }
    });
    
    return $items;
}

/**
 * Busca archivos por contenido (texto dentro del archivo)
 * @param string $directorio Directorio base donde buscar
 * @param string $termino Término de búsqueda
 * @param array $extensiones Lista de extensiones a buscar
 * @return array Array con los resultados de la búsqueda
 */
function buscarPorContenido($directorio, $termino, $extensiones = []) {
    $opciones = [
        'contenido' => true,
        'solo_archivos' => true,
        'extensiones' => !empty($extensiones) ? $extensiones : ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log']
    ];
    
    return buscarArchivos($directorio, $termino, $opciones);
}
?>
