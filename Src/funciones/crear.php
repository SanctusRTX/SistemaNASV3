<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $directorioRaiz = __DIR__ . '/../Almacenamiento';

    // Normalización de entrada del formulario
    $carpetaBaseSeleccionada = htmlspecialchars(trim($_POST['directorio']));
    $nombreCarpeta = htmlspecialchars(trim($_POST['nombreCarpeta']));
    
    // Verificar si estamos en la raíz de Almacenamiento o en una subcarpeta
    if ($carpetaBaseSeleccionada === 'Almacenamiento') {
        // Si es la carpeta raíz, dejamos la ruta vacía para usar directamente $directorioRaiz
        $carpetaBaseSeleccionada = '';
    } else {
        // Si es una subcarpeta, normalizamos la ruta
        $carpetaBaseSeleccionada = str_replace('Almacenamiento', '', $carpetaBaseSeleccionada);
        $carpetaBaseSeleccionada = trim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $carpetaBaseSeleccionada));
    }

    // Validación de campos
    if (empty($nombreCarpeta)) {
        die("Error: Debes escribir un nombre válido para la nueva carpeta.");
    }

    // Construcción de la ruta final
    $rutaBaseSeleccionada = rtrim($directorioRaiz, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($carpetaBaseSeleccionada, DIRECTORY_SEPARATOR);

    // Depuración de la ruta
    echo "Debug - Ruta Base Seleccionada: " . $rutaBaseSeleccionada . "<br>";

    // Validación de la carpeta base
    if (!is_dir($rutaBaseSeleccionada)) {
        die("Error: La carpeta base seleccionada no existe o no es accesible: " . $rutaBaseSeleccionada);
    }

    // Construir la ruta de la nueva carpeta
    $rutaCompleta = $rutaBaseSeleccionada . DIRECTORY_SEPARATOR . $nombreCarpeta;

    // Verificar si la carpeta ya existe
    if (is_dir($rutaCompleta)) {
        die("Error: La carpeta '$nombreCarpeta' ya existe en la ubicación seleccionada.");
    }

    // Intentar crear la carpeta
    if (mkdir($rutaCompleta, 0755, true)) {
        header("Location: ../index.php");
        exit();
    } else {
        echo "Error: No se pudo crear la carpeta. Verifica los permisos del servidor.";
    }
}
?>
