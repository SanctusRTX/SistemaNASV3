<?php
/**
 * Funciones simplificadas para la papelera de reciclaje
 * Esta versión utiliza un enfoque más directo y con mejor manejo de errores
 */

// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Definir la ruta base de almacenamiento
$rutaBase = realpath(__DIR__ . '/../Almacenamiento');

// Definir la ruta de la papelera (directamente en la raíz del proyecto)
$rutaPapelera = realpath(__DIR__ . '/../../Papelera');
if (!$rutaPapelera) {
    // Si la carpeta no existe, crearla
    mkdir(__DIR__ . '/../../Papelera', 0755, true);
    $rutaPapelera = realpath(__DIR__ . '/../../Papelera');
}

// Crear archivo de log para depuración
$logFile = __DIR__ . '/debug_papelera_simple.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);

// Función para escribir en el log
function escribirLogPapelera($mensaje, $logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $mensaje\n", FILE_APPEND);
}

/**
 * Mueve un archivo o carpeta a la papelera
 * 
 * @param string $rutaOriginal Ruta absoluta del elemento a mover
 * @param string $rutaBase Ruta base del almacenamiento
 * @param string $tipo Tipo de elemento ('archivo' o 'carpeta')
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se movió correctamente, False en caso contrario
 */
function moverAPapelera($rutaOriginal, $rutaBase, $tipo, $logFile) {
    global $rutaPapelera;
    
    escribirLogPapelera("Moviendo a papelera: $rutaOriginal", $logFile);
    
    // Verificar que la ruta existe
    if (!file_exists($rutaOriginal)) {
        escribirLogPapelera("ERROR: La ruta no existe: $rutaOriginal", $logFile);
        return false;
    }
    
    // Verificar que la papelera existe
    if (!file_exists($rutaPapelera)) {
        escribirLogPapelera("Creando carpeta de papelera: $rutaPapelera", $logFile);
        if (!mkdir($rutaPapelera, 0755, true)) {
            escribirLogPapelera("ERROR: No se pudo crear la carpeta de la papelera", $logFile);
            return false;
        }
    }
    
    // Crear un nombre único para el elemento en la papelera
    $nombreBase = basename($rutaOriginal);
    $nombreUnico = date('YmdHis') . '_' . $nombreBase;
    $rutaDestino = $rutaPapelera . DIRECTORY_SEPARATOR . $nombreUnico;
    
    escribirLogPapelera("Ruta destino en papelera: $rutaDestino", $logFile);
    
    // Crear un archivo de metadatos con la información original
    $rutaRelativa = str_replace($rutaBase . DIRECTORY_SEPARATOR, '', $rutaOriginal);
    $metadatos = [
        'nombre_original' => $nombreBase,
        'ruta_original' => $rutaRelativa,
        'tipo' => $tipo,
        'fecha_eliminacion' => date('Y-m-d H:i:s')
    ];
    
    $rutaMetadatos = $rutaPapelera . DIRECTORY_SEPARATOR . $nombreUnico . '.meta';
    if (!file_put_contents($rutaMetadatos, json_encode($metadatos, JSON_PRETTY_PRINT))) {
        escribirLogPapelera("ERROR: No se pudo crear el archivo de metadatos", $logFile);
        return false;
    }
    
    // Mover el elemento a la papelera
    $resultado = false;
    if ($tipo === 'archivo') {
        // Para archivos, simplemente usamos copy + unlink
        if (copy($rutaOriginal, $rutaDestino)) {
            $resultado = unlink($rutaOriginal);
        }
    } else {
        // Para carpetas, usamos una función recursiva
        $resultado = moverCarpetaRecursiva($rutaOriginal, $rutaDestino, $logFile);
    }
    
    if ($resultado) {
        escribirLogPapelera("Elemento movido correctamente a la papelera", $logFile);
    } else {
        escribirLogPapelera("ERROR: No se pudo mover el elemento a la papelera", $logFile);
        // Eliminar el archivo de metadatos si falló
        if (file_exists($rutaMetadatos)) {
            unlink($rutaMetadatos);
        }
    }
    
    return $resultado;
}

/**
 * Mueve una carpeta y su contenido a la papelera
 * 
 * @param string $origen Ruta de origen
 * @param string $destino Ruta de destino
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se movió correctamente, False en caso contrario
 */
function moverCarpetaRecursiva($origen, $destino, $logFile) {
    escribirLogPapelera("Moviendo carpeta recursivamente: $origen -> $destino", $logFile);
    
    // Crear el directorio de destino
    if (!file_exists($destino)) {
        if (!mkdir($destino, 0755, true)) {
            escribirLogPapelera("ERROR: No se pudo crear el directorio de destino", $logFile);
            return false;
        }
    }
    
    $dir = opendir($origen);
    $resultado = true;
    
    while (($archivo = readdir($dir)) !== false) {
        if ($archivo != "." && $archivo != "..") {
            $rutaOrigen = $origen . DIRECTORY_SEPARATOR . $archivo;
            $rutaDestino = $destino . DIRECTORY_SEPARATOR . $archivo;
            
            if (is_dir($rutaOrigen)) {
                // Si es un directorio, llamar recursivamente
                $resultadoParcial = moverCarpetaRecursiva($rutaOrigen, $rutaDestino, $logFile);
                $resultado = $resultado && $resultadoParcial;
            } else {
                // Si es un archivo, copiarlo
                if (!copy($rutaOrigen, $rutaDestino)) {
                    escribirLogPapelera("ERROR: No se pudo copiar el archivo: $rutaOrigen", $logFile);
                    $resultado = false;
                }
            }
        }
    }
    
    closedir($dir);
    
    // Si la copia fue exitosa, eliminar la carpeta original
    if ($resultado) {
        $resultado = eliminarCarpetaVacia($origen, $logFile);
    }
    
    return $resultado;
}

/**
 * Elimina una carpeta y su contenido
 * 
 * @param string $ruta Ruta de la carpeta a eliminar
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se eliminó correctamente, False en caso contrario
 */
function eliminarCarpetaVacia($ruta, $logFile) {
    $dir = opendir($ruta);
    
    while (($archivo = readdir($dir)) !== false) {
        if ($archivo != "." && $archivo != "..") {
            $rutaArchivo = $ruta . DIRECTORY_SEPARATOR . $archivo;
            
            if (is_dir($rutaArchivo)) {
                eliminarCarpetaVacia($rutaArchivo, $logFile);
            } else {
                unlink($rutaArchivo);
            }
        }
    }
    
    closedir($dir);
    
    if (rmdir($ruta)) {
        return true;
    } else {
        escribirLogPapelera("ERROR: No se pudo eliminar la carpeta: $ruta", $logFile);
        return false;
    }
}

/**
 * Elimina permanentemente un elemento de la papelera
 * 
 * @param string $nombreElemento Nombre del elemento en la papelera
 * @param string $logFile Ruta del archivo de log
 * @return bool True si se eliminó correctamente, False en caso contrario
 */
function eliminarDePapelera($nombreElemento, $logFile) {
    global $rutaPapelera;
    
    escribirLogPapelera("Eliminando permanentemente de la papelera: $nombreElemento", $logFile);
    
    $rutaElemento = $rutaPapelera . DIRECTORY_SEPARATOR . $nombreElemento;
    $rutaMetadatos = $rutaElemento . '.meta';
    
    // Verificar que el elemento existe
    if (!file_exists($rutaElemento)) {
        escribirLogPapelera("ERROR: El elemento no existe en la papelera: $rutaElemento", $logFile);
        return false;
    }
    
    $resultado = false;
    
    // Eliminar el elemento según su tipo
    if (is_dir($rutaElemento)) {
        // Si es un directorio, eliminarlo recursivamente
        $resultado = eliminarCarpetaVacia($rutaElemento, $logFile);
    } else {
        // Si es un archivo, eliminarlo directamente
        $resultado = unlink($rutaElemento);
    }
    
    // Eliminar el archivo de metadatos si existe
    if (file_exists($rutaMetadatos)) {
        unlink($rutaMetadatos);
    }
    
    if ($resultado) {
        escribirLogPapelera("Elemento eliminado permanentemente: $nombreElemento", $logFile);
    } else {
        escribirLogPapelera("ERROR: No se pudo eliminar permanentemente el elemento: $nombreElemento", $logFile);
    }
    
    return $resultado;
}

// Procesar solicitudes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    // Crear un nuevo archivo de log para cada solicitud
    $logFile = __DIR__ . '/debug_papelera_simple_' . date('Y-m-d_H-i-s') . '.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);
    
    escribirLogPapelera("=== NUEVA SOLICITUD DE PAPELERA ===", $logFile);
    escribirLogPapelera("Datos POST: " . json_encode($_POST), $logFile);
    
    $accion = $_POST['accion'];
    $resultado = false;
    
    switch ($accion) {
        case 'eliminar_permanente':
            if (isset($_POST['elemento']) && !empty($_POST['elemento'])) {
                $elemento = $_POST['elemento'];
                escribirLogPapelera("Solicitud de eliminación permanente: $elemento", $logFile);
                $resultado = eliminarDePapelera($elemento, $logFile);
            } else {
                escribirLogPapelera("ERROR: No se especificó el elemento a eliminar permanentemente", $logFile);
            }
            
            // Preparar la URL de redirección para eliminación permanente
            $urlReferrer = $_SERVER['HTTP_REFERER'] ?? '../index.php?modulo=papelera';
            $urlBase = strtok($urlReferrer, '?'); // Obtener la URL base sin parámetros
            
            // Construir la URL de redirección
            if (strpos($urlReferrer, 'modulo=papelera') !== false) {
                $urlRedireccion = $urlBase . '?modulo=papelera';
            } else {
                $urlRedireccion = $urlBase;
            }
            
            // Añadir el resultado de la operación
            if ($resultado) {
                $urlRedireccion .= strpos($urlRedireccion, '?') !== false ? '&accion_completada=1' : '?accion_completada=1';
            } else {
                $urlRedireccion .= strpos($urlRedireccion, '?') !== false ? '&error=accion_fallida' : '?error=accion_fallida';
            }
            
            // Añadir timestamp para forzar recarga
            $urlRedireccion .= '&t=' . time();
            
            // Redireccionar
            escribirLogPapelera("Redirigiendo a: $urlRedireccion", $logFile);
            header('Location: ' . $urlRedireccion);
            exit;
            
            break;
            
        case 'eliminar':
            // Verificar que se proporcionó una ruta
            if (!isset($_POST['ruta']) || empty($_POST['ruta'])) {
                escribirLogPapelera("ERROR: No se proporcionó una ruta", $logFile);
                header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=ruta_vacia');
                exit;
            }
            
            $rutaRelativa = $_POST['ruta'];
            $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
            
            escribirLogPapelera("Ruta relativa a eliminar: $rutaRelativa", $logFile);
            escribirLogPapelera("Tipo de elemento: $tipo", $logFile);
            
            // Construir la ruta absoluta correcta
            $rutaAbsoluta = $rutaBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);
            escribirLogPapelera("Ruta absoluta construida: $rutaAbsoluta", $logFile);
            
            // Verificar que la ruta existe
            if (!file_exists($rutaAbsoluta)) {
                escribirLogPapelera("ERROR: La ruta absoluta no existe: $rutaAbsoluta", $logFile);
                header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=ruta_no_existe');
                exit;
            }
            
            // Determinar si es un archivo o carpeta si no se especificó
            if (empty($tipo)) {
                if (is_dir($rutaAbsoluta)) {
                    $tipo = 'carpeta';
                } else {
                    $tipo = 'archivo';
                }
                escribirLogPapelera("Tipo determinado automáticamente: $tipo", $logFile);
            }
            
            // Mover a la papelera
            $resultado = moverAPapelera($rutaAbsoluta, $rutaBase, $tipo, $logFile);
            
            // Preparar la URL de redirección
            $urlReferrer = $_SERVER['HTTP_REFERER'];
            $urlBase = strtok($urlReferrer, '?'); // Obtener la URL base sin parámetros
            
            // Preservar el parámetro de carpeta si existe
            $carpetaParam = '';
            if (preg_match('/[\?&]carpeta=([^&]+)/', $urlReferrer, $matches)) {
                $carpetaParam = 'carpeta=' . $matches[1];
            }
            
            // Construir la URL de redirección
            $urlRedireccion = $urlBase;
            if (!empty($carpetaParam)) {
                $urlRedireccion .= '?' . $carpetaParam;
                if ($resultado) {
                    $urlRedireccion .= '&eliminado=1';
                } else {
                    $urlRedireccion .= '&error=eliminacion_fallida';
                }
            } else {
                if ($resultado) {
                    $urlRedireccion .= '?eliminado=1';
                } else {
                    $urlRedireccion .= '?error=eliminacion_fallida';
                }
            }
            
            // Añadir un parámetro de timestamp para forzar la recarga
            if (strpos($urlRedireccion, '?') !== false) {
                $urlRedireccion .= '&t=' . time();
            } else {
                $urlRedireccion .= '?t=' . time();
            }
            
            // Redireccionar
            escribirLogPapelera("Redirigiendo a: $urlRedireccion", $logFile);
            header('Location: ' . $urlRedireccion);
            exit;
            
            break;
    }
}
?>
