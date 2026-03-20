<?php
// Incluir configuración de límites de subida de archivos
require_once __DIR__ . '/config/upload_limits.php';

// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de roles
require_once __DIR__ . '/funciones/roles_simple.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Ya incluimos roles_simple.php, no necesitamos permisos.php
// require_once 'funciones/permisos.php';

// Incluir el encabezado
include_once 'vistas/partials/header.php';

// Incluir funciones necesarias
include_once 'funciones/busqueda.php';
include_once 'funciones/validacion.php';
include_once 'controladores/controlador_busqueda.php';

// Guardar datos POST para depuración
if (!empty($_POST)) {
    $logDebug = __DIR__ . '/logs/debug_post.log';
    $postData = "[" . date('Y-m-d H:i:s') . "] POST data: " . print_r($_POST, true) . "\n";
    file_put_contents($logDebug, $postData, FILE_APPEND);
}

// Procesar creación de archivos de texto
if (isset($_POST['accion']) && $_POST['accion'] === 'crear_archivo' && isset($_POST['nombre_archivo']) && isset($_POST['extension']) && isset($_POST['carpeta_destino'])) {
    // Ruta para el archivo de log
    $logFileCrear = __DIR__ . '/logs/crear_archivos.log';
    
    // Función para escribir en el log
    function escribirLogCrear($mensaje, $archivo) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $mensaje\n";
        file_put_contents($archivo, $logMessage, FILE_APPEND);
    }
    
    // Crear directorio de logs si no existe
    if (!file_exists(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0777, true);
    }
    
    escribirLogCrear("Iniciando proceso de creación de archivo", $logFileCrear);
    
    // Obtener los datos del formulario
    $nombreArchivo = trim($_POST['nombre_archivo']);
    $extension = $_POST['extension'];
    $carpetaDestino = $_POST['carpeta_destino'];
    $contenido = isset($_POST['contenido']) ? $_POST['contenido'] : '';
    
    // Validar el nombre del archivo (eliminar caracteres no permitidos)
    $nombreArchivo = preg_replace('/[\\/\:\*\?\"\<\>\|]/', '', $nombreArchivo);
    
    if (empty($nombreArchivo)) {
        escribirLogCrear("ERROR: Nombre de archivo vacío o inválido", $logFileCrear);
        $_SESSION['mensaje'] = "Error: El nombre del archivo no puede estar vacío o contener caracteres no permitidos.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index.php?modulo=crear_archivo");
        exit;
    }
    
    // Construir el nombre completo del archivo con su extensión
    $nombreCompletoArchivo = $nombreArchivo . $extension;
    escribirLogCrear("Nombre de archivo: $nombreCompletoArchivo", $logFileCrear);
    
    // Construir la ruta completa de destino
    $rutaBase = realpath(__DIR__ . '/Almacenamiento');
    escribirLogCrear("Ruta base: $rutaBase", $logFileCrear);
    
    // Si la carpeta destino es 'Almacenamiento', usar la raíz
    if ($carpetaDestino === 'Almacenamiento') {
        $rutaDestino = $rutaBase;
    } else {
        // Asegurarse de que la ruta esté dentro de Almacenamiento
        $carpetaRelativa = ltrim(str_replace('Almacenamiento/', '', $carpetaDestino), '/\\');
        $rutaDestino = $rutaBase . DIRECTORY_SEPARATOR . $carpetaRelativa;
        escribirLogCrear("Carpeta relativa: $carpetaRelativa", $logFileCrear);
        
        // Verificar que la ruta de destino es válida y está dentro de Almacenamiento
        if (!file_exists($rutaDestino)) {
            escribirLogCrear("ADVERTENCIA: La carpeta de destino no existe: $rutaDestino", $logFileCrear);
            // Intentar crear la carpeta si no existe
            if (!mkdir($rutaDestino, 0777, true)) {
                escribirLogCrear("ERROR: No se pudo crear la carpeta de destino: $rutaDestino", $logFileCrear);
                $_SESSION['mensaje'] = "Error: No se pudo crear la carpeta de destino.";
                $_SESSION['tipo_mensaje'] = "error";
                header("Location: index.php?modulo=crear_archivo");
                exit;
            }
            escribirLogCrear("Se creó la carpeta de destino: $rutaDestino", $logFileCrear);
        }
        
        // Verificar que la ruta está dentro de Almacenamiento
        if (strpos($rutaDestino, $rutaBase) !== 0) {
            escribirLogCrear("ERROR: Ruta de destino fuera de Almacenamiento: $carpetaDestino, Ruta calculada: $rutaDestino", $logFileCrear);
            $_SESSION['mensaje'] = "Error: La carpeta de destino no es válida.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?modulo=crear_archivo");
            exit;
        }
    }
    
    escribirLogCrear("Ruta de destino: $rutaDestino", $logFileCrear);
    
    // Ruta completa donde se guardará el archivo
    $rutaCompleta = $rutaDestino . DIRECTORY_SEPARATOR . $nombreCompletoArchivo;
    
    escribirLogCrear("Intentando crear archivo: $nombreCompletoArchivo en $rutaCompleta", $logFileCrear);
    
    // Verificar si ya existe un archivo con el mismo nombre
    if (file_exists($rutaCompleta)) {
        escribirLogCrear("ERROR: Ya existe un archivo con el nombre: $nombreCompletoArchivo en $rutaDestino", $logFileCrear);
        $_SESSION['mensaje'] = "Error: Ya existe un archivo con el nombre '$nombreCompletoArchivo' en la carpeta de destino.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index.php?modulo=crear_archivo");
        exit;
    }
    
    // Verificar permisos de escritura
    if (!is_writable($rutaDestino)) {
        escribirLogCrear("ERROR: No se tienen permisos de escritura en la carpeta: $rutaDestino", $logFileCrear);
        $_SESSION['mensaje'] = "Error: No se tienen permisos de escritura en la carpeta de destino.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index.php?modulo=crear_archivo");
        exit;
    }
    
    // Crear el archivo
    try {
        $resultado = file_put_contents($rutaCompleta, $contenido);
        escribirLogCrear("Resultado de file_put_contents: " . ($resultado === false ? "ERROR" : "$resultado bytes escritos"), $logFileCrear);
    } catch (Exception $e) {
        escribirLogCrear("ERROR de excepción al crear archivo: " . $e->getMessage(), $logFileCrear);
        $resultado = false;
    }
    
    if ($resultado !== false) {
        escribirLogCrear("Archivo creado exitosamente: $nombreCompletoArchivo en $rutaCompleta", $logFileCrear);
        
        // Crear mensaje de éxito
        $_SESSION['mensaje'] = "El archivo '$nombreCompletoArchivo' ha sido creado exitosamente.";
        $_SESSION['tipo_mensaje'] = "exito";
        
        // Redirigir de vuelta a la página principal o a la carpeta donde se creó el archivo
        if ($carpetaDestino === 'Almacenamiento') {
            header("Location: index.php?archivo_creado=1");
        } else {
            // Obtener la ruta relativa para la redirección
            $rutaRelativa = str_replace($rutaBase, '', $rutaDestino);
            $rutaRelativa = ltrim($rutaRelativa, '/\\');
            if (empty($rutaRelativa)) {
                header("Location: index.php?archivo_creado=1");
            } else {
                header("Location: index.php?carpeta=" . urlencode($rutaRelativa) . "&archivo_creado=1");
            }
        }
        exit;
    } else {
        escribirLogCrear("ERROR: No se pudo crear el archivo: $nombreCompletoArchivo en $rutaCompleta", $logFileCrear);
        $_SESSION['mensaje'] = "Error: No se pudo crear el archivo. Verifique los permisos de escritura.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index.php?modulo=crear_archivo");
        exit;
    }
}

// Procesar subida de archivos si se ha enviado un formulario
if (isset($_FILES['archivo'])) {
    // Ruta para el archivo de log
    $logFile = __DIR__ . '/logs/subida_archivos.log';
    
    // Función para escribir en el log
    function escribirLogSubida($mensaje, $archivo) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $mensaje\n";
        file_put_contents($archivo, $logMessage, FILE_APPEND);
    }
    
    // Crear directorio de logs si no existe
    if (!file_exists(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0777, true);
    }
    
    escribirLogSubida("Iniciando proceso de subida de archivo", $logFile);
    
    // Verificar si hay errores en la subida
    if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $errorMensaje = "";
        switch ($_FILES['archivo']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMensaje = "El archivo excede el tamaño máximo permitido. Se ha configurado el sistema para aceptar archivos grandes, pero puede que necesites ajustar la configuración de PHP en el servidor.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMensaje = "El archivo solo fue subido parcialmente. Intenta nuevamente.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMensaje = "No se seleccionó ningún archivo para subir.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMensaje = "Falta la carpeta temporal en el servidor.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMensaje = "No se pudo escribir el archivo en el disco.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMensaje = "Una extensión de PHP detuvo la subida del archivo.";
                break;
            default:
                $errorMensaje = "Error desconocido al subir el archivo.";
        }
        
        escribirLogSubida("ERROR: " . $errorMensaje, $logFile);
        $_SESSION['mensaje'] = "Error: " . $errorMensaje;
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index.php?modulo=subir");
        exit;
    }
    
    // Si no hay errores, continuar con la subida
    
    // Obtener información del archivo
    $nombreArchivo = $_FILES['archivo']['name'];
    $archivoTemporal = $_FILES['archivo']['tmp_name'];
    $tamanoArchivo = $_FILES['archivo']['size'];
    
    // Obtener la carpeta de destino
    $carpetaDestino = isset($_POST['carpeta_destino']) ? $_POST['carpeta_destino'] : 'Almacenamiento';
    
    // Construir la ruta completa de destino
    $rutaBase = realpath(__DIR__ . '/Almacenamiento');
    escribirLogSubida("Ruta base: $rutaBase", $logFile);
    
    // Si la carpeta destino es 'Almacenamiento', usar la raíz
    if ($carpetaDestino === 'Almacenamiento') {
        $rutaDestino = $rutaBase;
    } else {
        // Asegurarse de que la ruta esté dentro de Almacenamiento
        $carpetaRelativa = ltrim(str_replace('Almacenamiento/', '', $carpetaDestino), '/\\');
        $rutaDestino = $rutaBase . DIRECTORY_SEPARATOR . $carpetaRelativa;
        escribirLogSubida("Carpeta relativa: $carpetaRelativa", $logFile);
        
        // Verificar que la ruta de destino es válida y está dentro de Almacenamiento
        if (!file_exists($rutaDestino)) {
            escribirLogSubida("ADVERTENCIA: La carpeta de destino no existe: $rutaDestino", $logFile);
            // Intentar crear la carpeta si no existe
            if (!mkdir($rutaDestino, 0777, true)) {
                escribirLogSubida("ERROR: No se pudo crear la carpeta de destino: $rutaDestino", $logFile);
                $_SESSION['mensaje'] = "Error: No se pudo crear la carpeta de destino.";
                $_SESSION['tipo_mensaje'] = "error";
                header("Location: index.php?modulo=subir");
                exit;
            }
            escribirLogSubida("Se creó la carpeta de destino: $rutaDestino", $logFile);
        }
        
        // Verificar que la ruta está dentro de Almacenamiento
        if (strpos($rutaDestino, $rutaBase) !== 0) {
            escribirLogSubida("ERROR: Ruta de destino fuera de Almacenamiento: $carpetaDestino, Ruta calculada: $rutaDestino", $logFile);
            $_SESSION['mensaje'] = "Error: La carpeta de destino no es válida.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?modulo=subir");
            exit;
        }
    }
    
    escribirLogSubida("Ruta de destino: $rutaDestino", $logFile);
    
    // Ruta completa donde se guardará el archivo
    $rutaCompleta = $rutaDestino . DIRECTORY_SEPARATOR . $nombreArchivo;
    
    escribirLogSubida("Intentando subir archivo: $nombreArchivo a $rutaCompleta", $logFile);
    
    // Verificar si ya existe un archivo con el mismo nombre
    if (file_exists($rutaCompleta)) {
        escribirLogSubida("ERROR: Ya existe un archivo con el nombre: $nombreArchivo en $rutaDestino", $logFile);
        $_SESSION['mensaje'] = "Error: Ya existe un archivo con el nombre '$nombreArchivo' en la carpeta de destino.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index.php?modulo=subir");
        exit;
    }
    
    // Mover el archivo subido a la carpeta de destino
    if (move_uploaded_file($archivoTemporal, $rutaCompleta)) {
        escribirLogSubida("Archivo subido exitosamente: $nombreArchivo a $rutaCompleta", $logFile);
        
        // Crear mensaje de éxito
        $_SESSION['mensaje'] = "El archivo '$nombreArchivo' ha sido subido exitosamente.";
        $_SESSION['tipo_mensaje'] = "exito";
        
        // Redirigir de vuelta a la página principal o a la carpeta donde se subió el archivo
        if ($carpetaDestino === 'Almacenamiento') {
            header("Location: index.php?subida_exitosa=1");
        } else {
            // Obtener la ruta relativa para la redirección
            $rutaRelativa = str_replace($rutaBase, '', $rutaDestino);
            $rutaRelativa = ltrim($rutaRelativa, '/\\');
            if (empty($rutaRelativa)) {
                header("Location: index.php?subida_exitosa=1");
            } else {
                header("Location: index.php?carpeta=" . urlencode($rutaRelativa) . "&subida_exitosa=1");
            }
        }
        exit;
    } else {
        escribirLogSubida("ERROR: No se pudo mover el archivo subido a la carpeta de destino", $logFile);
        $_SESSION['mensaje'] = "Error: No se pudo subir el archivo. Por favor, inténtelo de nuevo.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index.php?modulo=subir");
        exit;
    }
}

// Incluir los modales para renombrar (asegurando que estén disponibles en todas las vistas)
include_once 'vistas/partials/modales_renombrar.php';

// Procesar búsqueda si existe
$search_results = array();
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $rutaBase = realpath(__DIR__ . "/Almacenamiento");
    $search_term = $_GET['buscar'];
    
    // Obtener parámetros adicionales de búsqueda
    $parametrosBusqueda = [
        'tipo' => isset($_GET['tipo']) ? $_GET['tipo'] : 'todos',
        'ordenar' => isset($_GET['ordenar']) ? $_GET['ordenar'] : 'nombre_asc',
        'contenido' => isset($_GET['contenido']) ? $_GET['contenido'] : 'false',
        'guardar_historial' => 'true'
    ];
    
    // Usar el controlador de búsqueda mejorado
    $search_results = procesarBusqueda($search_term, $rutaBase, $parametrosBusqueda);
}

?>

<div class="main-content">
    <?php
    // Mostrar mensajes de notificación
    if (isset($_SESSION['mensaje'])) {
        $tipoAlerta = isset($_SESSION['tipo_mensaje']) && $_SESSION['tipo_mensaje'] === 'error' ? 'danger' : 'success';
        echo '<div class="alert alert-' . $tipoAlerta . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['mensaje'];
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
        
        // Limpiar mensaje de sesión
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    }
    
    // Mostrar mensajes de notificación para subida de archivos
    if (isset($_GET['subida_exitosa']) && $_GET['subida_exitosa'] == '1') {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-check-circle"></i> El archivo ha sido subido correctamente.';
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
    }
    
    // Mostrar mensajes de notificación para archivos creados
    if (isset($_GET['archivo_creado']) && $_GET['archivo_creado'] == '1') {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-check-circle"></i> El archivo de texto ha sido creado correctamente.';
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
    }
    
    // Mostrar mensajes de notificación para eliminación
    if (isset($_GET['eliminado']) && $_GET['eliminado'] == '1') {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-check-circle"></i> El elemento ha sido eliminado correctamente.';
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
    } elseif (isset($_GET['error'])) {
        $mensajeError = 'Ha ocurrido un error.';
        
        if ($_GET['error'] == 'eliminacion_fallida') {
            $mensajeError = 'No se pudo mover el elemento a la papelera. Verifique los permisos e intente nuevamente.';
        } elseif ($_GET['error'] == 'ruta_no_existe') {
            $mensajeError = 'No se pudo encontrar el elemento a eliminar. Es posible que ya haya sido eliminado.';
        } elseif ($_GET['error'] == 'ruta_vacia') {
            $mensajeError = 'No se proporcionó una ruta válida para el elemento a eliminar.';
        }
        
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-exclamation-triangle"></i> ' . $mensajeError;
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
    }
    
    // Mostrar resultados de búsqueda si se realizó una búsqueda
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        include 'vistas/admin/modulos/resultados_busqueda.php';
    }
    // Si no hay búsqueda, mostrar el módulo solicitado
    elseif (isset($_GET['modulo'])) {
        $modulo = $_GET['modulo'];
        switch ($modulo) {
            case 'explorar':
                // Todos los roles pueden explorar
                include 'vistas/admin/modulos/explorador_windows.php';
                break;
            case 'eliminar':
                // Solo administrador y supervisor pueden eliminar
                if (tienePermiso('eliminar')) {
                    include 'vistas/admin/modulos/eliminar.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para eliminar archivos o carpetas.</div>';
                }
                break;
            case 'explorador_windows':
                // Todos los roles pueden usar el explorador estilo Windows
                include 'vistas/admin/modulos/explorador_windows.php';
                break;
            case 'editar_archivo':
                // Solo administrador y supervisor pueden editar archivos
                if (tienePermiso('crear_archivo')) {
                    include 'vistas/admin/modulos/editar_archivo.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para editar archivos.</div>';
                }
                break;
            case 'subir':
                // Verificar permisos para subir archivos
                if (tienePermiso('subir')) {
                    include 'vistas/admin/modulos/subir.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para subir archivos.</div>';
                }
                break;
            case 'crear_archivo':
                // Verificar permisos
                if (tienePermiso('crear_archivo')) {
                    include 'vistas/admin/modulos/crear_archivo.php';
                } else {
                }
                break;
            case 'renombrar':
                // Verificar permisos para renombrar
                if (tienePermiso('renombrar')) {
                    include 'vistas/admin/modulos/renombrar.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para renombrar archivos o carpetas.</div>';
                }
                break;
            case 'papelera':
                // Verificar permisos para acceder a la papelera
                if (tienePermiso('papelera')) {
                    include 'vistas/admin/modulos/papelera.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para acceder a la papelera.</div>';
                }
                break;
            case 'copiarmover':
                // Verificar permisos para mover o copiar archivos/carpetas
                if (tienePermiso('mover') || tienePermiso('copiar')) {
                    include 'vistas/admin/modulos/copiarmover.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para mover o copiar archivos o carpetas.</div>';
                }
                break;
            case 'crear_carpeta':
                // Verificar permisos para crear carpetas
                if (tienePermiso('crear_carpeta')) {
                    include 'vistas/admin/modulos/crear_carpeta.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para crear carpetas.</div>';
                }
                break;
            case 'restaurar':
                // Verificar permisos para restaurar elementos de la papelera
                if (tienePermiso('restaurar')) {
                    include 'vistas/admin/modulos/restaurar.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para restaurar elementos de la papelera.</div>';
                }
                break;
            case 'vaciar_papelera':
                // Verificar permisos para vaciar la papelera
                if (tienePermiso('vaciar_papelera')) {
                    include 'vistas/admin/modulos/vaciar_papelera.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para vaciar la papelera.</div>';
                }
                break;
            case 'descargar':
                // Verificar permisos para vaciar la papelera
                if (tienePermiso('descargar')) {
                    include 'vistas/admin/modulos/descargar.php';
                } else {
                    echo '<div class="alert alert-danger">No tienes permiso para vaciar la papelera.</div>';
                }
                break;
            default:
                // Si el módulo no existe, mostrar el explorador por defecto
                include 'vistas/admin/modulos/explorador_windows.php';
                break;
        }
    } else {
        // Incluir directamente la vista de explorador estilo Windows como predeterminado
        include 'vistas/admin/modulos/explorador_windows.php';
        // Terminar la ejecución para evitar que se muestre cualquier otro contenido
        echo '</div>';
        include './vistas/partials/footer.php';
        exit;
        
        // Modal para renombrar carpetas (Bootstrap 4)
        echo '<div class="modal fade" id="modalRenombrar" tabindex="-1" role="dialog" aria-labelledby="modalRenombrarLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRenombrarLabel">Renombrar Carpeta</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="/Sistema-NASv3/Src/funciones/editar.php" method="POST" id="formRenombrar">
                        <div class="modal-body">
                            <input type="hidden" name="accion" value="renombrar">
                            <input type="hidden" name="ruta" id="rutaRenombrar">
                            <input type="hidden" name="tipo_elemento" id="tipoElemento" value="carpeta">
                            <div class="form-group">
                                <label for="nuevo_nombre">Nuevo nombre:</label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="nuevo_nombre" required>
                            </div>
                            <div id="rutaActual" class="text-muted small"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';
        
        // Modal específico para renombrar archivos (Bootstrap 4)
        echo '<div class="modal fade" id="modalRenombrarArchivo" tabindex="-1" role="dialog" aria-labelledby="modalRenombrarArchivoLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRenombrarArchivoLabel">Renombrar Archivo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="/Sistema-NASv3/Src/funciones/editar.php" method="POST" id="formRenombrarArchivo">
                        <div class="modal-body">
                            <input type="hidden" name="accion" value="renombrar">
                            <input type="hidden" name="ruta" id="rutaRenombrarArchivo">
                            <input type="hidden" name="tipo_elemento" id="tipoElementoArchivo" value="archivo">
                            <div class="form-group">
                                <label for="nuevo_nombre_archivo">Nuevo nombre:</label>
                                <input type="text" class="form-control" id="nuevo_nombre_archivo" name="nuevo_nombre" required>
                                <small class="form-text text-muted">No es necesario incluir la extensión del archivo.</small>
                            </div>
                            <div id="rutaActualArchivo" class="text-muted small"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';
        
        // Botones para probar los modales directamente (ocultos en producción)
        echo '<div class="d-none">';
        echo '<button type="button" id="testModalCarpeta" class="btn btn-primary m-1">Probar Modal Carpeta</button>';
        echo '<button type="button" id="testModalArchivo" class="btn btn-primary m-1">Probar Modal Archivo</button>';
        echo '</div>';
        
        // Nota: El código JavaScript para los modales ha sido movido a /Public/Js/renombrar.js
        echo '<script>
            // Verificar que el script de renombrar esté cargado
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof configurarBotonesRenombrar === "function") {
                    console.log("Script de renombrar cargado correctamente");
                } else {
                    console.error("¡ERROR! El script de renombrar no está cargado correctamente");
                }
            });
        </script>';
        
        echo '<style>
            .carpetas-container {
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
            }
            .carpeta-item, .archivo-item {
                margin: 5px 0;
                padding: 5px;
            }
            .carpeta-item:hover, .archivo-item:hover {
                background-color: #f0f0f0;
            }
            .archivos-section {
                margin-top: 15px;
                padding-top: 10px;
                border-top: 1px dashed #ccc;
            }
            h3, h4 {
                margin-bottom: 15px;
                color: #333;
            }
            i.fas.fa-folder, i.fas.fa-folder-open {
                color: #ffc107;
            }
            i.fas.fa-file {
                color: #6c757d;
            }
        </style>';
        
        // El script para configurar los botones ha sido movido a /Public/Js/renombrar.js
        
        echo '</div>';
    }
    ?>
</div>
<?php include './vistas/partials/footer.php'; ?>