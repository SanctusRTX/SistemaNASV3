<?php
// Función para copiar una carpeta y su contenido

function copiarCarpeta($carpetaOrigen, $carpetaDestino) {
    // Verificar si la carpeta de origen existe
    if (!is_dir($carpetaOrigen)) {
        echo "Error: La carpeta de origen no existe ($carpetaOrigen).\n";
        return false;
    }
    
    // Verificar que no se esté intentando copiar una carpeta dentro de sí misma
    if (strpos($carpetaDestino, $carpetaOrigen . DIRECTORY_SEPARATOR) === 0) {
        echo "Error: No se puede copiar una carpeta dentro de sí misma.\n";
        return false;
    }
    
    // Obtener el nombre de la carpeta de origen
    $nombreCarpeta = basename($carpetaOrigen);
    $destinoFinal = $carpetaDestino . DIRECTORY_SEPARATOR . $nombreCarpeta;
    
    // Crear la carpeta de destino si no existe
    if (!is_dir($carpetaDestino)) {
        if (!mkdir($carpetaDestino, 0755, true)) {
            echo "Error: No se pudo crear la carpeta de destino ($carpetaDestino).\n";
            return false;
        }
    }
    
    // Crear la carpeta con el nombre de la carpeta origen en el destino
    if (!is_dir($destinoFinal)) {
        if (!mkdir($destinoFinal, 0755, true)) {
            echo "Error: No se pudo crear la carpeta de destino ($destinoFinal).\n";
            return false;
        }
    }
    
    // Abrir la carpeta de origen
    $dir = opendir($carpetaOrigen);
    if (!$dir) {
        echo "Error: No se pudo abrir la carpeta de origen.\n";
        return false;
    }
    
    // Recorrer archivos y carpetas en el directorio
    while (($file = readdir($dir)) !== false) {
        if ($file !== '.' && $file !== '..') {
            $sourcePath = $carpetaOrigen . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $destinoFinal . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($sourcePath)) {
                // Si es un directorio, crear el directorio en el destino y llamar recursivamente
                if (!is_dir($destinationPath) && !mkdir($destinationPath, 0755, true)) {
                    echo "Error: No se pudo crear el directorio $destinationPath.\n";
                    closedir($dir);
                    return false;
                }
                
                // Llamada recursiva para copiar el contenido de la subcarpeta
                if (!copiarContenidoCarpeta($sourcePath, $destinationPath)) {
                    closedir($dir);
                    return false;
                }
            } else {
                // Copiar archivos
                if (!copy($sourcePath, $destinationPath)) {
                    echo "Error al copiar el archivo: $sourcePath.\n";
                    closedir($dir);
                    return false;
                }
            }
        }
    }
    
    closedir($dir); // Cerrar el directorio
    echo "Copia completada de $carpetaOrigen a $destinoFinal.\n";
    return true;
}

// Función auxiliar para copiar el contenido de una carpeta sin crear una nueva carpeta con el mismo nombre
function copiarContenidoCarpeta($carpetaOrigen, $carpetaDestino) {
    // Verificar si la carpeta de origen existe
    if (!is_dir($carpetaOrigen)) {
        echo "Error: La carpeta de origen no existe ($carpetaOrigen).\n";
        return false;
    }
    
    // Crear la carpeta de destino si no existe
    if (!is_dir($carpetaDestino)) {
        if (!mkdir($carpetaDestino, 0755, true)) {
            echo "Error: No se pudo crear la carpeta de destino ($carpetaDestino).\n";
            return false;
        }
    }
    
    // Abrir la carpeta de origen
    $dir = opendir($carpetaOrigen);
    if (!$dir) {
        echo "Error: No se pudo abrir la carpeta de origen.\n";
        return false;
    }
    
    // Recorrer archivos y carpetas en el directorio
    while (($file = readdir($dir)) !== false) {
        if ($file !== '.' && $file !== '..') {
            $sourcePath = $carpetaOrigen . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $carpetaDestino . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($sourcePath)) {
                // Si es un directorio, crear el directorio en el destino y llamar recursivamente
                if (!is_dir($destinationPath) && !mkdir($destinationPath, 0755, true)) {
                    echo "Error: No se pudo crear el directorio $destinationPath.\n";
                    closedir($dir);
                    return false;
                }
                
                // Llamada recursiva para subcarpetas
                if (!copiarContenidoCarpeta($sourcePath, $destinationPath)) {
                    closedir($dir);
                    return false;
                }
            } else {
                // Copiar archivos
                if (!copy($sourcePath, $destinationPath)) {
                    echo "Error al copiar el archivo: $sourcePath.\n";
                    closedir($dir);
                    return false;
                }
            }
        }
    }
    
    closedir($dir); // Cerrar el directorio
    return true;
}

function obtenerOpcionesCarpetas($directorio) {
    // Definir la ruta base de Almacenamiento usando una ruta absoluta
    $rutaBase = 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\Almacenamiento';
    
    // Verificar si la ruta base existe
    if (!$rutaBase || !is_dir($rutaBase)) {
        echo "<option value=''>Error: Directorio base no encontrado</option>";
        return;
    }
    
    // Depuración: mostrar la ruta base encontrada
    // echo "<!-- Debug: Ruta base = $rutaBase -->";
    
    // Construir la ruta completa del directorio
    $rutaCompleta = $rutaBase;
    if ($directorio !== 'Almacenamiento') {
        $rutaCompleta = $rutaBase . DIRECTORY_SEPARATOR . ltrim($directorio, '/\\');
    }
    
    // Depuración: mostrar la ruta completa
    // echo "<!-- Debug: Ruta completa = $rutaCompleta -->";
    
    // Asegúrate de que la carpeta exista
    if (!is_dir($rutaCompleta)) {
        echo "<option value=''>Directorio no encontrado: $rutaCompleta</option>";
        return;
    }
    
    // Añadir la carpeta raíz como opción
    if ($directorio === 'Almacenamiento') {
        echo "<option value='Almacenamiento'>Almacenamiento (Raíz)</option>";
    }
    
    // Obtener todas las subcarpetas
    $carpetas = array_filter(glob($rutaCompleta . '/*'), 'is_dir');
    
    // Depuración: mostrar las carpetas encontradas
    // echo "<!-- Debug: Carpetas encontradas = " . count($carpetas) . " -->";
    
    // Si no hay subcarpetas, mostrar un mensaje
    if (empty($carpetas)) {
        echo "<option value='' disabled>No hay subcarpetas</option>";
        return;
    }
    
    // Generar opciones para cada carpeta encontrada
    foreach ($carpetas as $carpeta) {
        $nombreCarpeta = basename($carpeta);
        $rutaRelativa = 'Almacenamiento/' . substr($carpeta, strlen($rutaBase) + 1);
        echo "<option value='$rutaRelativa'>$nombreCarpeta</option>";
    }
}

function moverCarpeta($source, $destination) {
    // Verificar si la carpeta de origen existe
    if (!is_dir($source)) {
        echo "Error: La carpeta de origen no existe ($source).\n";
        return false;
    }
    
    // Verificar que no se esté intentando mover una carpeta dentro de sí misma
    if (strpos($destination, $source . DIRECTORY_SEPARATOR) === 0) {
        echo "Error: No se puede mover una carpeta dentro de sí misma.\n";
        return false;
    }
    
    // Verificar si ya existe una carpeta con el mismo nombre en el destino
    $nombreCarpeta = basename($source);
    $rutaDestinoFinal = $destination . DIRECTORY_SEPARATOR . $nombreCarpeta;
    
    if (is_dir($rutaDestinoFinal)) {
        echo "Error: Ya existe una carpeta con el nombre '$nombreCarpeta' en el destino.\n";
        return false;
    }
    
    // Crear el directorio de destino si no existe
    $directorioDestino = dirname($destination);
    if (!is_dir($directorioDestino) && !mkdir($directorioDestino, 0755, true)) {
        echo "Error: No se pudo crear el directorio de destino.\n";
        return false;
    }
    
    // Usar rename() para mover la carpeta
    if (!rename($source, $destination)) {
        echo "Error: No se pudo mover la carpeta de $source a $destination.\n";
        return false;
    }
    
    echo "La carpeta se movió correctamente de $source a $destination.\n";
    return true;
}
?>