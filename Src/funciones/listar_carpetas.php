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

/**
 * Función para listar carpetas y subcarpetas recursivamente
 * @param string $directorio Directorio a listar
 * @param int $nivel Nivel de profundidad actual (para la indentación)
 * @return string HTML con la lista de carpetas y subcarpetas
 */
function listarCarpetasRecursivas($directorio, $nivel = 0) {
    if (!is_dir($directorio)) {
        return '<div class="alert alert-danger">El directorio no existe: ' . htmlspecialchars($directorio) . '</div>';
    }
    
    $resultado = '';
    $archivos = scandir($directorio);
    
    foreach ($archivos as $archivo) {
        if ($archivo != '.' && $archivo != '..') {
            $rutaCompleta = $directorio . '/' . $archivo;
            
            if (is_dir($rutaCompleta)) {
                // Calcular la indentación basada en el nivel
                $indentacion = str_repeat('    ', $nivel);
                $iconoFlecha = $nivel > 0 ? '↳ ' : '';
                
                // Obtener la ruta relativa desde la carpeta Almacenamiento
                $rutaBase = realpath(__DIR__ . '/../Almacenamiento');
                $rutaRelativa = str_replace($rutaBase, 'Almacenamiento', $rutaCompleta);
                $rutaRelativa = str_replace('\\', '/', $rutaRelativa);
                
                // Añadir la carpeta actual a la lista
                $resultado .= '<div class="carpeta-item" style="margin-left: ' . ($nivel * 20) . 'px;">';
                $resultado .= '<span class="carpeta-icono"><i class="fas fa-folder"></i></span>';
                $resultado .= '<span class="carpeta-nombre">' . $iconoFlecha . htmlspecialchars($archivo) . '</span>';
                $resultado .= '<span class="carpeta-acciones">';
                $resultado .= '<a href="?modulo=explorar&carpeta=' . urlencode($rutaRelativa) . '" class="btn btn-sm btn-primary">Explorar</a>';
                $resultado .= '</span>';
                $resultado .= '</div>';
                
                // Llamada recursiva para las subcarpetas
                $resultado .= listarCarpetasRecursivas($rutaCompleta, $nivel + 1);
            }
        }
    }
    
    return $resultado;
}

// Directorio base para listar
$directorioBase = __DIR__ . '/../Almacenamiento';

// Obtener el HTML con la lista de carpetas
$listaCarpetas = listarCarpetasRecursivas($directorioBase);

// Incluir estilos CSS
$estilos = '
<style>
    .carpetas-container {
        margin: 20px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        background-color: #f9f9f9;
    }
    .carpeta-item {
        padding: 8px 5px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }
    .carpeta-item:hover {
        background-color: #f0f0f0;
    }
    .carpeta-icono {
        margin-right: 10px;
        color: #ffc107;
    }
    .carpeta-nombre {
        flex-grow: 1;
    }
    .carpeta-acciones {
        margin-left: 10px;
    }
    .carpeta-acciones .btn {
        margin-left: 5px;
    }
</style>
';

// Incluir Font Awesome para los iconos
$fontAwesome = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorador de Carpetas - Sistema NASv2</title>
    <?php echo $fontAwesome; ?>
    <?php echo $estilos; ?>
</head>
<body>
    <div class="container">
        <h1>Explorador de Carpetas</h1>
        
        <div class="carpetas-container">
            <h2>Carpetas y Subcarpetas</h2>
            
            <?php if (empty($listaCarpetas)): ?>
                <div class="alert alert-info">No se encontraron carpetas.</div>
            <?php else: ?>
                <div class="carpeta-item">
                    <span class="carpeta-icono"><i class="fas fa-folder-open"></i></span>
                    <span class="carpeta-nombre">Almacenamiento (Raíz)</span>
                    <span class="carpeta-acciones">
                        <a href="?modulo=explorar&carpeta=Almacenamiento" class="btn btn-sm btn-primary">Explorar</a>
                    </span>
                </div>
                <?php echo $listaCarpetas; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
