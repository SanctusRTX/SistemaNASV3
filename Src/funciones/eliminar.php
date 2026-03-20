<?php
/**
 * Funciones para eliminar archivos y carpetas
 * Este archivo contiene las funciones necesarias para mover archivos y carpetas a la papelera
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

// Crear archivo de log para depuración
$logFile = __DIR__ . '/debug_eliminar.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);

// Incluir las funciones de la papelera
require_once __DIR__ . '/papelera.php';

// Función para escribir en el log
function escribirLog($mensaje, $logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $mensaje\n", FILE_APPEND);
}

// Función para validar que la ruta esté dentro del directorio de almacenamiento
function validarRuta($ruta, $rutaBase, $logFile) {
    escribirLog("Validando ruta: $ruta", $logFile);
    
    // Verificar que la ruta existe
    if (!file_exists($ruta)) {
        escribirLog("ERROR: La ruta no existe: $ruta", $logFile);
        return false;
    }
    
    // Convertir a ruta absoluta
    $rutaAbsoluta = realpath($ruta);
    
    // Verificar que la ruta absoluta se obtuvo correctamente
    if (!$rutaAbsoluta) {
        escribirLog("ERROR: No se pudo obtener la ruta absoluta para: $ruta", $logFile);
        return false;
    }
    
    // Verificar que la ruta está dentro del directorio base
    $rutaBaseAbsoluta = realpath($rutaBase);
    if (strpos($rutaAbsoluta, $rutaBaseAbsoluta) !== 0) {
        escribirLog("ERROR: La ruta está fuera del directorio base:", $logFile);
        escribirLog("Ruta a validar: $rutaAbsoluta", $logFile);
        escribirLog("Directorio base: $rutaBaseAbsoluta", $logFile);
        return false;
    }
    
    escribirLog("Ruta validada correctamente: $rutaAbsoluta", $logFile);
    return $rutaAbsoluta;
}

// Función para mover una carpeta a la papelera en lugar de eliminarla
function eliminarCarpeta($ruta, $rutaBase, $logFile) {
    escribirLog("Iniciando movimiento de carpeta a papelera: $ruta", $logFile);
    
    // Validar la ruta
    $rutaAbsoluta = validarRuta($ruta, $rutaBase, $logFile);
    if (!$rutaAbsoluta) {
        escribirLog("ERROR: La ruta no es válida: $ruta", $logFile);
        return false;
    }
    
    // Verificar que es un directorio
    if (!is_dir($rutaAbsoluta)) {
        escribirLog("ERROR: No es un directorio: $rutaAbsoluta", $logFile);
        return false;
    }
    
    // Verificar que la carpeta de la papelera existe (ahora fuera del directorio de Almacenamiento)
    $rutaPapelera = realpath(__DIR__ . '/../../Papelera');
    if (!file_exists($rutaPapelera)) {
        escribirLog("Creando carpeta de papelera: $rutaPapelera", $logFile);
        if (!mkdir($rutaPapelera, 0755, true)) {
            escribirLog("ERROR: No se pudo crear la carpeta de la papelera: $rutaPapelera", $logFile);
            return false;
        }
    }
    escribirLog("Usando la papelera en: $rutaPapelera", $logFile);
    
    // Mover a la papelera en lugar de eliminar
    escribirLog("Llamando a moverAPapelera para: $rutaAbsoluta", $logFile);
    $resultado = moverAPapelera($rutaAbsoluta, $rutaBase, 'carpeta', $logFile);
    
    if ($resultado) {
        escribirLog("Carpeta movida a la papelera correctamente: $rutaAbsoluta", $logFile);
    } else {
        escribirLog("ERROR: No se pudo mover la carpeta a la papelera: $rutaAbsoluta", $logFile);
    }
    
    return $resultado;
}

// Función para mover un archivo a la papelera en lugar de eliminarlo
function eliminarArchivo($ruta, $rutaBase, $logFile) {
    escribirLog("Iniciando movimiento de archivo a papelera: $ruta", $logFile);
    
    // Validar la ruta
    $rutaAbsoluta = validarRuta($ruta, $rutaBase, $logFile);
    if (!$rutaAbsoluta) {
        escribirLog("ERROR: La ruta no es válida: $ruta", $logFile);
        return false;
    }
    
    // Verificar que es un archivo
    if (!is_file($rutaAbsoluta)) {
        escribirLog("ERROR: No es un archivo: $rutaAbsoluta", $logFile);
        return false;
    }
    
    // Verificar que la carpeta de la papelera existe (ahora fuera del directorio de Almacenamiento)
    $rutaPapelera = realpath(__DIR__ . '/../../Papelera');
    if (!file_exists($rutaPapelera)) {
        escribirLog("Creando carpeta de papelera: $rutaPapelera", $logFile);
        if (!mkdir($rutaPapelera, 0755, true)) {
            escribirLog("ERROR: No se pudo crear la carpeta de la papelera: $rutaPapelera", $logFile);
            return false;
        }
    }
    escribirLog("Usando la papelera en: $rutaPapelera", $logFile);
    
    // Verificar permisos de lectura y escritura
    if (!is_readable($rutaAbsoluta)) {
        escribirLog("ERROR: No hay permisos de lectura en el archivo: $rutaAbsoluta", $logFile);
        return false;
    }
    
    if (!is_writable($rutaPapelera)) {
        escribirLog("ERROR: No hay permisos de escritura en la papelera: $rutaPapelera", $logFile);
        return false;
    }
    
    // Mover a la papelera en lugar de eliminar
    escribirLog("Llamando a moverAPapelera para: $rutaAbsoluta", $logFile);
    $resultado = moverAPapelera($rutaAbsoluta, $rutaBase, 'archivo', $logFile);
    
    if ($resultado) {
        escribirLog("Archivo movido a la papelera correctamente: $rutaAbsoluta", $logFile);
    } else {
        escribirLog("ERROR: No se pudo mover el archivo a la papelera: $rutaAbsoluta", $logFile);
    }
    
    return $resultado;
}

// Procesar solicitud de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    // Crear un nuevo archivo de log para cada solicitud
    $logFile = __DIR__ . '/debug_eliminar_' . date('Y-m-d_H-i-s') . '.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);
    
    escribirLog("=== NUEVA SOLICITUD DE ELIMINACIÓN ===", $logFile);
    
    // Registrar todos los datos POST para depuración
    escribirLog("Datos POST recibidos: " . json_encode($_POST), $logFile);
    escribirLog("Referrer: " . ($_SERVER['HTTP_REFERER'] ?? 'No disponible'), $logFile);
    
    // Verificar que se proporcionó una ruta
    if (!isset($_POST['ruta']) || empty($_POST['ruta'])) {
        escribirLog("ERROR: No se proporcionó una ruta", $logFile);
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=ruta_vacia');
        exit;
    }
    
    $rutaRelativa = $_POST['ruta'];
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    
    escribirLog("Ruta relativa a eliminar: $rutaRelativa", $logFile);
    escribirLog("Tipo de elemento: $tipo", $logFile);
    
    // Construir la ruta absoluta correcta
    $rutaAbsoluta = $rutaBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);
    escribirLog("Ruta absoluta construida: $rutaAbsoluta", $logFile);
    
    // Verificar que la ruta existe
    if (!file_exists($rutaAbsoluta)) {
        escribirLog("ERROR: La ruta absoluta no existe: $rutaAbsoluta", $logFile);
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
        escribirLog("Tipo determinado automáticamente: $tipo", $logFile);
    }
    
    // Eliminar según el tipo
    $resultado = false;
    if ($tipo === 'carpeta') {
        $resultado = eliminarCarpeta($rutaAbsoluta, $rutaBase, $logFile);
    } else {
        $resultado = eliminarArchivo($rutaAbsoluta, $rutaBase, $logFile);
    }
    
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
    // Usar & solo si ya hay al menos un parámetro (? ya fue usado)
    if (strpos($urlRedireccion, '?') !== false) {
        $urlRedireccion .= '&t=' . time();
    } else {
        $urlRedireccion .= '?t=' . time();
    }
    
    // Redireccionar
    if ($resultado) {
        escribirLog("Eliminación exitosa, redirigiendo a: $urlRedireccion", $logFile);
    } else {
        escribirLog("Error en la eliminación, redirigiendo a: $urlRedireccion", $logFile);
    }
    
    header('Location: ' . $urlRedireccion);
    exit;
}
?>
