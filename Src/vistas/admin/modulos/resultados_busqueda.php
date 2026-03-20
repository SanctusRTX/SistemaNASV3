<?php
/**
 * Vista de resultados de búsqueda
 * Sistema NAS v3
 * 
 * Muestra los resultados de búsqueda con opciones de filtrado y ordenamiento
 */

// Verificar que existan resultados de búsqueda
if (!isset($search_results) || !isset($search_term)) {
    echo '<div class="alert alert-warning">No se ha realizado ninguna búsqueda.</div>';
    exit;
}

// Función para formatear el tamaño en bytes a un formato legible
function formatSizeSearch($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Función para obtener el icono según la extensión
function getFileIcon($extension) {
    $iconoArchivo = 'fa-file';
    if (in_array($extension, ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md'])) {
        $iconoArchivo = 'fa-file-alt';
    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'])) {
        $iconoArchivo = 'fa-file-image';
    } elseif (in_array($extension, ['pdf'])) {
        $iconoArchivo = 'fa-file-pdf';
    } elseif (in_array($extension, ['doc', 'docx'])) {
        $iconoArchivo = 'fa-file-word';
    } elseif (in_array($extension, ['xls', 'xlsx'])) {
        $iconoArchivo = 'fa-file-excel';
    } elseif (in_array($extension, ['ppt', 'pptx'])) {
        $iconoArchivo = 'fa-file-powerpoint';
    } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
        $iconoArchivo = 'fa-file-archive';
    } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
        $iconoArchivo = 'fa-file-audio';
    } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'wmv'])) {
        $iconoArchivo = 'fa-file-video';
    }
    return $iconoArchivo;
}

// Obtener filtros de la URL
$tipoFiltro = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'nombre_asc';

// Filtrar resultados según el tipo
$resultadosFiltrados = $search_results;
if ($tipoFiltro !== 'todos') {
    $resultadosFiltrados = array_filter($search_results, function($item) use ($tipoFiltro) {
        if ($tipoFiltro === 'carpetas' && $item['is_dir']) {
            return true;
        } elseif ($tipoFiltro === 'archivos' && !$item['is_dir']) {
            return true;
        } elseif ($tipoFiltro !== 'carpetas' && $tipoFiltro !== 'archivos') {
            // Filtrar por extensión
            $extension = pathinfo($item['name'], PATHINFO_EXTENSION);
            $tiposArchivo = [
                'documentos' => ['txt', 'doc', 'docx', 'pdf', 'rtf', 'odt'],
                'imagenes' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'],
                'videos' => ['mp4', 'avi', 'mov', 'wmv', 'mkv'],
                'audio' => ['mp3', 'wav', 'ogg', 'flac'],
                'comprimidos' => ['zip', 'rar', '7z', 'tar', 'gz']
            ];
            
            if (isset($tiposArchivo[$tipoFiltro]) && in_array(strtolower($extension), $tiposArchivo[$tipoFiltro])) {
                return true;
            }
            return false;
        }
        return false;
    });
}

// Ordenar resultados
usort($resultadosFiltrados, function($a, $b) use ($ordenar) {
    switch ($ordenar) {
        case 'nombre_asc':
            return strcasecmp($a['name'], $b['name']);
        case 'nombre_desc':
            return strcasecmp($b['name'], $a['name']);
        case 'tipo_asc':
            $aIsDir = $a['is_dir'] ? 0 : 1;
            $bIsDir = $b['is_dir'] ? 0 : 1;
            if ($aIsDir === $bIsDir) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $aIsDir - $bIsDir;
        case 'tipo_desc':
            $aIsDir = $a['is_dir'] ? 1 : 0;
            $bIsDir = $b['is_dir'] ? 1 : 0;
            if ($aIsDir === $bIsDir) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $aIsDir - $bIsDir;
        case 'fecha_asc':
            $aTime = filemtime($a['path']);
            $bTime = filemtime($b['path']);
            return $aTime - $bTime;
        case 'fecha_desc':
            $aTime = filemtime($a['path']);
            $bTime = filemtime($b['path']);
            return $bTime - $aTime;
        default:
            return strcasecmp($a['name'], $b['name']);
    }
});

// Contar resultados
$totalResultados = count($resultadosFiltrados);
$totalCarpetas = count(array_filter($resultadosFiltrados, function($item) {
    return $item['is_dir'];
}));
$totalArchivos = $totalResultados - $totalCarpetas;
?>

<div class="container search-results-container">
    <div class="search-header mb-4">
        <h2>
            <i class="fas fa-search"></i> 
            Resultados de búsqueda para: <span class="search-term">"<?php echo htmlspecialchars($search_term); ?>"</span>
        </h2>
        <div class="search-stats">
            <span class="badge bg-primary"><?php echo $totalResultados; ?> resultados</span>
            <span class="badge bg-info"><?php echo $totalCarpetas; ?> carpetas</span>
            <span class="badge bg-secondary"><?php echo $totalArchivos; ?> archivos</span>
        </div>
    </div>
    
    <!-- Filtros y ordenamiento -->
    <div class="search-filters card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label for="filtroTipo" class="form-label"><i class="fas fa-filter"></i> Filtrar por tipo:</label>
                    <select id="filtroTipo" class="form-select" onchange="aplicarFiltros()">
                        <option value="todos" <?php echo $tipoFiltro === 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="carpetas" <?php echo $tipoFiltro === 'carpetas' ? 'selected' : ''; ?>>Carpetas</option>
                        <option value="archivos" <?php echo $tipoFiltro === 'archivos' ? 'selected' : ''; ?>>Archivos</option>
                        <option value="documentos" <?php echo $tipoFiltro === 'documentos' ? 'selected' : ''; ?>>Documentos</option>
                        <option value="imagenes" <?php echo $tipoFiltro === 'imagenes' ? 'selected' : ''; ?>>Imágenes</option>
                        <option value="videos" <?php echo $tipoFiltro === 'videos' ? 'selected' : ''; ?>>Videos</option>
                        <option value="audio" <?php echo $tipoFiltro === 'audio' ? 'selected' : ''; ?>>Audio</option>
                        <option value="comprimidos" <?php echo $tipoFiltro === 'comprimidos' ? 'selected' : ''; ?>>Archivos comprimidos</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="ordenarPor" class="form-label"><i class="fas fa-sort"></i> Ordenar por:</label>
                    <select id="ordenarPor" class="form-select" onchange="aplicarFiltros()">
                        <option value="nombre_asc" <?php echo $ordenar === 'nombre_asc' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                        <option value="nombre_desc" <?php echo $ordenar === 'nombre_desc' ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                        <option value="tipo_asc" <?php echo $ordenar === 'tipo_asc' ? 'selected' : ''; ?>>Tipo (Carpetas primero)</option>
                        <option value="tipo_desc" <?php echo $ordenar === 'tipo_desc' ? 'selected' : ''; ?>>Tipo (Archivos primero)</option>
                        <option value="fecha_asc" <?php echo $ordenar === 'fecha_asc' ? 'selected' : ''; ?>>Fecha (Más antiguo)</option>
                        <option value="fecha_desc" <?php echo $ordenar === 'fecha_desc' ? 'selected' : ''; ?>>Fecha (Más reciente)</option>
                    </select>
                </div>
            </div>
            
            <!-- Nueva búsqueda -->
            <div class="row mt-3">
                <div class="col-12">
                    <form action="index.php" method="get" class="d-flex">
                        <input type="text" name="buscar" class="form-control" placeholder="Nueva búsqueda..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="btn btn-primary ms-2">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($totalResultados === 0): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No se encontraron resultados para "<strong><?php echo htmlspecialchars($search_term); ?></strong>". Intenta con otros términos de búsqueda.
    </div>
    <?php else: ?>
    
    <!-- Vista de resultados -->
    <div class="search-results">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%"></th>
                        <th style="width: 40%">Nombre</th>
                        <th style="width: 15%">Tipo</th>
                        <th style="width: 15%">Tamaño</th>
                        <th style="width: 15%">Fecha</th>
                        <th style="width: 10%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultadosFiltrados as $item): 
                        $esDir = $item['is_dir'];
                        $nombre = $item['name'];
                        $ruta = $item['path'];
                        
                        // Obtener ruta relativa para la URL
                        $rutaRelativa = str_replace(realpath(__DIR__ . '/../../../Almacenamiento') . '\\', '', $ruta);
                        $rutaRelativa = str_replace('\\', '/', $rutaRelativa);
                        
                        // Obtener información adicional
                        $fechaModificacion = date('d/m/Y H:i', filemtime($ruta));
                        
                        // Determinar tipo y tamaño
                        if ($esDir) {
                            $tipo = 'Carpeta';
                            $tamanio = '-';
                            $icono = 'fa-folder';
                        } else {
                            $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                            $tipo = strtoupper($extension);
                            $tamanio = formatSizeSearch(filesize($ruta));
                            $icono = getFileIcon($extension);
                        }
                    ?>
                    <tr class="search-result-item" data-ruta="<?php echo htmlspecialchars($rutaRelativa); ?>" data-nombre="<?php echo htmlspecialchars($nombre); ?>" data-tipo="<?php echo $esDir ? 'carpeta' : 'archivo'; ?>">
                        <td class="text-center">
                            <i class="fas <?php echo $icono; ?> fa-lg" style="color: <?php echo $esDir ? '#ffc107' : '#6c757d'; ?>"></i>
                        </td>
                        <td>
                            <?php if ($esDir): ?>
                            <a href="index.php?modulo=explorador_windows&carpeta=<?php echo urlencode($rutaRelativa); ?>" class="search-result-link">
                                <?php echo htmlspecialchars($nombre); ?>
                            </a>
                            <?php else: 
                                // Determinar si es un archivo que se puede editar
                                $extensionesEditables = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'doc', 'docx', 'rtf', 'odt'];
                                $esEditable = in_array($extension, $extensionesEditables);
                                
                                if ($esEditable):
                            ?>
                            <a href="index.php?modulo=editar_archivo&archivo=<?php echo urlencode($rutaRelativa); ?>" class="search-result-link">
                                <?php echo htmlspecialchars($nombre); ?>
                            </a>
                            <?php else: ?>
                            <span class="search-result-name"><?php echo htmlspecialchars($nombre); ?></span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $tipo; ?></td>
                        <td><?php echo $tamanio; ?></td>
                        <td><?php echo $fechaModificacion; ?></td>
                        <td>
                            <div class="btn-group">
                                <?php if ($esDir): ?>
                                <a href="index.php?modulo=explorador_windows&carpeta=<?php echo urlencode($rutaRelativa); ?>" class="btn btn-sm btn-primary" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Abrir">
                                    <i class="fas fa-folder-open"></i>
                                </a>
                                <?php else: 
                                    if ($esEditable):
                                ?>
                                <a href="index.php?modulo=editar_archivo&archivo=<?php echo urlencode($rutaRelativa); ?>" class="btn btn-sm btn-info" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <a href="index.php?modulo=descargar&archivo=<?php echo urlencode($rutaRelativa); ?>" class="btn btn-sm btn-success" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-ruta="<?php echo htmlspecialchars($rutaRelativa); ?>" data-nombre="<?php echo htmlspecialchars($nombre); ?>" data-tipo="<?php echo $esDir ? 'carpeta' : 'archivo'; ?>" data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Eliminar" onclick="confirmarEliminar('<?php echo htmlspecialchars($rutaRelativa); ?>', '<?php echo htmlspecialchars($nombre); ?>', '<?php echo $esDir ? 'carpeta' : 'archivo'; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Función para aplicar filtros y ordenamiento
function aplicarFiltros() {
    const filtroTipo = document.getElementById('filtroTipo').value;
    const ordenarPor = document.getElementById('ordenarPor').value;
    const searchTerm = '<?php echo htmlspecialchars($search_term); ?>';
    
    // Construir URL con los parámetros
    let url = `index.php?buscar=${encodeURIComponent(searchTerm)}&tipo=${filtroTipo}&ordenar=${ordenarPor}`;
    
    // Redirigir a la nueva URL
    window.location.href = url;
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top',
            trigger: 'hover'
        });
    });
    
    // Hacer que las filas sean clickeables
    document.querySelectorAll('.search-result-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            // Solo navegar si no se hizo clic en un botón o enlace
            if (!e.target.closest('a') && !e.target.closest('button')) {
                const tipo = this.dataset.tipo;
                const ruta = this.dataset.ruta;
                
                if (tipo === 'carpeta') {
                    window.location.href = `index.php?modulo=explorador_windows&carpeta=${encodeURIComponent(ruta)}`;
                } else {
                    // Verificar si es editable
                    const extension = ruta.split('.').pop().toLowerCase();
                    const extensionesEditables = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'doc', 'docx', 'rtf', 'odt'];
                    
                    if (extensionesEditables.includes(extension)) {
                        window.location.href = `index.php?modulo=editar_archivo&archivo=${encodeURIComponent(ruta)}`;
                    }
                }
            }
        });
    });
});
</script>

<style>
.search-results-container {
    max-width: 100%;
    padding: 20px;
}

.search-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 15px;
}

.search-term {
    color: #007bff;
    font-weight: bold;
}

.search-stats {
    display: flex;
    gap: 10px;
}

.search-filters {
    background-color: #f8f9fa;
    border-radius: 8px;
}

.search-result-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.search-result-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.search-result-link:hover {
    text-decoration: underline;
}

.search-result-name {
    color: #212529;
    font-weight: 500;
}

/* Estilos para modo oscuro */
body.dark-mode .search-filters {
    background-color: #2d3748;
    color: #e2e8f0;
}

body.dark-mode .search-result-item:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

body.dark-mode .search-result-name {
    color: #e2e8f0;
}

body.dark-mode .table {
    color: #e2e8f0;
}

body.dark-mode .table-light {
    background-color: #4a5568;
    color: #e2e8f0;
}

body.dark-mode .search-term {
    color: #63b3ed;
}
</style>
