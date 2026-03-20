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

// Definir la ruta base de almacenamiento
$rutaBase = realpath(__DIR__ . '/../Almacenamiento');

// Crear archivo de log para depuración
$logFile = __DIR__ . '/debug_renombrar.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);

// Función para escribir en el log
function escribirLog($mensaje, $logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $mensaje . "\n", FILE_APPEND);
}

// Función para validar que la ruta esté dentro del directorio de almacenamiento
function validarRuta($ruta, $rutaBase, $logFile) {
    $rutaAbsoluta = realpath($ruta);
    escribirLog("Validando ruta: {$ruta}", $logFile);
    escribirLog("Ruta absoluta: {$rutaAbsoluta}", $logFile);
    escribirLog("Ruta base: {$rutaBase}", $logFile);
    
    if (!$rutaAbsoluta) {
        escribirLog("La ruta no existe o no es accesible", $logFile);
        return false;
    }
    
    $resultado = strpos($rutaAbsoluta, $rutaBase) === 0;
    escribirLog("¿Está dentro de la ruta base? " . ($resultado ? "Sí" : "No"), $logFile);
    return $resultado;
}

// Función para renombrar carpetas (función original que ya funciona)
function renombrar($rutaOriginal, $nuevoNombre, $rutaBase, $logFile) {
    escribirLog("Intentando renombrar: {$rutaOriginal} a {$nuevoNombre}", $logFile);
    
    // Validar que la ruta original esté dentro del directorio de almacenamiento
    if (!validarRuta($rutaOriginal, $rutaBase, $logFile)) {
        escribirLog("Error: La ruta no es válida o está fuera de la carpeta raíz", $logFile);
        return [
            'success' => false,
            'message' => 'La ruta original no es válida o está fuera de la carpeta raíz.'
        ];
    }
    
    // Verificar si es la carpeta principal Almacenamiento
    if (realpath($rutaOriginal) === $rutaBase) {
        escribirLog("Error: Intento de renombrar la carpeta principal Almacenamiento", $logFile);
        return [
            'success' => false,
            'message' => 'No se puede renombrar la carpeta principal Almacenamiento.'
        ];
    }
    
    // Obtener el directorio padre
    $dirPadre = dirname($rutaOriginal);
    escribirLog("Directorio padre: {$dirPadre}", $logFile);
    
    // Construir la nueva ruta
    $nuevaRuta = $dirPadre . DIRECTORY_SEPARATOR . $nuevoNombre;
    escribirLog("Nueva ruta: {$nuevaRuta}", $logFile);
    
    // Verificar que el nuevo nombre no exista ya
    if (file_exists($nuevaRuta)) {
        escribirLog("Error: Ya existe un archivo o carpeta con ese nombre", $logFile);
        return [
            'success' => false,
            'message' => 'Ya existe un archivo o carpeta con ese nombre en la misma ubicación.'
        ];
    }
    
    // Intentar renombrar
    $resultado = rename($rutaOriginal, $nuevaRuta);
    escribirLog("Resultado de rename(): " . ($resultado ? "Éxito" : "Fallo"), $logFile);
    
    if ($resultado) {
        escribirLog("Renombrado exitosamente", $logFile);
        return [
            'success' => true,
            'message' => 'Renombrado exitosamente.',
            'nuevaRuta' => str_replace($rutaBase, 'Almacenamiento', $nuevaRuta)
        ];
    } else {
        escribirLog("Error al renombrar. Verifica los permisos.", $logFile);
        return [
            'success' => false,
            'message' => 'Error al renombrar. Verifica los permisos.'
        ];
    }
}

// Nueva función específica para renombrar archivos
function renombrarArchivo($rutaOriginal, $nuevoNombre, $rutaBase, $logFile) {
    escribirLog("Intentando renombrar archivo: {$rutaOriginal} a {$nuevoNombre}", $logFile);
    
    // Validar que la ruta original esté dentro del directorio de almacenamiento
    if (!validarRuta($rutaOriginal, $rutaBase, $logFile)) {
        escribirLog("Error: La ruta del archivo no es válida o está fuera de la carpeta raíz", $logFile);
        return [
            'success' => false,
            'message' => 'La ruta del archivo no es válida o está fuera de la carpeta raíz.'
        ];
    }
    
    // Verificar que sea un archivo y no un directorio
    if (is_dir($rutaOriginal)) {
        escribirLog("Error: La ruta especificada es un directorio, no un archivo", $logFile);
        return [
            'success' => false,
            'message' => 'La ruta especificada es un directorio, no un archivo.'
        ];
    }
    
    // Obtener el directorio padre y la extensión del archivo original
    $dirPadre = dirname($rutaOriginal);
    $extension = pathinfo($rutaOriginal, PATHINFO_EXTENSION);
    escribirLog("Directorio padre: {$dirPadre}", $logFile);
    escribirLog("Extensión del archivo: {$extension}", $logFile);
    
    // Asegurarse de que el nuevo nombre tenga la misma extensión
    if (!empty($extension)) {
        // Verificar si el nuevo nombre ya incluye la extensión
        if (strtolower(pathinfo($nuevoNombre, PATHINFO_EXTENSION)) !== strtolower($extension)) {
            $nuevoNombre .= '.' . $extension;
            escribirLog("Añadida extensión al nuevo nombre: {$nuevoNombre}", $logFile);
        }
    }
    
    // Construir la nueva ruta
    $nuevaRuta = $dirPadre . DIRECTORY_SEPARATOR . $nuevoNombre;
    escribirLog("Nueva ruta del archivo: {$nuevaRuta}", $logFile);
    
    // Verificar que el nuevo nombre no exista ya
    if (file_exists($nuevaRuta)) {
        escribirLog("Error: Ya existe un archivo con ese nombre", $logFile);
        return [
            'success' => false,
            'message' => 'Ya existe un archivo con ese nombre en la misma ubicación.'
        ];
    }
    
    // Intentar renombrar el archivo
    $resultado = rename($rutaOriginal, $nuevaRuta);
    escribirLog("Resultado de rename(): " . ($resultado ? "Éxito" : "Fallo"), $logFile);
    
    if ($resultado) {
        escribirLog("Archivo renombrado exitosamente", $logFile);
        return [
            'success' => true,
            'message' => 'Archivo renombrado exitosamente.',
            'nuevaRuta' => str_replace($rutaBase, 'Almacenamiento', $nuevaRuta)
        ];
    } else {
        escribirLog("Error al renombrar el archivo. Verifica los permisos.", $logFile);
        return [
            'success' => false,
            'message' => 'Error al renombrar el archivo. Verifica los permisos.'
        ];
    }
}

// Función para guardar contenido de archivo de texto
function guardarContenido($rutaArchivo, $contenido, $rutaBase, $logFile) {
    escribirLog("Intentando guardar contenido en: {$rutaArchivo}", $logFile);
    
    // Validar que la ruta del archivo esté dentro del directorio de almacenamiento
    if (!validarRuta($rutaArchivo, $rutaBase, $logFile)) {
        escribirLog("Error: La ruta del archivo no es válida o está fuera de la carpeta raíz", $logFile);
        return [
            'success' => false,
            'message' => 'La ruta del archivo no es válida o está fuera de la carpeta raíz.'
        ];
    }
    
    // Verificar que sea un archivo de texto
    $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
    $extensionesTexto = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'doc', 'docx', 'rtf', 'odt', 'tex'];
    
    escribirLog("Extensión del archivo: {$extension}", $logFile);
    
    if (!in_array($extension, $extensionesTexto)) {
        escribirLog("Error: No es un archivo de texto editable", $logFile);
        return [
            'success' => false,
            'message' => 'Solo se pueden editar archivos de texto.'
        ];
    }
    
    // Intentar guardar el contenido
    $resultado = file_put_contents($rutaArchivo, $contenido);
    escribirLog("Resultado de file_put_contents(): " . ($resultado !== false ? "Éxito" : "Fallo"), $logFile);
    
    if ($resultado !== false) {
        escribirLog("Contenido guardado exitosamente", $logFile);
        return [
            'success' => true,
            'message' => 'Contenido guardado exitosamente.'
        ];
    } else {
        escribirLog("Error al guardar el contenido. Verifica los permisos.", $logFile);
        return [
            'success' => false,
            'message' => 'Error al guardar el contenido. Verifica los permisos.'
        ];
    }
}

// Procesar solicitud de renombrar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'renombrar') {
    // Crear un nuevo archivo de log para cada solicitud
    $logFile = __DIR__ . '/debug_renombrar_' . date('Y-m-d_H-i-s') . '.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Inicio de log\n", FILE_APPEND);
    
    escribirLog("=== NUEVA SOLICITUD DE RENOMBRAR ===", $logFile);
    escribirLog("POST data: " . print_r($_POST, true), $logFile);
    
    // Obtener la ruta relativa desde el formulario
    $rutaRelativa = isset($_POST['ruta']) ? trim($_POST['ruta']) : '';
    $nuevoNombre = isset($_POST['nuevo_nombre']) ? trim($_POST['nuevo_nombre']) : '';
    $tipoElemento = isset($_POST['tipo_elemento']) ? trim($_POST['tipo_elemento']) : '';
    
    escribirLog("Ruta relativa recibida: '{$rutaRelativa}'", $logFile);
    escribirLog("Nuevo nombre: '{$nuevoNombre}'", $logFile);
    escribirLog("Tipo de elemento: '{$tipoElemento}'", $logFile);
    
    // Validar que tenemos un nuevo nombre
    if (empty($nuevoNombre)) {
        escribirLog("Error: Nombre vacío", $logFile);
        $_SESSION['mensaje'] = 'Debe proporcionar un nombre válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Validar el nuevo nombre (no permitir caracteres especiales excepto guiones y guiones bajos)
    if (!preg_match('/^[a-zA-Z0-9_\-. ]+$/', $nuevoNombre)) {
        escribirLog("Error: Nombre con caracteres no permitidos", $logFile);
        $_SESSION['mensaje'] = 'El nombre contiene caracteres no permitidos. Use solo letras, números, espacios, guiones, puntos y guiones bajos.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Si la ruta está vacía, mostrar un error
    if (empty($rutaRelativa)) {
        escribirLog("Error: Ruta vacía", $logFile);
        $_SESSION['mensaje'] = 'No se pudo obtener la ruta del elemento a renombrar.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=ruta_vacia');
        exit;
    }
    
    // Construir la ruta absoluta
    $rutaOriginal = $rutaBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/'));
    escribirLog("Ruta absoluta construida: '{$rutaOriginal}'", $logFile);
    escribirLog("Ruta base: '{$rutaBase}'", $logFile);
    
    // Verificar si la ruta existe
    if (!file_exists($rutaOriginal)) {
        escribirLog("Error: La ruta no existe: '{$rutaOriginal}'", $logFile);
        $_SESSION['mensaje'] = 'La ruta especificada no existe.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Verificar si es la carpeta principal Almacenamiento
    if (realpath($rutaOriginal) === $rutaBase) {
        escribirLog("Error: Intento de renombrar la carpeta principal Almacenamiento", $logFile);
        $_SESSION['mensaje'] = 'No se puede renombrar la carpeta principal Almacenamiento.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Verificar si el tipo de elemento coincide con lo que realmente es
    $esDirectorio = is_dir($rutaOriginal);
    if (!empty($tipoElemento)) {
        if (($tipoElemento === 'carpeta' && !$esDirectorio) || ($tipoElemento === 'archivo' && $esDirectorio)) {
            escribirLog("Error: El tipo de elemento no coincide con lo que realmente es", $logFile);
            escribirLog("Tipo declarado: {$tipoElemento}, Es directorio: " . ($esDirectorio ? 'Sí' : 'No'), $logFile);
            $_SESSION['mensaje'] = 'El tipo de elemento no coincide con lo que realmente es.';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
    
    // Determinar si es un archivo o una carpeta y llamar a la función correspondiente
    if ($tipoElemento === 'archivo' || (!is_dir($rutaOriginal) && $tipoElemento !== 'carpeta')) {
        // Es un archivo, usar la nueva función específica para archivos
        escribirLog("Usando la función específica para renombrar archivos", $logFile);
        $resultado = renombrarArchivo($rutaOriginal, $nuevoNombre, $rutaBase, $logFile);
    } else {
        // Es una carpeta o no se especificó el tipo, usar la función original
        escribirLog("Usando la función original para renombrar carpetas", $logFile);
        $resultado = renombrar($rutaOriginal, $nuevoNombre, $rutaBase, $logFile);
    }
    
    escribirLog("Resultado de la operación: " . ($resultado['success'] ? "Éxito" : "Fallo"), $logFile);
    escribirLog("Mensaje: " . $resultado['message'], $logFile);
    
    $_SESSION['mensaje'] = $resultado['message'];
    $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'error';
    
    // Redirigir de vuelta
    escribirLog("Redirigiendo a: " . $_SERVER['HTTP_REFERER'], $logFile);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Procesar solicitud de guardar contenido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_contenido') {
    escribirLog("=== NUEVA SOLICITUD DE GUARDAR CONTENIDO ===", $logFile);
    
    $rutaRelativa = $_POST['ruta'];
    $contenido = $_POST['contenido'];
    
    escribirLog("Ruta relativa recibida: {$rutaRelativa}", $logFile);
    
    // Construir la ruta absoluta
    $rutaArchivo = $rutaBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/'));
    escribirLog("Ruta absoluta construida: {$rutaArchivo}", $logFile);
    
    // Verificar si la ruta existe
    if (!file_exists($rutaArchivo)) {
        escribirLog("Error: El archivo no existe: {$rutaArchivo}", $logFile);
        $_SESSION['mensaje'] = 'El archivo especificado no existe.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: /Sistema-NASv3/Src/index.php');
        exit;
    }
    
    $resultado = guardarContenido($rutaArchivo, $contenido, $rutaBase, $logFile);
    
    escribirLog("Resultado de la operación: " . ($resultado['success'] ? "Éxito" : "Fallo"), $logFile);
    escribirLog("Mensaje: " . $resultado['message'], $logFile);
    
    $_SESSION['mensaje'] = $resultado['message'];
    $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'error';
    
    // Obtener la carpeta de origen
    $carpetaOrigen = '';
    if (isset($_POST['carpeta_origen'])) {
        $carpetaOrigen = $_POST['carpeta_origen'];
    } else {
        // Si no se proporcionó, intentar determinarla a partir de la ruta del archivo
        $rutaRelativa = ltrim($_POST['ruta'], '/');
        $carpetaOrigen = dirname($rutaRelativa);
        if ($carpetaOrigen === '.') {
            $carpetaOrigen = '';
        }
    }
    
    escribirLog("Carpeta de origen determinada: {$carpetaOrigen}", $logFile);
    
    // Redirigir a la carpeta de origen
    if (!empty($carpetaOrigen)) {
        escribirLog("Redirigiendo a la carpeta de origen: {$carpetaOrigen}", $logFile);
        header('Location: /Sistema-NASv3/Src/index.php?carpeta=' . urlencode($carpetaOrigen));
    } else {
        // Si no hay carpeta de origen o está vacía, ir a la página principal
        escribirLog("Redirigiendo a la página principal", $logFile);
        header('Location: /Sistema-NASv3/Src/index.php');
    }
    exit;
}
?>
