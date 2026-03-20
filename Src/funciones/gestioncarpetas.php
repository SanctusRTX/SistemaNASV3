<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../../index.php');
    exit;
}

// Incluir el controlador usando una ruta absoluta
$rutaControlador = __DIR__ . '/../controladores/controlador_copiarmover.php';
if (file_exists($rutaControlador)) {
    include $rutaControlador; // Incluye las funciones copiarCarpeta y moverCarpeta
} else {
    die("Error: No se pudo encontrar el controlador en: $rutaControlador");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener la ruta base de Almacenamiento usando una ruta absoluta
    $carpetaRaiz = realpath(__DIR__ . '/../Almacenamiento');
    
    // Depuración: mostrar la ruta base
    echo "<!-- Debug: Ruta base = $carpetaRaiz -->";
    
    // Captura y sanitiza las entradas del formulario
    $carpetaOrigen = htmlspecialchars(trim($_POST['carpetaOrigen']));
    $carpetaDestino = htmlspecialchars(trim($_POST['carpetaDestino']));
    $accion = htmlspecialchars(trim($_POST['accion']));
    
    // Mensaje para redirección
    $mensaje = '';
    $exito = false;
    
    // Depuración: mostrar las carpetas seleccionadas
    echo "<!-- Debug: Carpeta origen = $carpetaOrigen, Carpeta destino = $carpetaDestino -->";
    
    // Construir las rutas absolutas dentro de la carpeta raíz
    if ($carpetaOrigen === 'Almacenamiento') {
        $rutaOrigen = $carpetaRaiz;
    } else {
        // Extraer la parte de la ruta después de 'Almacenamiento/'
        $subcarpetaOrigen = preg_replace('#^Almacenamiento[/\\]?#', '', $carpetaOrigen);
        $rutaOrigen = $carpetaRaiz . DIRECTORY_SEPARATOR . $subcarpetaOrigen;
        // Asegurarse de que la ruta existe
        if (is_dir($rutaOrigen)) {
            $rutaOrigen = realpath($rutaOrigen);
        }
    }
    
    if ($carpetaDestino === 'Almacenamiento') {
        $rutaDestino = $carpetaRaiz;
    } else {
        // Extraer la parte de la ruta después de 'Almacenamiento/'
        $subcarpetaDestino = preg_replace('#^Almacenamiento[/\\]?#', '', $carpetaDestino);
        $rutaDestino = $carpetaRaiz . DIRECTORY_SEPARATOR . $subcarpetaDestino;
    }
    
    // Depuración: mostrar las rutas construidas
    echo "<!-- Debug: Ruta origen = $rutaOrigen, Ruta destino = $rutaDestino -->";

    
    // Verificar que las rutas sean válidas
    if (!is_dir($carpetaRaiz)) {
        $mensaje = "Error: La carpeta raíz no existe o no es accesible: $carpetaRaiz";
    } elseif (!is_dir($rutaOrigen)) {
        $mensaje = "Error: La carpeta de origen no existe: $rutaOrigen";
    } elseif (!file_exists($rutaDestino) && !mkdir($rutaDestino, 0755, true)) {
        $mensaje = "Error: No se pudo crear la carpeta de destino: $rutaDestino";
    } else {
        // Procesar la acción seleccionada
        if ($accion === 'copiar') {
            if (copiarCarpeta($rutaOrigen, $rutaDestino)) {
                $mensaje = "Carpeta copiada correctamente.";
                $exito = true;
            } else {
                $mensaje = "Error al copiar la carpeta. Por favor, inténtelo de nuevo.";
            }
        } elseif ($accion === 'mover') {
            if (moverCarpeta($rutaOrigen, $rutaDestino)) {
                $mensaje = "Carpeta movida correctamente.";
                $exito = true;
            } else {
                $mensaje = "Error al mover la carpeta. Por favor, inténtelo de nuevo.";
            }
        } else {
            $mensaje = "Acción no válida.";
        }
    }
    
    // Redireccionar al usuario con un mensaje
    $tipoMensaje = $exito ? 'exito' : 'error';
    header("Location: /Sistema-NASv3/Src/index.php?modulo=copiarmover&mensaje=$mensaje&tipo=$tipoMensaje");
    exit;
}
?>