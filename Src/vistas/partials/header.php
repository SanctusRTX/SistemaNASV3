<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

include './bd/db.php';
require_once './controladores/controller.php';
require_once './controladores/controlador_busqueda.php';
require_once './controladores/crearcarpeta_controlador.php';
require_once './controladores/controlador_copiarmover.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo']) && isset($_POST['carpeta_destino'])) {
        uploadFile($_FILES['archivo']['tmp_name'], $_POST['carpeta_destino']);
    } elseif (isset($_POST['eliminar_seleccionados'])) {
        if (isset($_POST['archivos_a_eliminar'])) {
            foreach ($_POST['archivos_a_eliminar'] as $archivo) {
                deleteFile($archivo);
            }
        }
    } elseif (isset($_POST['cerrar_sesion'])) {
        cerrarSesion();
    } elseif (isset($_POST['actualizar'])) {
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } elseif (isset($_POST['subir_archivo'])) {
        header("Location: index.php?modulo=subir");
        exit();
    } elseif (isset($_POST['crear_carpeta'])) {
        header("Location: index.php?modulo=crear_carpeta");
        exit();
    } elseif (isset($_POST['crear_archivo'])) {
        header("Location: index.php?modulo=crear_archivo");
        exit();
    } elseif (isset($_POST['copiarmover'])) {
        header("Location: index.php?modulo=copiarmover");
        exit();
    } elseif (isset($_POST['papelera'])) {
        header("Location: index.php?modulo=papelera");
        exit();
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

$carpetas = listDirectories(__DIR__ . '/../../Almacenamiento');
$local_files_and_directories = getFilesAndDirectories(__DIR__ . '/../../Almacenamiento');
$archivos_locales = getFilesInDirectory(__DIR__ . '/../../Almacenamiento');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema NAS</title>
    <!-- Archivos CSS locales -->
    <link href="/Sistema-NASv3/Public/Css/bootstrap.min.css" rel="stylesheet">
    <link href="/Sistema-NASv3/Public/Css/custom-bootstrap.css" rel="stylesheet">
    <link href="/Sistema-NASv3/Public/Css/poppins.css" rel="stylesheet">
    <link href="/Sistema-NASv3/Public/Css/iconos.css" rel="stylesheet">
    <link href="/Sistema-NASv3/Public/Css/estilos.css" rel="stylesheet">
    <link href="/Sistema-NASv3/Public/Css/botones.css" rel="stylesheet">
    <link href="/Sistema-NASv3/Public/Css/vista-explorador.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Estilos para el buscador -->
    <link href="/Sistema-NASv3/Public/Css/busqueda.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Estilos para tooltips animados -->
    <link href="/Sistema-NASv3/Public/Css/tooltips-animados.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Estilos responsivos para móviles -->
    <link href="/Sistema-NASv3/Public/Css/responsive.css" rel="stylesheet">
    <!-- Correcciones para la barra lateral -->
    <link href="/Sistema-NASv3/Public/Css/sidebar-fix.css" rel="stylesheet">
    <!-- Font Awesome local -->
    <link rel="stylesheet" href="/Sistema-NASv3/Public/vendor/fontawesome/css/all.min.css">
    
    <!-- Archivos JavaScript locales -->
    <script src="/Sistema-NASv3/Public/vendor/jquery/jquery-3.6.0.min.js"></script>
    <script src="/Sistema-NASv3/Public/vendor/popper/popper.min.js"></script>
    <!-- Scripts principales -->
    <script src="/Sistema-NASv3/Public/Js/jquery.min.js"></script>
    <script src="/Sistema-NASv3/Public/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="/Sistema-NASv3/Public/Js/bootstrap.bundle.min.js"></script>
    <script src="/Sistema-NASv3/Public/Js/ionicons.js"></script>
    <!-- Script para tooltips animados -->
    <script src="/Sistema-NASv3/Public/Js/tooltips-animados.js?v=<?php echo time(); ?>"></script>
    
    <!-- JavaScript para la funcionalidad de renombrar -->
    <script src="/Sistema-NASv3/Public/Js/renombrar.js"></script>
    <!-- Script de depuración específico para la vista de carpetas -->
    <script src="/Sistema-NASv3/Public/Js/carpeta-debug.js"></script>
    <!-- JavaScript para la funcionalidad de eliminación con confirmación -->
    <script src="/Sistema-NASv3/Public/Js/eliminar.js"></script>
    <!-- JavaScript para mejorar la experiencia en dispositivos móviles -->
    <script src="/Sistema-NASv3/Public/Js/mobile-responsive.js"></script>
    <!-- JavaScript para la vista de explorador de Windows -->
    <script src="/Sistema-NASv3/Public/Js/vista-explorador.js"></script>
    
    <!-- Estilos adicionales para Bootstrap 4 (local) -->
    <link rel="stylesheet" href="/Sistema-NASv3/Public/vendor/bootstrap/css/bootstrap.min.css">
    
    <script>
    // Verificar que jQuery esté cargado
    $(document).ready(function() {
        console.log("jQuery cargado correctamente");
    });
    </script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark border-bottom" style="background-color: #3AB397;">
        <div class="container-fluid">
            <!-- Botón para mostrar/ocultar sidebar en móviles -->
            <button id="toggleSidebar" class="btn btn-outline-light d-lg-none mr-2">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="../index.php">
                <img src="/Sistema-NASv3/Public/img/logo1-.png" alt="Logo" width="110" height="95" class="d-inline-block align-text-top">
            </a>
            <c class="navbar-brand115" href="">
                <img src="/Sistema-NASv3/Public/img/logo2-.png" alt="Logo" width="245" height="95" class="d-inline-block align-center">
            </c>
            
            <!-- Botón para mostrar menú en móviles -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMobile" aria-controls="navbarMobile" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            
            <!-- Menú colapsable para móviles -->
            <div class="collapse navbar-collapse" id="navbarMobile">
                <ul class="navbar-nav ml-auto">
                    <!-- Mostrar rol en el menú móvil -->
                    <li class="nav-item d-lg-none">
                        <span class="nav-link text-white">
                            <i class="fas fa-user"></i> Usuario: <?php echo isset($_SESSION['username']) ? ucfirst($_SESSION['username']) : 'Invitado'; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <form class="d-flex" method="post" action="">
                            <button class="btn btn-outline-light" type="submit" name="cerrar_sesion" title="Salir de la Sesión Actual">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </button>
                        </form>
                    </li>
                    <!-- Mostrar información del usuario -->
                    <li class="nav-item ml-2">
                        <div class="navbar-text text-white d-flex align-items-center">
                            <i class="fas fa-user"></i> 
                            <?php 
                            $nombreUsuario = isset($_SESSION['username']) ? ucfirst($_SESSION['username']) : 'Invitado';
                            $rolNombre = isset($_SESSION['rol']) ? ucfirst($_SESSION['rol']) : 'Usuario';
                            echo " Usuario: $nombreUsuario <small>($rolNombre)</small>"; 
                            ?>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main>
    <div class="row position-relative">
        <!-- Contenedor para el botón y texto de la barra lateral (visible en escritorio) -->
        <div class="position-absolute d-none d-lg-flex align-items-center" style="left: 10px; top: 10px; z-index: 1000;">
            <span id="sidebar-label" class="mr-2 font-weight-bold">Navegador</span>
            <button type="button" id="toggleSidebar" class="btn btn-primary d-flex" style="border-radius: 50%; width: 40px; height: 40px; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <script>
            // Asignar evento al botón de la barra lateral en escritorio
            document.addEventListener('DOMContentLoaded', function() {
                const desktopToggleBtn = document.getElementById('toggleSidebar');
                if (desktopToggleBtn) {
                    desktopToggleBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof toggleSidebarGlobal === 'function') {
                            toggleSidebarGlobal();
                        } else {
                            console.error('La función toggleSidebarGlobal no está definida');
                        }
                    });
                }
            });
        </script>
        
        <!-- Botón para ocultar/mostrar la barra lateral (visible en móviles) -->
        <button id="toggleSidebarMobile" class="btn btn-light position-fixed d-lg-none" onclick="toggleSidebarGlobal()" style="left: 10px; bottom: 10px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Overlay para cerrar el sidebar en móviles -->
        <div class="sidebar-overlay"></div>
        
        <!-- Sidebar con clases optimizadas para móviles -->
        <div id="sidebar" class="col-md-3 border-right">
            <aside class="p-3">
                <hr>
                <!-- Formulario de búsqueda mejorado -->
                <div class="search-container mb-4">
                    <form action="index.php" method="get" class="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control" name="buscar" id="buscarInput" placeholder="Buscar archivos..." aria-label="Buscar archivos" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Buscar">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="advancedSearchBtn" data-tooltip data-tooltip-type="info" data-tooltip-placement="top" title="Búsqueda avanzada">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Opciones de búsqueda avanzada (ocultas por defecto) -->
                        <div class="advanced-search-options mt-2" id="advancedSearchOptions" style="display: none;">
                            <div class="card card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="buscarContenido" name="contenido" value="true">
                                            <label class="form-check-label" for="buscarContenido">
                                                <i class="fas fa-file-alt"></i> Buscar dentro del contenido de archivos
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="tipoArchivo" class="form-label small mb-0"><i class="fas fa-filter"></i> Filtrar por tipo:</label>
                                        <select class="form-select form-select-sm" id="tipoArchivo" name="tipo">
                                            <option value="todos" selected>Todos</option>
                                            <option value="carpetas">Carpetas</option>
                                            <option value="archivos">Archivos</option>
                                            <option value="documentos">Documentos</option>
                                            <option value="imagenes">Imágenes</option>
                                            <option value="videos">Videos</option>
                                            <option value="audio">Audio</option>
                                            <option value="comprimidos">Archivos comprimidos</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sugerencias de búsqueda (se muestran al escribir) -->
                        <div class="search-suggestions" id="searchSuggestions"></div>
                    </form>
                    
                    <?php
                    // Mostrar búsquedas recientes si existen
                    if (function_exists('obtenerBusquedasRecientes')) {
                        $busquedasRecientes = obtenerBusquedasRecientes(3);
                        if (!empty($busquedasRecientes)) {
                            echo '<div class="recent-searches mt-2">';
                            echo '<small class="text-muted"><i class="fas fa-history"></i> Búsquedas recientes:</small>';
                            echo '<div class="d-flex flex-wrap gap-1 mt-1">';
                            foreach ($busquedasRecientes as $busqueda) {
                                echo '<a href="index.php?buscar=' . urlencode($busqueda) . '" class="badge bg-light text-dark text-decoration-none">';
                                echo htmlspecialchars($busqueda);
                                echo '</a>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                
                <!-- Script para el buscador mejorado -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Toggle para opciones de búsqueda avanzada
                    const advancedSearchBtn = document.getElementById('advancedSearchBtn');
                    const advancedSearchOptions = document.getElementById('advancedSearchOptions');
                    
                    if (advancedSearchBtn && advancedSearchOptions) {
                        advancedSearchBtn.addEventListener('click', function() {
                            if (advancedSearchOptions.style.display === 'none') {
                                advancedSearchOptions.style.display = 'block';
                                // Cambiar icono del botón
                                this.innerHTML = '<i class="fas fa-times"></i>';
                                this.setAttribute('data-bs-original-title', 'Cerrar opciones avanzadas');
                            } else {
                                advancedSearchOptions.style.display = 'none';
                                // Restaurar icono original
                                this.innerHTML = '<i class="fas fa-sliders-h"></i>';
                                this.setAttribute('data-bs-original-title', 'Búsqueda avanzada');
                            }
                        });
                    }
                    
                    // Inicializar tooltips
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl, {
                            placement: 'top',
                            trigger: 'hover'
                        });
                    });
                });
                </script>
                
                <!-- Espacio para la barra lateral -->
                <hr>
                
                <!-- Sección de Carpetas -->
                <div class="d-flex align-items-center mb-3">
                    <ion-icon name="folder-outline" class="mr-2" style="font-size: 1.5rem;"></ion-icon>
                    <h6 class="mb-0">Almacenamiento</h6>
                </div>
                <ul class="nav flex-column mt-2 mobile-nav">
                    <?php foreach ($carpetas as $carpeta): ?>
                    <li class="nav-item d-flex align-items-center my-2">
                        <ion-icon name="folder-outline" style="margin-right: 8px; font-size: 1.2rem;"></ion-icon>
                        <a class="nav-link" href="index.php?carpeta=<?= urlencode($carpeta) ?>" style="color: black">
                            <?= htmlspecialchars($carpeta) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>

            </aside>
        </div>
        <div id="mainContent" class="col-md-9 py-3 pr-5">
            <div class="container border">
                <div class="row">
                    <div class="col-md-12 text-end p-3">
                        <form action="" method="post">
                            <!-- Botón de actualizar (visible para todos) -->
                            <button type="submit" class="btn btn-funcion btn-secondary btn-sm" name="actualizar" data-tooltip data-tooltip-type="secondary" data-tooltip-placement="top" title="Actualizar Página">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            
                            <!-- Botón de crear carpeta (solo administrador y supervisor) -->
                            <?php if (tienePermiso('crear_carpeta')): ?>
                            <button type="submit" class="btn btn-funcion btn-primary btn-sm" name="crear_carpeta" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Crear Nueva Carpeta">
                                <i class="fas fa-folder-plus"></i>
                            </button>
                            <?php endif; ?>
                            
                            <!-- Botón de crear archivo (todos los roles) -->
                            <button type="submit" class="btn btn-funcion btn-success btn-sm" name="crear_archivo" data-tooltip data-tooltip-type="success" data-tooltip-placement="top" title="Crear Nuevo Archivo de Texto">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            
                            <!-- Botón de copiar/mover (solo administrador y supervisor) -->
                            <?php if (tienePermiso('mover') || tienePermiso('copiar')): ?>
                            <button type="submit" class="btn btn-funcion btn-info btn-sm" name="copiarmover" data-tooltip data-tooltip-type="info" data-tooltip-placement="top" title="Mover o Copiar Archivos">
                                <i class="fas fa-copy"></i>
                            </button>
                            <?php endif; ?>
                            
                            <!-- Botón de modo oscuro -->
                            <button type="button" id="darkModeBtn" class="btn btn-funcion btn-outline-secondary btn-sm" data-tooltip data-tooltip-type="secondary" data-tooltip-placement="top" title="Cambiar Modo Oscuro/Claro">
                                <i class="fas fa-moon" id="darkModeIcon"></i>
                            </button>
                            
                            <!-- Botón de subir archivo (todos los roles) -->
                            <button type="submit" class="btn btn-funcion btn-success btn-sm" name="subir_archivo" data-tooltip data-tooltip-type="success" data-tooltip-placement="top" title="Subir Archivo">
                                <i class="fas fa-upload"></i>
                            </button>
                            
                            <!-- Botón de papelera (solo administrador y supervisor) -->
                            <?php if (tienePermiso('papelera')): ?>
                            <button type="submit" class="btn btn-funcion btn-danger btn-sm" name="papelera" data-tooltip data-tooltip-type="danger" data-tooltip-placement="top" title="Papelera de Reciclaje">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Barra de navegación de rutas (breadcrumb) estilo Explorador de Windows -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center explorer-path">
                                    <span class="me-2 text-muted"><i class="fas fa-folder-open text-warning"></i> Ubicación:</span>
                                    
                                    <?php
                                    // Obtener la ruta actual
                                    $rutaActual = isset($_GET['carpeta']) ? $_GET['carpeta'] : '';
                                    
                                    // Siempre mostrar el enlace a la raíz (Almacenamiento)
                                    echo '<a href="index.php" class="path-segment"><i class="fas fa-home text-primary"></i> Inicio</a>';
                                    
                                    if (!empty($rutaActual)) {
                                        // Mostrar el separador
                                        echo '<span class="path-separator"><i class="fas fa-chevron-right text-secondary"></i></span>';
                                        
                                        // Dividir la ruta en segmentos
                                        $segmentos = explode('/', trim($rutaActual, '/'));
                                        $rutaAcumulada = '';
                                        
                                        // Generar enlaces para cada segmento de la ruta
                                        foreach ($segmentos as $i => $segmento) {
                                            $rutaAcumulada .= '/' . $segmento;
                                            $esUltimo = ($i === count($segmentos) - 1);
                                            
                                            if ($esUltimo) {
                                                // El último segmento es la ubicación actual (resaltado)
                                                echo '<span class="path-segment current"><i class="fas fa-folder-open text-warning"></i> ' . htmlspecialchars($segmento) . '</span>';
                                            } else {
                                                // Segmentos intermedios (con enlaces)
                                                echo '<a href="index.php?carpeta=' . urlencode(ltrim($rutaAcumulada, '/')) . '" class="path-segment"><i class="fas fa-folder text-warning"></i> ' . htmlspecialchars($segmento) . '</a>';
                                                
                                                // Añadir separador después de cada segmento excepto el último
                                                if ($i < count($segmentos) - 1) {
                                                    echo '<span class="path-separator"><i class="fas fa-chevron-right text-secondary"></i></span>';
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estilos para la barra de navegación de rutas -->
                <style>
                .navbar-brand115 {
                    margin-top: auto;
                    align-items: center;
                    padding-left: 35%;
                }

                .explorer-path {
                    font-size: 0.95rem;
                    overflow-x: auto;
                    white-space: nowrap;
                    padding: 0.25rem 0;
                }
                .path-segment {
                    padding: 0.25rem 0.5rem;
                    border-radius: 4px;
                    color: #333;
                    text-decoration: none;
                    transition: background-color 0.2s;
                }
                .path-segment:hover {
                    background-color: #f0f0f0;
                    text-decoration: none;
                }
                .path-segment.current {
                    font-weight: bold;
                    color: #0056b3;
                }
                .path-separator {
                    margin: 0 0.25rem;
                    color: #aaa;
                }
                
                /* Estilos para el modo oscuro */
                body.dark-mode {
                    background-color: #121212;
                    color: #e0e0e0;
                }
                
                /* Aplicar fondo oscuro a todos los elementos con excepciones específicas */
                body.dark-mode *:not(.btn):not(.progress-bar):not(.alert):not(.custom-control-input):not(.dropdown-menu):not(.dropdown-item) {
                    background-color: transparent;
                }
                
                /* Corregir específicamente los bordes y espacios entre carpetas */
                body.dark-mode #mainContent,
                body.dark-mode main,
                body.dark-mode .row,
                body.dark-mode .col-md-3,
                body.dark-mode .col-md-9,
                body.dark-mode .col-md-12,
                body.dark-mode .container-fluid,
                body.dark-mode .container,
                body.dark-mode .py-3,
                body.dark-mode .py-5,
                body.dark-mode .pr-5,
                body.dark-mode .p-3,
                body.dark-mode aside,
                body.dark-mode ul,
                body.dark-mode li,
                body.dark-mode .nav,
                body.dark-mode .nav-item,
                body.dark-mode .d-flex,
                body.dark-mode .align-items-center,
                body.dark-mode .my-2 {
                    background-color: #121212;
                }
                
                /* Asegurar que los elementos específicos tengan el color correcto */
                body.dark-mode #sidebar h4,
                body.dark-mode #sidebar h6,
                body.dark-mode #sidebar hr {
                    background-color: #121212;
                    border-color: #333;
                    color: #e0e0e0;
                }
                
                body.dark-mode .navbar {
                    background-color: #1f1f1f !important;
                }
                
                body.dark-mode .card {
                    background-color: #1f1f1f;
                    border-color: #333;
                }
                
                body.dark-mode .card-header {
                    background-color: #2d2d2d;
                    border-color: #333;
                }
                
                body.dark-mode .table {
                    color: #e0e0e0;
                    background-color: #1f1f1f;
                }
                
                body.dark-mode .table thead th {
                    border-color: #333;
                    background-color: #2d2d2d;
                }
                
                body.dark-mode .table td, body.dark-mode .table th {
                    border-color: #333;
                    background-color: #1f1f1f;
                }
                
                body.dark-mode .border {
                    border-color: #333 !important;
                }
                
                body.dark-mode .bg-light {
                    background-color: #2d2d2d !important;
                }
                
                body.dark-mode .text-dark {
                    color: #e0e0e0 !important;
                }
                
                body.dark-mode a:not(.btn), body.dark-mode .nav-link {
                    color: #8ab4f8;
                }
                
                body.dark-mode a:hover:not(.btn), body.dark-mode .nav-link:hover {
                    color: #aecbfa;
                }
                
                body.dark-mode .border-end {
                    border-color: #333 !important;
                }
                
                body.dark-mode .nav-link[style*="color: black"] {
                    color: #e0e0e0 !important;
                }
                
                body.dark-mode span[style*="color: black"] {
                    color: #e0e0e0 !important;
                }
                
                body.dark-mode .path-segment {
                    color: #e0e0e0;
                    background-color: #1f1f1f;
                }
                
                body.dark-mode .path-segment:hover {
                    background-color: #2d2d2d;
                }
                
                body.dark-mode .path-segment.current {
                    color: #8ab4f8;
                    background-color: #2d2d2d;
                }
                
                /* Estilos para el apartado de carpetas en modo oscuro */
                body.dark-mode #sidebar {
                    background-color: #1a1a1a;
                    color: #e0e0e0;
                }
                
                body.dark-mode .container.border {
                    background-color: #1f1f1f;
                    border-color: #333 !important;
                }
                
                body.dark-mode .archivo-item,
                body.dark-mode .carpeta-item {
                    background-color: #2d2d2d;
                    border-color: #444;
                }
                
                body.dark-mode .archivos-section h4,
                body.dark-mode .carpetas-section h4 {
                    color: #e0e0e0;
                    background-color: #1f1f1f;
                }
                
                body.dark-mode ion-icon[name="folder-outline"],
                body.dark-mode .fas.fa-folder,
                body.dark-mode .fas.fa-folder-open {
                    color: #ffd866;
                }
                
                body.dark-mode .custom-file-label {
                    background-color: #2d2d2d;
                    color: #e0e0e0;
                    border-color: #444;
                }
                
                body.dark-mode .form-control {
                    background-color: #2d2d2d;
                    color: #e0e0e0;
                    border-color: #444;
                }
                
                body.dark-mode .form-control:focus {
                    background-color: #333;
                    color: #fff;
                }
                
                body.dark-mode textarea.form-control {
                    background-color: #2d2d2d;
                    color: #e0e0e0;
                }
                
                body.dark-mode .form-text.text-muted {
                    color: #aaa !important;
                }
                /* Estilos adicionales para asegurar compatibilidad completa */
                body.dark-mode hr {
                    border-color: #333;
                }
                
                body.dark-mode .list-group-item {
                    background-color: #1a1a1a;
                    border-color: #333;
                    color: #e0e0e0;
                }
                
                body.dark-mode .dropdown-menu {
                    background-color: #1a1a1a !important;
                    border-color: #333;
                }
                
                body.dark-mode .dropdown-item {
                    color: #e0e0e0;
                    background-color: #1a1a1a;
                }
                
                body.dark-mode .dropdown-item:hover {
                    background-color: #2d2d2d;
                }
                
                body.dark-mode .modal-content {
                    background-color: #1a1a1a;
                    border-color: #333;
                }
                
                body.dark-mode .modal-header,
                body.dark-mode .modal-footer {
                    border-color: #333;
                    background-color: #1f1f1f;
                }
                
                body.dark-mode .modal-body {
                    background-color: #1a1a1a;
                    color: #e0e0e0;
                }
                
                body.dark-mode .close {
                    color: #e0e0e0;
                    text-shadow: none;
                }
                
                body.dark-mode .close:hover {
                    color: #fff;
                }
                
                /* Corregir específicamente los bordes */
                body.dark-mode * {
                    border-color: #333 !important;
                }
                
                /* Excepciones para botones */
                body.dark-mode .btn-primary {
                    border-color: #0d6efd !important;
                }
                
                body.dark-mode .btn-success {
                    border-color: #198754 !important;
                }
                
                body.dark-mode .btn-danger {
                    border-color: #dc3545 !important;
                }
                
                body.dark-mode .btn-warning {
                    border-color: #ffc107 !important;
                }
                
                body.dark-mode .btn-info {
                    border-color: #0dcaf0 !important;
                }
                
                /* Asegurar que los elementos de la lista de carpetas tengan el fondo correcto */
                body.dark-mode .nav-link,
                body.dark-mode .nav-item {
                    background-color: transparent !important;
                }
                
                body.dark-mode #sidebar {
                    background-color: #121212 !important;
                }
                
                body.dark-mode aside {
                    background-color: #121212 !important;
                }
                
                /* Asegurar que los espacios entre carpetas tengan el color correcto */
                body.dark-mode .nav-item + .nav-item,
                body.dark-mode .nav-link + .nav-link {
                    border-top-color: #333 !important;
                }
                
                /* Corregir el fondo de los elementos de la lista */
                body.dark-mode ul.nav.flex-column.mt-2,
                body.dark-mode ul.nav.flex-column.ms-3 {
                    background-color: #121212 !important;
                }
                
                /* Cambiar el color de la carpeta raíz a blanco en modo oscuro */
                body.dark-mode .carpeta-raiz,
                body.dark-mode .carpeta-raiz a,
                body.dark-mode .nav-link[data-carpeta="raiz"],
                body.dark-mode a[href*="carpeta=raiz"],
                body.dark-mode a[href*="carpeta=Almacenamiento"] {
                    color: #ffffff !important;
                }
                
                /* Corregir el color de fondo de las tablas y sus elementos */
                body.dark-mode table.table-striped tbody tr:nth-of-type(odd) {
                    background-color: #252525 !important;
                }
                
                body.dark-mode table.table-hover tbody tr:hover {
                    background-color: #2d2d2d !important;
                }
                
                /* Corregir el color de los iconos */
                body.dark-mode .fa,
                body.dark-mode .fas,
                body.dark-mode .far,
                body.dark-mode .fal,
                body.dark-mode .fab {
                    color: #e0e0e0;
                }
                
                /* Corregir el color de los badges */
                body.dark-mode .badge {
                    background-color: #2d2d2d;
                    color: #e0e0e0;
                }
                
                /* Corregir el color de los tooltips */
                body.dark-mode .tooltip-inner {
                    background-color: #1a1a1a;
                    color: #e0e0e0;
                }
                </style>
                
                <!-- JavaScript para el modo oscuro -->
                <script>
                $(document).ready(function() {
                    // Verificar si el modo oscuro estaba activado anteriormente
                    // Función para actualizar el icono según el estado del modo oscuro
                    function updateDarkModeIcon(isDarkMode) {
                        if (isDarkMode) {
                            $('#darkModeIcon').removeClass('fa-moon').addClass('fa-sun');
                            $('#darkModeBtn').removeClass('btn-outline-secondary').addClass('btn-warning');
                        } else {
                            $('#darkModeIcon').removeClass('fa-sun').addClass('fa-moon');
                            $('#darkModeBtn').removeClass('btn-warning').addClass('btn-outline-secondary');
                        }
                    }
                    
                    // Verificar el estado guardado del modo oscuro
                    if (localStorage.getItem('darkMode') === 'enabled') {
                        $('body').addClass('dark-mode');
                        updateDarkModeIcon(true);
                    } else {
                        updateDarkModeIcon(false);
                    }
                    
                    // Manejar el clic en el botón de modo oscuro
                    $('#darkModeBtn').on('click', function() {
                        // Verificar el estado actual
                        const isDarkMode = $('body').hasClass('dark-mode');
                        
                        if (isDarkMode) {
                            // Cambiar a modo claro
                            $('body').removeClass('dark-mode');
                            localStorage.setItem('darkMode', 'disabled');
                            updateDarkModeIcon(false);
                        } else {
                            // Cambiar a modo oscuro
                            $('body').addClass('dark-mode');
                            localStorage.setItem('darkMode', 'enabled');
                            updateDarkModeIcon(true);
                        }
                    });
                });
                </script>