<?php
/**
 * Controlador de búsqueda para el Sistema NAS
 * Gestiona las operaciones de búsqueda y procesamiento de resultados
 */

/**
 * Procesa una solicitud de búsqueda y devuelve los resultados formateados
 * @param string $termino Término de búsqueda
 * @param string $directorio Directorio base donde buscar
 * @param array $parametros Parámetros adicionales de búsqueda
 * @return array Resultados de búsqueda procesados
 */
function procesarBusqueda($termino, $directorio, $parametros = []) {
    // Configurar opciones de búsqueda
    $opciones = [];
    
    // Procesar parámetros de filtrado
    if (isset($parametros['tipo'])) {
        $tipo = $parametros['tipo'];
        if ($tipo === 'carpetas') {
            $opciones['solo_carpetas'] = true;
        } elseif ($tipo === 'archivos') {
            $opciones['solo_archivos'] = true;
        } elseif (in_array($tipo, ['documentos', 'imagenes', 'videos', 'audio', 'comprimidos'])) {
            $opciones['solo_archivos'] = true;
            
            // Definir extensiones por tipo
            $extensionesPorTipo = [
                'documentos' => ['txt', 'doc', 'docx', 'pdf', 'rtf', 'odt', 'html', 'htm', 'md'],
                'imagenes' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'tiff'],
                'videos' => ['mp4', 'avi', 'mov', 'wmv', 'mkv', 'flv', 'webm'],
                'audio' => ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'],
                'comprimidos' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2']
            ];
            
            if (isset($extensionesPorTipo[$tipo])) {
                $opciones['extensiones'] = $extensionesPorTipo[$tipo];
            }
        }
    }
    
    // Configurar búsqueda en contenido
    if (isset($parametros['contenido']) && $parametros['contenido'] === 'true') {
        $opciones['contenido'] = true;
    }
    
    // Configurar límite de resultados
    if (isset($parametros['limite']) && is_numeric($parametros['limite'])) {
        $opciones['limite'] = intval($parametros['limite']);
    }
    
    // Realizar la búsqueda
    $resultados = buscarArchivos($directorio, $termino, $opciones);
    
    // Registrar la búsqueda en el historial si está habilitado
    if (isset($parametros['guardar_historial']) && $parametros['guardar_historial'] === 'true') {
        guardarHistorialBusqueda($termino, count($resultados));
    }
    
    return $resultados;
}

/**
 * Guarda una búsqueda en el historial
 * @param string $termino Término buscado
 * @param int $resultados Número de resultados encontrados
 * @return bool Éxito de la operación
 */
function guardarHistorialBusqueda($termino, $resultados) {
    // Verificar que exista la carpeta de logs
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/busquedas.log';
    $fecha = date('Y-m-d H:i:s');
    $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'invitado';
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Formatear entrada de log
    $logEntry = "[$fecha] Usuario: $usuario, IP: $ip, Búsqueda: '$termino', Resultados: $resultados\n";
    
    // Escribir en el archivo de log
    return file_put_contents($logFile, $logEntry, FILE_APPEND) !== false;
}

/**
 * Obtiene las búsquedas recientes (para sugerencias)
 * @param int $limite Número máximo de búsquedas a devolver
 * @return array Lista de búsquedas recientes
 */
function obtenerBusquedasRecientes($limite = 5) {
    $logFile = __DIR__ . '/../logs/busquedas.log';
    $busquedas = [];
    
    if (file_exists($logFile)) {
        $lineas = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lineas = array_reverse($lineas); // Más recientes primero
        
        $patron = '/Búsqueda: \'([^\']+)\'/'; // Extraer el término de búsqueda
        $contador = 0;
        
        foreach ($lineas as $linea) {
            if (preg_match($patron, $linea, $coincidencias) && $contador < $limite) {
                $termino = $coincidencias[1];
                if (!in_array($termino, $busquedas)) {
                    $busquedas[] = $termino;
                    $contador++;
                }
            }
        }
    }
    
    return $busquedas;
}

/**
 * Busca carpetas en un directorio específico (función de compatibilidad)
 * @param string $dir Directorio donde buscar
 * @return array Lista de carpetas encontradas
 */
function buscarCarpetas($dir) {
    $dir = rtrim($dir, '/') . '/'; 
    $result = array();
    $items = scandir($dir);
    foreach ($items as $item) {
        if (!in_array($item, array(".", ".."))) {
            $path = $dir . $item;
            $result[] = array(
                'name' => $item,
                'is_dir' => is_dir($path),
                'path' => $path
            );
        }
    }
    return $result;
}
?>