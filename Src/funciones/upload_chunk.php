<?php
/**
 * Manejador de subida de archivos por fragmentos
 * Permite subir archivos grandes dividiéndolos en fragmentos pequeños
 */

// Iniciar sesión para verificar permisos
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Incluir archivo de roles para verificar permisos
require_once __DIR__ . '/roles_simple.php';

// Verificar si el usuario tiene permiso para subir archivos
if (!tienePermiso('subir')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para subir archivos']);
    exit;
}

// Configurar límites de PHP para manejar archivos grandes
ini_set('upload_max_filesize', '10000M');
ini_set('post_max_size', '10000M');
ini_set('memory_limit', '1000M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
set_time_limit(0);

// Directorio para almacenar fragmentos temporales
$tempDir = __DIR__ . '/../temp';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Directorio base de almacenamiento
$rutaBase = __DIR__ . '/../Almacenamiento';

if (!is_dir($rutaBase)) {
    if (!mkdir($rutaBase, 0777, true)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se pudo crear la carpeta base Almacenamiento']);
        exit;
    }
}
// Función para escribir en el log
function escribirLog($mensaje) {
    $logFile = __DIR__ . '/../logs/chunked_upload.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $mensaje\n";
    
    // Crear directorio de logs si no existe
    if (!file_exists(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0777, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Manejar la acción de completar la subida
if (isset($_POST['action']) && $_POST['action'] === 'complete') {
    handleCompleteUpload();
} else {
    // Manejar la subida de un fragmento
    handleChunkUpload();
}

// Función para manejar la subida de un fragmento
function handleChunkUpload() {
    global $tempDir;
    
    // Verificar que se hayan enviado todos los datos necesarios
    if (!isset($_FILES['chunk']) || !isset($_POST['fileId']) || !isset($_POST['chunkIndex']) || !isset($_POST['totalChunks']) || !isset($_POST['fileName'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
        exit;
    }
    
    $fileId = $_POST['fileId'];
    $chunkIndex = (int)$_POST['chunkIndex'];
    $totalChunks = (int)$_POST['totalChunks'];
    $fileName = $_POST['fileName'];
    
    // Validar el nombre del archivo (eliminar caracteres no permitidos)
    $fileName = preg_replace('/[\\/\:\*\?\"\<\>\|]/', '', $fileName);
    
    // Crear directorio para los fragmentos de este archivo si no existe
    $fileDir = $tempDir . '/' . $fileId;
    if (!file_exists($fileDir)) {
        mkdir($fileDir, 0777, true);
    }
    
    // Ruta del fragmento
    $chunkPath = $fileDir . '/' . $chunkIndex;
    
    // Mover el fragmento subido a su ubicación temporal
    if (move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath)) {
        escribirLog("Fragmento $chunkIndex/$totalChunks del archivo $fileName subido correctamente");
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Fragmento $chunkIndex/$totalChunks subido correctamente",
            'chunkIndex' => $chunkIndex,
            'totalChunks' => $totalChunks
        ]);
    } else {
        escribirLog("Error al subir el fragmento $chunkIndex/$totalChunks del archivo $fileName");
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Error al subir el fragmento $chunkIndex/$totalChunks"
        ]);
    }
}

// Función para manejar la finalización de la subida
function handleCompleteUpload() {
    global $tempDir, $rutaBase;
    
    // Verificar que se hayan enviado todos los datos necesarios
    if (!isset($_POST['fileId']) || !isset($_POST['fileName']) || !isset($_POST['totalChunks'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
        exit;
    }
    
    $fileId = $_POST['fileId'];
    $fileName = $_POST['fileName'];
    $totalChunks = (int)$_POST['totalChunks'];
    $carpetaDestino = isset($_POST['carpeta_destino']) ? $_POST['carpeta_destino'] : 'Almacenamiento';
    
    // Validar el nombre del archivo (eliminar caracteres no permitidos)
    $fileName = preg_replace('/[\\/\:\*\?\"\<\>\|]/', '', $fileName);
    
    // Directorio donde están los fragmentos
    $fileDir = $tempDir . '/' . $fileId;
    
    // Verificar que todos los fragmentos existan
    for ($i = 0; $i < $totalChunks; $i++) {
        if (!file_exists($fileDir . '/' . $i)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => "Faltan fragmentos. No se encontró el fragmento $i/$totalChunks"
            ]);
            exit;
        }
    }
    
    // Determinar la ruta de destino
    
    // Normalizar y limpiar la ruta de destino recibida desde el formulario
    if (str_starts_with($carpetaDestino, $rutaBase)) {
        $carpetaRelativa = trim(str_replace($rutaBase, '', $carpetaDestino), '/\\');
    } elseif (str_starts_with($carpetaDestino, 'Almacenamiento')) {
        $carpetaRelativa = trim(str_replace('Almacenamiento', '', $carpetaDestino), '/\\'); 
    } else {
        $carpetaRelativa = trim($carpetaDestino, '/\\');
    }

    // Normalizar separadores
    $carpetaRelativa = str_replace(['\\', '//'], '/', $carpetaRelativa);

    // Log para depuración
    escribirLog("Valor original de carpetaDestino: '$carpetaDestino'");
    escribirLog("Valor procesado de carpetaRelativa: '$carpetaRelativa'");

    // Validación de seguridad
    if (
        strpos($carpetaRelativa, '..') !== false ||
        preg_match('/^[A-Za-z]:/', $carpetaRelativa) // Detecta rutas tipo "C:\"
    ) {
        escribirLog("ERROR: Ruta de destino inválida o potencialmente peligrosa → '$carpetaRelativa'");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ruta de destino inválida']);
        exit;
    }

    // Construir ruta absoluta final
    if ($carpetaRelativa === '') {
        $rutaDestino = rtrim($rutaBase, '/\\');
    } else {
        $rutaDestino = rtrim($rutaBase, '/\\') . DIRECTORY_SEPARATOR . $carpetaRelativa;
    }

    escribirLog("Ruta de destino final: $rutaDestino");



        // Verificar que la ruta de destino es válida y está dentro de Almacenamiento
        if (!file_exists($rutaDestino)) {
            // Intentar crear la carpeta si no existe
            escribirLog("Intentando crear ruta: $rutaDestino");

            if (!mkdir($rutaDestino, 0777, true)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No se pudo crear la carpeta de destino']);
                exit;
            }
        }
        
        // Verificar que la ruta está dentro de Almacenamiento
        if (strpos($rutaDestino, $rutaBase) !== 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'La carpeta de destino no es válida']);
            exit;
        }
    
    
    // Ruta completa donde se guardará el archivo
    $rutaCompleta = $rutaDestino . DIRECTORY_SEPARATOR . $fileName;
    
    // Verificar si ya existe un archivo con el mismo nombre
    if (file_exists($rutaCompleta)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Ya existe un archivo con el nombre '$fileName' en la carpeta de destino"
        ]);
        exit;
    }
    
    // Combinar todos los fragmentos en el archivo final
    $finalFile = fopen($rutaCompleta, 'wb');
    
    if ($finalFile === false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se pudo crear el archivo final']);
        exit;
    }
    
    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkPath = $fileDir . '/' . $i;
        $chunkContent = file_get_contents($chunkPath);
        fwrite($finalFile, $chunkContent);
        unlink($chunkPath); // Eliminar el fragmento después de usarlo
    }
    
    fclose($finalFile);
    
    // Eliminar el directorio temporal de fragmentos
    rmdir($fileDir);
    
    escribirLog("Archivo $fileName combinado y guardado correctamente en $rutaCompleta");
    
    // Obtener la ruta relativa para la redirección
    $rutaRelativa = str_replace($rutaBase, '', $rutaDestino);
    $rutaRelativa = ltrim($rutaRelativa, '/\\');
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "El archivo '$fileName' ha sido subido exitosamente",
        'fileName' => $fileName,
        'filePath' => $rutaCompleta,
        'redirectUrl' => empty($rutaRelativa) ? 
            "index.php?subida_exitosa=1" : 
            "index.php?carpeta=" . urlencode($rutaRelativa) . "&subida_exitosa=1"
    ]);
}