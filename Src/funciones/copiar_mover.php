<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /Sistema-NASv3/index.php');
    exit;
}

// Función para copiar una carpeta y su contenido recursivamente
function copiarCarpetaRecursiva($origen, $destino) {
    // Crear la carpeta de destino si no existe
    if (!file_exists($destino)) {
        mkdir($destino, 0755, true);
    }
    
    // Abrir el directorio de origen
    $dir = opendir($origen);
    
    // Copiar cada archivo y subcarpeta
    while (($archivo = readdir($dir)) !== false) {
        if ($archivo != '.' && $archivo != '..') {
            $rutaOrigen = $origen . '/' . $archivo;
            $rutaDestino = $destino . '/' . $archivo;
            
            if (is_dir($rutaOrigen)) {
                // Si es un directorio, llamada recursiva
                copiarCarpetaRecursiva($rutaOrigen, $rutaDestino);
            } else {
                // Si es un archivo, copiarlo
                copy($rutaOrigen, $rutaDestino);
            }
        }
    }
    
    // Cerrar el directorio
    closedir($dir);
    
    return true;
}

// Función para mover una carpeta
function moverCarpeta($origen, $destino) {
    // Intentar mover directamente (más eficiente)
    if (@rename($origen, $destino)) {
        return true;
    }
    
    // Si rename falla, copiar y luego eliminar
    if (copiarCarpetaRecursiva($origen, $destino)) {
        // Eliminar la carpeta original y su contenido
        eliminarCarpetaRecursiva($origen);
        return true;
    }
    
    return false;
}

// Función para eliminar una carpeta y su contenido recursivamente
function eliminarCarpetaRecursiva($carpeta) {
    if (!is_dir($carpeta)) {
        return false;
    }
    
    $archivos = array_diff(scandir($carpeta), array('.', '..'));
    
    foreach ($archivos as $archivo) {
        $ruta = $carpeta . '/' . $archivo;
        
        if (is_dir($ruta)) {
            eliminarCarpetaRecursiva($ruta);
        } else {
            unlink($ruta);
        }
    }
    
    return rmdir($carpeta);
}

// Procesar la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Definir la ruta base de Almacenamiento
    $rutaBase = __DIR__ . '/../Almacenamiento';
    
    // Capturar y sanitizar las entradas del formulario
    $carpetaOrigen = isset($_POST['carpetaOrigen']) ? trim($_POST['carpetaOrigen']) : '';
    $carpetaDestino = isset($_POST['carpetaDestino']) ? trim($_POST['carpetaDestino']) : '';
    $accion = isset($_POST['accion']) ? trim($_POST['accion']) : '';
    
    // Mensaje para redirección
    $mensaje = '';
    $exito = false;
    
    // Construir las rutas completas
    $rutaOrigen = '';
    $rutaDestino = '';
    
    // Extraer la parte después de "Almacenamiento/"
    if ($carpetaOrigen === 'Almacenamiento') {
        $rutaOrigen = $rutaBase;
    } else {
        $subcarpetaOrigen = str_replace('Almacenamiento/', '', $carpetaOrigen);
        $rutaOrigen = $rutaBase . '/' . $subcarpetaOrigen;
    }
    
    if ($carpetaDestino === 'Almacenamiento') {
        $rutaDestino = $rutaBase;
    } else {
        $subcarpetaDestino = str_replace('Almacenamiento/', '', $carpetaDestino);
        $rutaDestino = $rutaBase . '/' . $subcarpetaDestino;
    }
    
    // Si el origen y destino son iguales, mostrar error
    if ($rutaOrigen === $rutaDestino) {
        $mensaje = "Error: La carpeta de origen y destino no pueden ser la misma.";
    }
    // Verificar que la carpeta de origen exista
    elseif (!is_dir($rutaOrigen)) {
        $mensaje = "Error: La carpeta de origen no existe: " . htmlspecialchars($carpetaOrigen);
    }
    // Verificar que la carpeta de destino exista o se pueda crear
    elseif (!is_dir($rutaDestino) && !mkdir($rutaDestino, 0755, true)) {
        $mensaje = "Error: No se pudo crear la carpeta de destino: " . htmlspecialchars($carpetaDestino);
    }
    else {
        // Obtener el nombre de la carpeta de origen
        $nombreCarpeta = basename($rutaOrigen);
        
        // Construir la ruta de destino final incluyendo el nombre de la carpeta
        $rutaDestinoFinal = $rutaDestino . '/' . $nombreCarpeta;
        
        // Verificar si ya existe una carpeta con el mismo nombre en el destino
        if (is_dir($rutaDestinoFinal)) {
            $mensaje = "Error: Ya existe una carpeta con el nombre '$nombreCarpeta' en la carpeta de destino.";
        }
        else {
            // Procesar la acción seleccionada
            if ($accion === 'copiar') {
                if (copiarCarpetaRecursiva($rutaOrigen, $rutaDestinoFinal)) {
                    $mensaje = "La carpeta '$nombreCarpeta' se ha copiado correctamente.";
                    $exito = true;
                } else {
                    $mensaje = "Error al copiar la carpeta. Por favor, inténtelo de nuevo.";
                }
            } 
            elseif ($accion === 'mover') {
                if (moverCarpeta($rutaOrigen, $rutaDestinoFinal)) {
                    $mensaje = "La carpeta '$nombreCarpeta' se ha movido correctamente.";
                    $exito = true;
                } else {
                    $mensaje = "Error al mover la carpeta. Por favor, inténtelo de nuevo.";
                }
            } 
            else {
                $mensaje = "Acción no válida.";
            }
        }
    }
    
    // Redireccionar al usuario con un mensaje
    $tipoMensaje = $exito ? 'exito' : 'error';
    header("Location: /Sistema-NASv3/Src/index.php?modulo=copiarmover&mensaje=" . urlencode($mensaje) . "&tipo=$tipoMensaje");
    exit;
}
?>
