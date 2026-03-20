<?php
/**
 * Funciones para la papelera de reciclaje
 * Este archivo contiene las funciones necesarias para gestionar la papelera de reciclaje
 */

// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /Sistema-NASv3/index.php');
    exit;
}

// Definir la ruta base de almacenamiento
$rutaBase = realpath(__DIR__ . '/../Almacenamiento');

// Nueva ubicación de la papelera fuera del directorio de Almacenamiento
$rutaPapelera = realpath(__DIR__ . '/../../Papelera');

// Crear la carpeta de la papelera si no existe
if (!file_exists($rutaPapelera)) {
    mkdir($rutaPapelera, 0755, true);
}

// Registrar la ubicación de la papelera para depuración
$logFile = __DIR__ . '/debug_papelera.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ruta de la papelera: $rutaPapelera\n", FILE_APPEND);

// Crear archivo de log para depuración
$logFile = __DIR__ . '/debug_papelera.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);

// Función para escribir en el log
function escribirLogPapelera($mensaje, $logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $mensaje\n", FILE_APPEND);
}

/**
 * Mueve un archivo o carpeta a la papelera en lugar de eliminarlo
 * 
 * @param string $ruta Ruta absoluta del elemento a mover a la papelera
 * @param string $rutaBase Ruta base del almacenamiento
 * @param string $tipo Tipo de elemento ('archivo' o 'carpeta')
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se movió correctamente, False en caso contrario
 */
function moverAPapelera($ruta, $rutaBase, $tipo, $logFile) {
    escribirLogPapelera("Moviendo a papelera: $ruta", $logFile);
    
    // Verificar que la ruta existe
    if (!file_exists($ruta)) {
        escribirLogPapelera("ERROR: La ruta no existe: $ruta", $logFile);
        return false;
    }
    
    // Convertir a ruta absoluta
    $rutaAbsoluta = realpath($ruta);
    
    // Verificar que la ruta absoluta se obtuvo correctamente
    if (!$rutaAbsoluta) {
        escribirLogPapelera("ERROR: No se pudo obtener la ruta absoluta para: $ruta", $logFile);
        return false;
    }
    
    // Verificar que la ruta está dentro del directorio base
    $rutaBaseAbsoluta = realpath($rutaBase);
    if (strpos($rutaAbsoluta, $rutaBaseAbsoluta) !== 0) {
        escribirLogPapelera("ERROR: La ruta está fuera del directorio base:", $logFile);
        escribirLogPapelera("Ruta a validar: $rutaAbsoluta", $logFile);
        escribirLogPapelera("Directorio base: $rutaBaseAbsoluta", $logFile);
        return false;
    }
    
    // Obtener la ruta relativa al directorio base
    $rutaRelativa = substr($rutaAbsoluta, strlen($rutaBaseAbsoluta) + 1);
    escribirLogPapelera("Ruta relativa: $rutaRelativa", $logFile);
    
    // Obtener la ruta de la papelera (ahora fuera del directorio de Almacenamiento)
    $rutaPapeleraAbsoluta = realpath(__DIR__ . '/../../Papelera');
    escribirLogPapelera("Ruta de la papelera: $rutaPapeleraAbsoluta", $logFile);
    
    // Crear un nombre único para evitar colisiones en la papelera
    $nombreUnico = date('YmdHis') . '_' . str_replace(['/', '\\'], '_', $rutaRelativa);
    $rutaDestino = $rutaPapeleraAbsoluta . DIRECTORY_SEPARATOR . $nombreUnico;
    
    escribirLogPapelera("Ruta destino en papelera: $rutaDestino", $logFile);
    
    // Crear un archivo de metadatos para guardar la información original
    // Verificar que la carpeta de la papelera existe y tiene permisos de escritura
    if (!file_exists($rutaPapeleraAbsoluta)) {
        if (!mkdir($rutaPapeleraAbsoluta, 0755, true)) {
            escribirLogPapelera("ERROR: No se pudo crear la carpeta de la papelera: $rutaPapeleraAbsoluta", $logFile);
            return false;
        }
    } else if (!is_writable($rutaPapeleraAbsoluta)) {
        escribirLogPapelera("ERROR: No hay permisos de escritura en la carpeta de la papelera: $rutaPapeleraAbsoluta", $logFile);
        return false;
    }
    
    $metadatos = [
        'ruta_original' => $rutaRelativa,
        'tipo' => $tipo,
        'fecha_eliminacion' => date('Y-m-d H:i:s'),
        'usuario' => isset($_SESSION['usuario']) ? $_SESSION['usuario'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'desconocido')
    ];
    
    escribirLogPapelera("Metadatos creados: " . json_encode($metadatos), $logFile);
    
    $rutaMetadatos = $rutaPapeleraAbsoluta . DIRECTORY_SEPARATOR . $nombreUnico . '.meta';
    file_put_contents($rutaMetadatos, json_encode($metadatos, JSON_PRETTY_PRINT));
    escribirLogPapelera("Archivo de metadatos creado en: $rutaMetadatos", $logFile);
    
    // Mover el archivo o carpeta a la papelera
    if ($tipo === 'carpeta') {
        // Para carpetas, usamos una función recursiva para copiar y luego eliminar
        $resultado = copiarCarpetaRecursiva($rutaAbsoluta, $rutaDestino, $logFile);
        if ($resultado) {
            // Si la copia fue exitosa, eliminamos la carpeta original
            $resultado = eliminarCarpetaRecursiva($rutaAbsoluta, $logFile);
        }
    } else {
        // Para archivos, simplemente usamos rename que es más eficiente
        $resultado = rename($rutaAbsoluta, $rutaDestino);
    }
    
    if (!$resultado) {
        escribirLogPapelera("ERROR: No se pudo mover a la papelera: $rutaAbsoluta", $logFile);
        // Eliminar el archivo de metadatos si falló
        if (file_exists($rutaMetadatos)) {
            unlink($rutaMetadatos);
        }
        return false;
    }
    
    escribirLogPapelera("Elemento movido correctamente a la papelera: $rutaAbsoluta -> $rutaDestino", $logFile);
    return true;
}

/**
 * Copia una carpeta y su contenido de forma recursiva
 * 
 * @param string $origen Ruta de origen
 * @param string $destino Ruta de destino
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se copió correctamente, False en caso contrario
 */
function copiarCarpetaRecursiva($origen, $destino, $logFile) {
    escribirLogPapelera("Iniciando copia recursiva de carpeta: $origen -> $destino", $logFile);
    
    // Crear el directorio de destino
    if (!file_exists($destino)) {
        escribirLogPapelera("Creando directorio de destino: $destino", $logFile);
        if (!mkdir($destino, 0755, true)) {
            $errorMsg = error_get_last();
            escribirLogPapelera("ERROR: No se pudo crear el directorio de destino: $destino. Error: " . ($errorMsg ? $errorMsg['message'] : 'Desconocido'), $logFile);
            return false;
        }
    }
    
    // Verificar que el origen existe y es un directorio
    if (!file_exists($origen)) {
        escribirLogPapelera("ERROR: El directorio de origen no existe: $origen", $logFile);
        return false;
    }
    
    if (!is_dir($origen)) {
        escribirLogPapelera("ERROR: El origen no es un directorio: $origen", $logFile);
        return false;
    }
    
    // Verificar permisos de lectura en el origen
    if (!is_readable($origen)) {
        escribirLogPapelera("ERROR: No hay permisos de lectura en el directorio de origen: $origen", $logFile);
        return false;
    }
    
    // Intentar abrir el directorio
    $dir = @opendir($origen);
    if (!$dir) {
        $errorMsg = error_get_last();
        escribirLogPapelera("ERROR: No se pudo abrir el directorio: $origen. Error: " . ($errorMsg ? $errorMsg['message'] : 'Desconocido'), $logFile);
        return false;
    }
    
    $resultado = true;
    
    while (($archivo = readdir($dir)) !== false) {
        if ($archivo != '.' && $archivo != '..') {
            $rutaOrigen = $origen . DIRECTORY_SEPARATOR . $archivo;
            $rutaDestino = $destino . DIRECTORY_SEPARATOR . $archivo;
            
            escribirLogPapelera("Procesando: $rutaOrigen", $logFile);
            
            if (is_dir($rutaOrigen)) {
                // Si es un directorio, llamar recursivamente
                $resultadoParcial = copiarCarpetaRecursiva($rutaOrigen, $rutaDestino, $logFile);
                if (!$resultadoParcial) {
                    escribirLogPapelera("ERROR: Falló la copia recursiva de: $rutaOrigen", $logFile);
                    $resultado = false;
                }
            } else {
                // Si es un archivo, copiarlo
                escribirLogPapelera("Copiando archivo: $rutaOrigen -> $rutaDestino", $logFile);
                if (!@copy($rutaOrigen, $rutaDestino)) {
                    $errorMsg = error_get_last();
                    escribirLogPapelera("ERROR: No se pudo copiar el archivo: $rutaOrigen -> $rutaDestino. Error: " . ($errorMsg ? $errorMsg['message'] : 'Desconocido'), $logFile);
                    $resultado = false;
                }
            }
        }
    }
    
    closedir($dir);
    
    if ($resultado) {
        escribirLogPapelera("Copia recursiva completada con éxito: $origen -> $destino", $logFile);
    } else {
        escribirLogPapelera("Copia recursiva completada con errores: $origen -> $destino", $logFile);
    }
    
    return $resultado;
}

/**
 * Elimina una carpeta y su contenido de forma recursiva
 * 
 * @param string $ruta Ruta de la carpeta a eliminar
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se eliminó correctamente, False en caso contrario
 */
function eliminarCarpetaRecursiva($ruta, $logFile) {
    escribirLogPapelera("Iniciando eliminación recursiva de carpeta: $ruta", $logFile);
    
    // Verificar que es un directorio
    if (!is_dir($ruta)) {
        escribirLogPapelera("ERROR: No es un directorio: $ruta", $logFile);
        return false;
    }
    
    // Verificar permisos
    if (!is_readable($ruta) || !is_writable($ruta)) {
        escribirLogPapelera("ERROR: No hay permisos suficientes en el directorio: $ruta", $logFile);
        return false;
    }
    
    try {
        // Crear el iterador recursivo
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($ruta, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        $errores = false;
        
        // Eliminar todos los archivos y subdirectorios
        foreach ($items as $item) {
            $path = $item->getRealPath();
            escribirLogPapelera("Eliminando: $path", $logFile);
            
            if ($item->isDir()) {
                // Es un directorio
                if (!@rmdir($path)) {
                    $errorMsg = error_get_last();
                    escribirLogPapelera("ERROR: No se pudo eliminar el directorio: $path. Error: " . ($errorMsg ? $errorMsg['message'] : 'Desconocido'), $logFile);
                    $errores = true;
                    // Continuamos con los demás elementos en lugar de abortar
                }
            } else {
                // Es un archivo
                if (!@unlink($path)) {
                    $errorMsg = error_get_last();
                    escribirLogPapelera("ERROR: No se pudo eliminar el archivo: $path. Error: " . ($errorMsg ? $errorMsg['message'] : 'Desconocido'), $logFile);
                    $errores = true;
                    // Continuamos con los demás elementos en lugar de abortar
                }
            }
        }
        
        // Si hubo errores, intentar eliminar lo que se pueda
        if ($errores) {
            escribirLogPapelera("ADVERTENCIA: Hubo errores al eliminar algunos elementos. Intentando eliminar el directorio principal de todos modos.", $logFile);
        }
        
        // Finalmente, eliminar el directorio principal
        if (!@rmdir($ruta)) {
            $errorMsg = error_get_last();
            escribirLogPapelera("ERROR: No se pudo eliminar el directorio principal: $ruta. Error: " . ($errorMsg ? $errorMsg['message'] : 'Desconocido'), $logFile);
            return false;
        }
        
        escribirLogPapelera("Eliminación recursiva completada con éxito: $ruta", $logFile);
        return true;
    } catch (Exception $e) {
        escribirLogPapelera("ERROR: Excepción al eliminar recursivamente: " . $e->getMessage(), $logFile);
        return false;
    }
}

/**
 * Restaura un elemento desde la papelera a su ubicación original
 * 
 * @param string $nombreElemento Nombre del elemento en la papelera
 * @param string $rutaBase Ruta base del almacenamiento
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se restauró correctamente, False en caso contrario
 */
function restaurarDePapelera($nombreElemento, $rutaBase, $logFile) {
    escribirLogPapelera("Restaurando elemento de la papelera: $nombreElemento", $logFile);
    
    // Usar la nueva ubicación de la papelera fuera del directorio de Almacenamiento
    $rutaPapelera = realpath(__DIR__ . '/../../Papelera');
    $rutaElemento = $rutaPapelera . DIRECTORY_SEPARATOR . $nombreElemento;
    $rutaMetadatos = $rutaElemento . '.meta';
    
    // Verificar que el elemento existe en la papelera
    if (!file_exists($rutaElemento)) {
        escribirLogPapelera("ERROR: El elemento no existe en la papelera: $rutaElemento", $logFile);
        return false;
    }
    
    // Verificar que existen los metadatos
    if (!file_exists($rutaMetadatos)) {
        escribirLogPapelera("ERROR: No se encontraron los metadatos del elemento: $rutaMetadatos", $logFile);
        return false;
    }
    
    // Leer los metadatos
    $metadatosJson = file_get_contents($rutaMetadatos);
    $metadatos = json_decode($metadatosJson, true);
    
    if (!$metadatos || !isset($metadatos['ruta_original']) || !isset($metadatos['tipo'])) {
        escribirLogPapelera("ERROR: Metadatos inválidos para el elemento: $rutaMetadatos", $logFile);
        return false;
    }
    
    $rutaOriginal = $rutaBase . DIRECTORY_SEPARATOR . $metadatos['ruta_original'];
    $tipo = $metadatos['tipo'];
    
    escribirLogPapelera("Restaurando a la ruta original: $rutaOriginal", $logFile);
    
    // Verificar si ya existe un elemento en la ruta original
    if (file_exists($rutaOriginal)) {
        escribirLogPapelera("ERROR: Ya existe un elemento en la ruta original: $rutaOriginal", $logFile);
        return false;
    }
    
    // Crear los directorios padre si no existen
    $dirPadre = dirname($rutaOriginal);
    if (!file_exists($dirPadre)) {
        if (!mkdir($dirPadre, 0755, true)) {
            escribirLogPapelera("ERROR: No se pudo crear el directorio padre: $dirPadre", $logFile);
            return false;
        }
    }
    
    // Restaurar el elemento
    $resultado = false;
    if ($tipo === 'carpeta') {
        // Para carpetas, usamos una función recursiva para copiar y luego eliminar
        $resultado = copiarCarpetaRecursiva($rutaElemento, $rutaOriginal, $logFile);
        if ($resultado) {
            // Si la copia fue exitosa, eliminamos la carpeta de la papelera
            $resultado = eliminarCarpetaRecursiva($rutaElemento, $logFile);
        }
    } else {
        // Para archivos, simplemente usamos rename que es más eficiente
        $resultado = rename($rutaElemento, $rutaOriginal);
    }
    
    if (!$resultado) {
        escribirLogPapelera("ERROR: No se pudo restaurar el elemento: $rutaElemento -> $rutaOriginal", $logFile);
        return false;
    }
    
    // Eliminar el archivo de metadatos
    if (file_exists($rutaMetadatos)) {
        unlink($rutaMetadatos);
    }
    
    escribirLogPapelera("Elemento restaurado correctamente: $rutaElemento -> $rutaOriginal", $logFile);
    return true;
}

/**
 * Elimina permanentemente un elemento de la papelera
 * 
 * @param string $nombreElemento Nombre del elemento en la papelera
 * @param string $rutaBase Ruta base del almacenamiento
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se eliminó correctamente, False en caso contrario
 */
function eliminarDePapelera($nombreElemento, $rutaBase, $logFile) {
    escribirLogPapelera("Eliminando permanentemente de la papelera: $nombreElemento", $logFile);
    
    // Usar la nueva ubicación de la papelera fuera del directorio de Almacenamiento
    $rutaPapelera = realpath(__DIR__ . '/../../Papelera');
    $rutaElemento = $rutaPapelera . DIRECTORY_SEPARATOR . $nombreElemento;
    $rutaMetadatos = $rutaElemento . '.meta';
    
    // Verificar que el elemento existe en la papelera
    if (!file_exists($rutaElemento)) {
        escribirLogPapelera("ERROR: El elemento no existe en la papelera: $rutaElemento", $logFile);
        return false;
    }
    
    // Leer los metadatos para determinar el tipo
    $tipo = 'archivo';
    if (file_exists($rutaMetadatos)) {
        $metadatosJson = file_get_contents($rutaMetadatos);
        $metadatos = json_decode($metadatosJson, true);
        if ($metadatos && isset($metadatos['tipo'])) {
            $tipo = $metadatos['tipo'];
        }
    }
    
    // Eliminar el elemento
    $resultado = false;
    if ($tipo === 'carpeta' && is_dir($rutaElemento)) {
        $resultado = eliminarCarpetaRecursiva($rutaElemento, $logFile);
    } else {
        $resultado = unlink($rutaElemento);
    }
    
    if (!$resultado) {
        escribirLogPapelera("ERROR: No se pudo eliminar permanentemente el elemento: $rutaElemento", $logFile);
        return false;
    }
    
    // Eliminar el archivo de metadatos
    if (file_exists($rutaMetadatos)) {
        unlink($rutaMetadatos);
    }
    
    escribirLogPapelera("Elemento eliminado permanentemente: $rutaElemento", $logFile);
    return true;
}

/**
 * Obtiene la lista de elementos en la papelera
 * 
 * @param string $rutaBase Ruta base del almacenamiento
 * @return array Lista de elementos en la papelera con sus metadatos
 */
function listarElementosPapelera($rutaBase) {
    // Usar la nueva ubicación de la papelera fuera del directorio de Almacenamiento
    $rutaPapelera = realpath(__DIR__ . '/../../Papelera');
    $elementos = [];
    
    if (!file_exists($rutaPapelera) || !is_dir($rutaPapelera)) {
        return $elementos;
    }
    
    $archivos = scandir($rutaPapelera);
    
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..' || substr($archivo, -5) === '.meta') {
            continue;
        }
        
        $rutaElemento = $rutaPapelera . DIRECTORY_SEPARATOR . $archivo;
        $rutaMetadatos = $rutaElemento . '.meta';
        
        $elemento = [
            'nombre' => $archivo,
            'ruta' => $rutaElemento,
            'es_directorio' => is_dir($rutaElemento),
            'fecha_eliminacion' => date('Y-m-d H:i:s', filemtime($rutaElemento)),
            'ruta_original' => '',
            'tipo' => is_dir($rutaElemento) ? 'carpeta' : 'archivo',
            'usuario' => 'desconocido'
        ];
        
        // Leer metadatos si existen
        if (file_exists($rutaMetadatos)) {
            $metadatosJson = file_get_contents($rutaMetadatos);
            $metadatos = json_decode($metadatosJson, true);
            
            if ($metadatos) {
                if (isset($metadatos['ruta_original'])) {
                    $elemento['ruta_original'] = $metadatos['ruta_original'];
                }
                if (isset($metadatos['fecha_eliminacion'])) {
                    $elemento['fecha_eliminacion'] = $metadatos['fecha_eliminacion'];
                }
                if (isset($metadatos['tipo'])) {
                    $elemento['tipo'] = $metadatos['tipo'];
                }
                if (isset($metadatos['usuario'])) {
                    $elemento['usuario'] = $metadatos['usuario'];
                }
            }
        }
        
        // Obtener tamaño para archivos
        if (!$elemento['es_directorio']) {
            $elemento['tamano'] = filesize($rutaElemento);
            $elemento['tamano_formateado'] = formatearTamano($elemento['tamano']);
        } else {
            // Para directorios, calcular tamaño recursivamente
            $elemento['tamano'] = calcularTamanoDirectorio($rutaElemento);
            $elemento['tamano_formateado'] = formatearTamano($elemento['tamano']);
        }
        
        // Obtener nombre original del archivo/carpeta
        $nombreOriginal = basename($elemento['ruta_original']);
        if (empty($nombreOriginal)) {
            // Si no hay ruta original, extraer del nombre en papelera
            $partes = explode('_', $archivo, 2);
            $nombreOriginal = $partes[1] ?? $archivo;
        }
        $elemento['nombre_original'] = $nombreOriginal;
        
        $elementos[] = $elemento;
    }
    
    // Ordenar por fecha de eliminación (más reciente primero)
    usort($elementos, function($a, $b) {
        return strtotime($b['fecha_eliminacion']) - strtotime($a['fecha_eliminacion']);
    });
    
    return $elementos;
}

/**
 * Calcula el tamaño de un directorio de forma recursiva
 * 
 * @param string $directorio Ruta del directorio
 * @return int Tamaño en bytes
 */
function calcularTamanoDirectorio($directorio) {
    $tamano = 0;
    
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio)) as $archivo) {
        if ($archivo->isFile()) {
            $tamano += $archivo->getSize();
        }
    }
    
    return $tamano;
}

/**
 * Formatea un tamaño en bytes a una unidad legible
 * 
 * @param int $tamano Tamaño en bytes
 * @return string Tamaño formateado
 */
function formatearTamano($tamano) {
    $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($tamano >= 1024 && $i < count($unidades) - 1) {
        $tamano /= 1024;
        $i++;
    }
    
    return round($tamano, 2) . ' ' . $unidades[$i];
}

/**
 * Vacía completamente la papelera
 * 
 * @param string $rutaBase Ruta base del almacenamiento
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se vació correctamente, False en caso contrario
 */
function vaciarPapelera($rutaBase, $logFile) {
    escribirLogPapelera("Vaciando papelera", $logFile);
    
    // Usar la nueva ubicación de la papelera fuera del directorio de Almacenamiento
    $rutaPapelera = realpath(__DIR__ . '/../../Papelera');
    
    if (!file_exists($rutaPapelera) || !is_dir($rutaPapelera)) {
        escribirLogPapelera("ERROR: La papelera no existe: $rutaPapelera", $logFile);
        return false;
    }
    
    $elementos = listarElementosPapelera($rutaBase);
    $resultado = true;
    
    foreach ($elementos as $elemento) {
        $resultadoElemento = eliminarDePapelera($elemento['nombre'], $rutaBase, $logFile);
        $resultado = $resultado && $resultadoElemento;
    }
    
    escribirLogPapelera("Papelera vaciada " . ($resultado ? "correctamente" : "con errores"), $logFile);
    return $resultado;
}

// Procesar solicitudes de la papelera
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear un nuevo archivo de log para cada solicitud
    $logFile = __DIR__ . '/debug_papelera_' . date('Y-m-d_H-i-s') . '.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);
    
    escribirLogPapelera("=== NUEVA SOLICITUD DE PAPELERA ===", $logFile);
    
    // Registrar todos los datos POST para depuración
    escribirLogPapelera("Datos POST recibidos: " . json_encode($_POST), $logFile);
    
    $accion = $_POST['accion'] ?? '';
    escribirLogPapelera("Acción solicitada: $accion", $logFile);
    $resultado = false;
    
    switch ($accion) {
        case 'restaurar':
            if (isset($_POST['elemento']) && !empty($_POST['elemento'])) {
                $elemento = $_POST['elemento'];
                escribirLogPapelera("Solicitud de restauración: $elemento", $logFile);
                $resultado = restaurarDePapelera($elemento, $rutaBase, $logFile);
            }
            break;
            
        case 'eliminar_permanente':
            if (isset($_POST['elemento']) && !empty($_POST['elemento'])) {
                $elemento = $_POST['elemento'];
                escribirLogPapelera("Solicitud de eliminación permanente: $elemento", $logFile);
                $resultado = eliminarDePapelera($elemento, $rutaBase, $logFile);
            }
            break;
            
        case 'vaciar':
            escribirLogPapelera("Solicitud de vaciado de papelera", $logFile);
            $resultado = vaciarPapelera($rutaBase, $logFile);
            break;
    }
    
    // Preparar la URL de redirección
    $urlReferrer = $_SERVER['HTTP_REFERER'] ?? '../index.php?modulo=papelera';
    $urlBase = strtok($urlReferrer, '?'); // Obtener la URL base sin parámetros
    
    // Construir la URL de redirección
    if (strpos($urlReferrer, 'modulo=papelera') !== false) {
        $urlRedireccion = $urlBase . '?modulo=papelera';
        
        // Ya tenemos un parámetro, usar & para los siguientes
        if ($resultado) {
            $urlRedireccion .= '&accion_completada=1';
        } else {
            $urlRedireccion .= '&error=accion_fallida';
        }
    } else {
        $urlRedireccion = $urlBase;
        
        // No tenemos parámetros aún, usar ? para el primero
        if ($resultado) {
            $urlRedireccion .= '?accion_completada=1';
        } else {
            $urlRedireccion .= '?error=accion_fallida';
        }
    }
    
    // Añadir un parámetro de timestamp para forzar la recarga
    // Siempre usar & porque ya tenemos al menos un parámetro
    $urlRedireccion .= '&t=' . time();
    
    // Redireccionar
    escribirLogPapelera("Redirigiendo a: $urlRedireccion", $logFile);
    header('Location: ' . $urlRedireccion);
    exit;
}
?>
