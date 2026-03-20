<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de validación
require_once __DIR__ . '/validacion.php';

// Ruta para el archivo de log
$logFile = __DIR__ . '/../logs/crear_archivos.log';

// Función para escribir en el log
function escribirLog($mensaje, $archivo) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $mensaje\n";
    file_put_contents($archivo, $logMessage, FILE_APPEND);
}

// Crear directorio de logs si no existe
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Verificar si se han enviado los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_archivo']) && isset($_POST['extension']) && isset($_POST['carpeta_destino'])) {
    escribirLog("Iniciando proceso de creación de archivo", $logFile);
    
    // Obtener los datos del formulario
    $nombreArchivo = trim($_POST['nombre_archivo']);
    $extension = $_POST['extension'];
    $carpetaDestino = $_POST['carpeta_destino'];
    $contenido = isset($_POST['contenido']) ? $_POST['contenido'] : '';
    
    // Validar el nombre del archivo (eliminar caracteres no permitidos)
    $nombreArchivo = preg_replace('/[\\\\\/\:\*\?\"\<\>\|]/', '', $nombreArchivo);
    
    if (empty($nombreArchivo)) {
        escribirLog("ERROR: Nombre de archivo vacío o inválido", $logFile);
        $_SESSION['mensaje'] = "Error: El nombre del archivo no puede estar vacío o contener caracteres no permitidos.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../index.php?modulo=crear_archivo");
        exit;
    }
    
    // Construir el nombre completo del archivo con su extensión
    $nombreCompletoArchivo = $nombreArchivo . $extension;
    escribirLog("Nombre de archivo: $nombreCompletoArchivo", $logFile);
    
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
                header("Location: ../index.php?modulo=crear_archivo");
                exit;
            }
            escribirLog("Se creó la carpeta de destino: $rutaDestino", $logFile);
        }
        
        // Verificar que la ruta está dentro de Almacenamiento
        if (strpos($rutaDestino, $rutaBase) !== 0) {
            escribirLog("ERROR: Ruta de destino fuera de Almacenamiento: $carpetaDestino, Ruta calculada: $rutaDestino", $logFile);
            $_SESSION['mensaje'] = "Error: La carpeta de destino no es válida.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../index.php?modulo=crear_archivo");
            exit;
        }
    }
    
    escribirLog("Ruta de destino: $rutaDestino", $logFile);
    
    // Ruta completa donde se guardará el archivo
    $rutaCompleta = $rutaDestino . DIRECTORY_SEPARATOR . $nombreCompletoArchivo;
    
    escribirLog("Intentando crear archivo: $nombreCompletoArchivo en $rutaCompleta", $logFile);
    
    // Verificar si ya existe un archivo con el mismo nombre
    if (file_exists($rutaCompleta)) {
        escribirLog("ERROR: Ya existe un archivo con el nombre: $nombreCompletoArchivo en $rutaDestino", $logFile);
        $_SESSION['mensaje'] = "Error: Ya existe un archivo con el nombre '$nombreCompletoArchivo' en la carpeta de destino.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../index.php?modulo=crear_archivo");
        exit;
    }
    
    // Crear el archivo
    $resultado = file_put_contents($rutaCompleta, $contenido);
    
    if ($resultado !== false) {
        escribirLog("Archivo creado exitosamente: $nombreCompletoArchivo en $rutaCompleta", $logFile);
        
        // Crear mensaje de éxito
        $_SESSION['mensaje'] = "El archivo '$nombreCompletoArchivo' ha sido creado exitosamente.";
        $_SESSION['tipo_mensaje'] = "exito";
        
        // Redirigir de vuelta a la página principal o a la carpeta donde se creó el archivo
        if ($carpetaDestino === 'Almacenamiento') {
            header("Location: ../index.php?archivo_creado=1");
        } else {
            // Obtener la ruta relativa para la redirección
            $rutaRelativa = str_replace($rutaBase, '', $rutaDestino);
            $rutaRelativa = ltrim($rutaRelativa, '/\\');
            if (empty($rutaRelativa)) {
                header("Location: ../index.php?archivo_creado=1");
            } else {
                header("Location: ../index.php?carpeta=" . urlencode($rutaRelativa) . "&archivo_creado=1");
            }
        }
        exit;
    } else {
        escribirLog("ERROR: No se pudo crear el archivo: $nombreCompletoArchivo en $rutaCompleta", $logFile);
        $_SESSION['mensaje'] = "Error: No se pudo crear el archivo. Verifique los permisos de escritura.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../index.php?modulo=crear_archivo");
        exit;
    }
} else {
    // Si no se enviaron los datos correctamente
    escribirLog("ERROR: Datos del formulario incompletos o método incorrecto", $logFile);
    $_SESSION['mensaje'] = "Error: Datos del formulario incompletos o método incorrecto.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../index.php?modulo=crear_archivo");
    exit;
}
?>
