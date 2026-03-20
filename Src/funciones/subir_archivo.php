<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de validación
require_once __DIR__ . '/validacion.php';

// Ruta para el archivo de log
$logFile = __DIR__ . '/../logs/subida_archivos.log';

// Función para escribir en el log
function escribirLog($mensaje, $archivo) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $mensaje\n";
    file_put_contents($archivo, $logMessage, FILE_APPEND);
}

// Verificar si se ha enviado un archivo
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    escribirLog("Iniciando proceso de subida de archivo", $logFile);
    
    // Obtener información del archivo
    $nombreArchivo = $_FILES['archivo']['name'];
    $archivoTemporal = $_FILES['archivo']['tmp_name'];
    $tamanoArchivo = $_FILES['archivo']['size'];
    
    // Obtener la carpeta de destino
    $carpetaDestino = isset($_POST['carpeta_destino']) ? $_POST['carpeta_destino'] : 'Almacenamiento';
    
    // Construir la ruta completa de destino
    $rutaBase = realpath(__DIR__ . '/../Almacenamiento');
    escribirLog("Ruta base: $rutaBase", $logFile);
    
    // Si la carpeta destino es 'Almacenamiento', usar la raíz
    if ($carpetaDestino === 'Almacenamiento') {
        $rutaDestino = $rutaBase;
    } else {
        // Asegurarse de que la ruta esté dentro de Almacenamiento
        $carpetaRelativa = ltrim(str_replace('Almacenamiento/', '', $carpetaDestino), '/\\');
        $rutaDestino = $rutaBase . DIRECTORY_SEPARATOR . $carpetaRelativa;
        escribirLog("Carpeta relativa: $carpetaRelativa", $logFile);
        
        // Verificar que la ruta de destino es válida y está dentro de Almacenamiento
        if (!file_exists($rutaDestino)) {
            escribirLog("ADVERTENCIA: La carpeta de destino no existe: $rutaDestino", $logFile);
            // Intentar crear la carpeta si no existe
            if (!mkdir($rutaDestino, 0777, true)) {
                escribirLog("ERROR: No se pudo crear la carpeta de destino: $rutaDestino", $logFile);
                $_SESSION['mensaje'] = "Error: No se pudo crear la carpeta de destino.";
                $_SESSION['tipo_mensaje'] = "error";
                header("Location: ../index.php?modulo=subir");
                exit;
            }
            escribirLog("Se creó la carpeta de destino: $rutaDestino", $logFile);
        }
        
        // Verificar que la ruta está dentro de Almacenamiento
        if (strpos($rutaDestino, $rutaBase) !== 0) {
            escribirLog("ERROR: Ruta de destino fuera de Almacenamiento: $carpetaDestino, Ruta calculada: $rutaDestino", $logFile);
            $_SESSION['mensaje'] = "Error: La carpeta de destino no es válida.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../index.php?modulo=subir");
            exit;
        }
    }
    
    escribirLog("Ruta de destino: $rutaDestino", $logFile);
    
    // Ruta completa donde se guardará el archivo
    $rutaCompleta = $rutaDestino . DIRECTORY_SEPARATOR . $nombreArchivo;
    
    escribirLog("Intentando subir archivo: $nombreArchivo a $rutaCompleta", $logFile);
    
    // Verificar si ya existe un archivo con el mismo nombre
    if (file_exists($rutaCompleta)) {
        escribirLog("ERROR: Ya existe un archivo con el nombre: $nombreArchivo en $rutaDestino", $logFile);
        $_SESSION['mensaje'] = "Error: Ya existe un archivo con el nombre '$nombreArchivo' en la carpeta de destino.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../index.php?modulo=subir");
        exit;
    }
    
    // Mover el archivo subido a la carpeta de destino
    if (move_uploaded_file($archivoTemporal, $rutaCompleta)) {
        escribirLog("Archivo subido exitosamente: $nombreArchivo a $rutaCompleta", $logFile);
        
        // Crear mensaje de éxito
        $_SESSION['mensaje'] = "El archivo '$nombreArchivo' ha sido subido exitosamente.";
        $_SESSION['tipo_mensaje'] = "exito";
        
        // Redirigir de vuelta a la página principal o a la carpeta donde se subió el archivo
        if ($carpetaDestino === 'Almacenamiento') {
            header("Location: ../index.php?subida_exitosa=1");
        } else {
            // Obtener la ruta relativa para la redirección
            $rutaRelativa = str_replace($rutaBase, '', $rutaDestino);
            $rutaRelativa = ltrim($rutaRelativa, '/\\');
            if (empty($rutaRelativa)) {
                header("Location: ../index.php?subida_exitosa=1");
            } else {
                header("Location: ../index.php?carpeta=" . urlencode($rutaRelativa) . "&subida_exitosa=1");
            }
        }
        exit;
    } else {
        escribirLog("ERROR: No se pudo mover el archivo subido a la carpeta de destino", $logFile);
        $_SESSION['mensaje'] = "Error: No se pudo subir el archivo. Por favor, inténtelo de nuevo.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../index.php?modulo=subir");
        exit;
    }
} else {
    // Si no se ha enviado un archivo o hay un error
    $errorMensaje = "Error desconocido al subir el archivo.";
    
    if (isset($_FILES['archivo'])) {
        switch ($_FILES['archivo']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMensaje = "El archivo es demasiado grande. El tamaño máximo permitido es de 50MB.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMensaje = "El archivo se subió parcialmente. Por favor, inténtelo de nuevo.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMensaje = "No se seleccionó ningún archivo para subir.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMensaje = "Falta la carpeta temporal del servidor. Contacte al administrador.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMensaje = "No se pudo escribir el archivo en el disco. Contacte al administrador.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMensaje = "Una extensión de PHP detuvo la subida del archivo.";
                break;
        }
    }
    
    escribirLog("ERROR: $errorMensaje", $logFile);
    $_SESSION['mensaje'] = "Error: $errorMensaje";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../index.php?modulo=subir");
    exit;
}
?>
