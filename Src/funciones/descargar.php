<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de roles
require_once __DIR__ . '/../funciones/roles_simple.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../../index.php');
    exit;
}

// Verificar si se ha proporcionado una ruta de archivo
if (!isset($_GET['archivo'])) {
    header('Location: ../index.php?error=no_archivo');
    exit;
}

// Obtener la ruta del archivo
$rutaArchivo = $_GET['archivo'];

// Construir la ruta completa al archivo
$rutaBase = realpath(__DIR__ . '/../Almacenamiento');
$rutaCompleta = realpath($rutaBase . DIRECTORY_SEPARATOR . ltrim($rutaArchivo, '/\\'));

// Verificar que el archivo existe y está dentro del directorio de almacenamiento
if (!$rutaCompleta || !file_exists($rutaCompleta) || !is_file($rutaCompleta) || strpos($rutaCompleta, $rutaBase) !== 0) {
    header('Location: ../index.php?error=archivo_no_encontrado');
    exit;
}

// Obtener información del archivo
$nombreArchivo = basename($rutaCompleta);
$tamanoArchivo = filesize($rutaCompleta);
$tipoArchivo = mime_content_type($rutaCompleta);

// Si no se puede determinar el tipo MIME, usar un tipo genérico
if (!$tipoArchivo) {
    $tipoArchivo = 'application/octet-stream';
}

// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

// Configurar las cabeceras para la descarga
header('Content-Description: File Transfer');
header('Content-Type: ' . $tipoArchivo);
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $tamanoArchivo);

// Leer y enviar el archivo
readfile($rutaCompleta);
exit;
?>
