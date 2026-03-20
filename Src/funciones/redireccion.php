<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Obtener la carpeta de origen desde la URL
$carpetaOrigen = isset($_GET['carpeta']) ? $_GET['carpeta'] : '';

// Configurar mensaje si existe
if (isset($_GET['mensaje'])) {
    $_SESSION['mensaje'] = $_GET['mensaje'];
    $_SESSION['tipo_mensaje'] = isset($_GET['tipo']) ? $_GET['tipo'] : 'success';
}

// Redirigir a la carpeta de origen si se proporcionó
if (!empty($carpetaOrigen)) {
    header('Location: /Sistema-NASv3/Src/index.php?carpeta=' . urlencode($carpetaOrigen));
} else {
    // Si no hay carpeta de origen o está vacía, ir a la página principal
    header('Location: /Sistema-NASv3/Src/index.php');
}
exit;
?>
