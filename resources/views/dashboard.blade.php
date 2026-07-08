@extends('layouts.app')

@section('content')

{{-- ===================== EXPLORADOR (default) ===================== --}}
@if($modulo === 'explorador_windows' || empty($modulo))

<div class="row">
    <!-- Columna Izquierda: Explorador (Estilo Linear) -->
    <div class="col-lg-8 mb-4">
        <div class="vista-controles p-2 d-flex align-items-center flex-wrap gap-2">
            <button id="vista-cuadricula-btn" class="vista-btn active">
                <i class="fas fa-th"></i> Cuadrícula
            </button>
            <button id="vista-lista-btn" class="vista-btn">
                <i class="fas fa-list"></i> Lista
            </button>
            <button id="toggle-actualizacion" class="btn btn-sm btn-outline-secondary ml-2">
                <i class="fas fa-pause"></i> Pausar actualización
            </button>
            <span class="ml-2 text-muted small" id="ultima-actualizacion"></span>
        </div>

        <div id="vista-explorador" class="vista-cuadricula" data-carpeta-actual="{{ request('carpeta', '') }}">
            <div class="text-center p-4">
                <i class="fas fa-spinner fa-spin"></i> Cargando archivos...
            </div>
        </div>

        <div id="context-menu" class="context-menu"></div>
    </div>

    <!-- Columna Derecha: Estado de Sistema & Widgets (Estilo Nexus OS) -->
    <div class="col-lg-4">
        <div id="preview-panel" class="preview-panel card shadow-sm d-none" style="background: var(--bg-card-dark); border: 1px solid var(--border-glass);">
            <div class="preview-panel-header card-header d-flex align-items-start justify-content-between py-2" style="background: rgba(255,255,255,0.01); border-bottom: 1px solid var(--border-glass);">
                <div class="preview-header-info" style="min-width:0; flex:1;">
                    <div class="preview-header-icon"><i class="fas fa-file"></i></div>
                    <h6 class="preview-header-name mb-0"></h6>
                    <small class="preview-header-meta text-muted"></small>
                </div>
                <button type="button" class="preview-close-btn" id="preview-panel-close" title="Cerrar vista previa" aria-label="Cerrar vista previa">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="preview-body card-body p-3" id="preview-panel-body"></div>
            <div class="preview-footer card-footer d-flex flex-wrap gap-2 py-2" style="background: rgba(255,255,255,0.01); border-top: 1px solid var(--border-glass);">
                <a href="#" class="btn btn-sm btn-outline-success preview-btn-download" target="_blank" rel="noopener">
                    <i class="fas fa-download"></i> Descargar
                </a>
                <a href="#" class="btn btn-sm btn-outline-info preview-btn-editar d-none">
                    <i class="fas fa-file-alt"></i> Editar
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary preview-btn-cerrar" id="preview-panel-close-footer">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>

        <div id="preview-widgets">
        <!-- Widget: Tiempo y Fecha -->
        <div class="card mb-4 shadow-sm" style="background: var(--bg-card-dark); border: 1px solid var(--border-glass);">
            <div class="card-body text-center py-4">
                <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; font-weight: 600;">System Time</div>
                <div id="system-clock" style="font-size: 2.15rem; font-weight: 800; color: #ffffff; margin: 0.25rem 0; font-family: monospace; letter-spacing: 1px;">00:00:00</div>
                <div id="system-date" style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">-- / -- / ----</div>
            </div>
        </div>

        <!-- Widget: Ficha de Servidor -->
        <div id="sys-overview-panel" class="card mb-4 shadow-sm" style="background: var(--bg-card-dark); border: 1px solid var(--border-glass);">
            <div class="card-header d-flex align-items-center justify-content-between py-2" style="background: rgba(255,255,255,0.01); border-bottom: 1px solid var(--border-glass);">
                <div>
                    <h6 class="mb-0" style="font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;"><i class="fas fa-chart-bar text-primary mr-2"></i> System Overview</h6>
                    <small id="sys-overview-host" class="text-muted" style="font-size: 0.6rem;">Cargando…</small>
                </div>
                <span id="sys-overview-badge" class="badge badge-secondary px-2 py-0.5" style="font-size: 0.6rem; border-radius: 4px;">…</span>
            </div>
            <div class="card-body p-3">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-muted small mb-1" style="font-size: 0.65rem; font-weight: 600;">CPU</div>
                        <div id="sys-cpu-value" style="font-size: 1.1rem; font-weight: 700; color: #fff;">—</div>
                        <small id="sys-cpu-detail" class="text-muted" style="font-size: 0.6rem;">…</small>
                    </div>
                    <div class="col-4 border-left border-right" style="border-color: var(--border-glass) !important;">
                        <div class="text-muted small mb-1" style="font-size: 0.65rem; font-weight: 600;">Memory</div>
                        <div id="sys-mem-value" style="font-size: 1.1rem; font-weight: 700; color: #fff;">—</div>
                        <small id="sys-mem-detail" class="text-muted" style="font-size: 0.6rem;">…</small>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small mb-1" style="font-size: 0.65rem; font-weight: 600;">Uptime</div>
                        <div id="sys-uptime-value" style="font-size: 1.1rem; font-weight: 700; color: #fff;">—</div>
                        <small id="sys-uptime-detail" class="text-muted" style="font-size: 0.6rem;">Tiempo activo</small>
                    </div>
                </div>

                <div class="border-top pt-3" style="border-top-color: var(--border-glass) !important;">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">Disco (NAS)</span>
                        <span id="sys-storage-label" style="font-size: 0.75rem; color: #ffffff; font-weight: 700;">—</span>
                    </div>
                    <div class="progress" style="height: 6px !important;">
                        <div id="sys-storage-bar" class="progress-bar" style="width: 0%; background: linear-gradient(90deg, var(--accent-teal) 0%, #22d3ee 100%) !important; transition: width 0.4s ease;"></div>
                    </div>
                    <small id="sys-storage-detail" class="text-muted d-block mt-1" style="font-size: 0.62rem;">Espacio del volumen donde se guarda el almacenamiento</small>
                </div>
            </div>
        </div>

        <!-- Widget: Acciones Rápidas -->
        <div class="card mb-4 shadow-sm" style="background: var(--bg-card-dark); border: 1px solid var(--border-glass);">
            <div class="card-header py-2" style="background: rgba(255,255,255,0.01); border-bottom: 1px solid var(--border-glass);">
                <h6 class="mb-0" style="font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;"><i class="fas fa-bolt text-warning mr-2"></i> Acciones Rápidas</h6>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-6 mb-2 pr-1">
                        <a href="{{ route('subir') }}" class="btn btn-funcion w-100 py-2 d-flex flex-column align-items-center justify-content-center text-decoration-none" style="gap: 4px; border-radius: 8px;">
                            <i class="fas fa-upload text-success" style="font-size: 1rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 500;">Subir Archivo</span>
                        </a>
                    </div>
                    @if(session('rol') === 'administrador' || session('rol') === 'supervisor')
                    <div class="col-6 mb-2 pl-1">
                        <a href="{{ route('dashboard') }}?modulo=crear_carpeta" class="btn btn-funcion w-100 py-2 d-flex flex-column align-items-center justify-content-center text-decoration-none" style="gap: 4px; border-radius: 8px;">
                            <i class="fas fa-folder-plus text-primary" style="font-size: 1rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 500;">Crear Carpeta</span>
                        </a>
                    </div>
                    @endif
                    <div class="col-6 pr-1">
                        <a href="{{ route('dashboard') }}?modulo=servidores" class="btn btn-funcion w-100 py-2 d-flex flex-column align-items-center justify-content-center text-decoration-none" style="gap: 4px; border-radius: 8px;">
                            <i class="fas fa-network-wired text-info" style="font-size: 1rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 500;">Servidores FTP</span>
                        </a>
                    </div>
                    <div class="col-6 pl-1">
                        <a href="{{ route('dashboard') }}?modulo=computadoras" class="btn btn-funcion w-100 py-2 d-flex flex-column align-items-center justify-content-center text-decoration-none" style="gap: 4px; border-radius: 8px;">
                            <i class="fas fa-desktop text-secondary" style="font-size: 1rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 500;">Computadoras</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </div>{{-- /preview-widgets --}}
    </div>
</div>

<script>
let intervaloActualizacion;
let actualizacionActiva = true;
let carpetaActual = '{{ request('carpeta', '') }}';
let isRemote = {{ isset($servidor) ? 'true' : 'false' }};
let apiBaseUrl = isRemote ? '/remoto/{{ isset($servidor) ? $servidor->id : "" }}' : '/explorador';
let viewBaseUrl = isRemote ? '/remoto/{{ isset($servidor) ? $servidor->id : "" }}' : '{{ route("dashboard") }}';

// ==================== BREADCRUMB DINÁMICO ====================
function updateBreadcrumb(ruta) {
    const container = document.getElementById('breadcrumb-path');
    if (!container) return;

    let html = `<span class="mr-2 text-muted"><i class="fas ${isRemote ? 'fa-globe' : 'fa-folder-open'} text-warning"></i> Ubicación:</span>
                <a href="${viewBaseUrl}" class="path-segment"><i class="fas ${isRemote ? 'fa-server' : 'fa-home'} text-primary"></i> Inicio ${isRemote ? '(Remoto)' : ''}</a>`;

    if (ruta && ruta.trim() !== '') {
        const segmentos = ruta.split('/').filter(s => s.trim() !== '');
        let rutaAcumulada = '';
        segmentos.forEach((segmento, i) => {
            rutaAcumulada += (rutaAcumulada ? '/' : '') + segmento;
            html += `<span class="path-separator mx-1"><i class="fas fa-chevron-right text-secondary" style="font-size:0.75rem;"></i></span>`;
            if (i === segmentos.length - 1) {
                html += `<span class="path-segment current font-weight-bold">
                            <i class="fas fa-folder-open text-warning"></i> ${segmento}
                         </span>`;
            } else {
                html += `<a href="${viewBaseUrl}?carpeta=${encodeURIComponent(rutaAcumulada)}" class="path-segment">
                            <i class="fas fa-folder text-warning"></i> ${segmento}
                         </a>`;
            }
        });
    }
    container.innerHTML = html;
}

// Inicializar breadcrumb con la ruta actual (si la hay)
updateBreadcrumb(carpetaActual);

// Detectar clase de icono según extensión de archivo
function getFileIconClass(nombre, iconoFallback) {
    const ext = (nombre.split('.').pop() || '').toLowerCase();
    const map = {
        pdf: 'file-icon-pdf',
        jpg: 'file-icon-img', jpeg: 'file-icon-img', png: 'file-icon-img', gif: 'file-icon-img', webp: 'file-icon-img', svg: 'file-icon-img', bmp: 'file-icon-img',
        doc: 'file-icon-doc', docx: 'file-icon-doc',
        xls: 'file-icon-xls', xlsx: 'file-icon-xls', csv: 'file-icon-xls',
        zip: 'file-icon-zip', rar: 'file-icon-zip', tar: 'file-icon-zip', gz: 'file-icon-zip', '7z': 'file-icon-zip',
        mp4: 'file-icon-video', mkv: 'file-icon-video', avi: 'file-icon-video', mov: 'file-icon-video', webm: 'file-icon-video',
        mp3: 'file-icon-audio', wav: 'file-icon-audio', flac: 'file-icon-audio', ogg: 'file-icon-audio',
        js: 'file-icon-code', ts: 'file-icon-code', php: 'file-icon-code', py: 'file-icon-code', html: 'file-icon-code', css: 'file-icon-code', json: 'file-icon-code', xml: 'file-icon-code', sh: 'file-icon-code', bat: 'file-icon-code', c: 'file-icon-code', cpp: 'file-icon-code'
    };
    return map[ext] || 'file-icon-other';
}

function renderItem(item, tipo) {
    if (tipo === 'carpeta') {
        let ctxBtns = '';
        if (item.puedeRenombrar) {
            ctxBtns += '<button type="button" class="action-btn" title="Renombrar" onclick="event.stopPropagation(); abrirModalRenombrarCarpeta(\'' + item.ruta + '\', \'' + item.nombre + '\')"><i class="fas fa-edit" style="font-size:0.75rem;"></i></button>';
        }
        if (item.puedeEliminar) {
            ctxBtns += '<button type="button" class="action-btn" style="border-color:rgba(239,68,68,0.3); color:#ef4444;" title="Eliminar" onclick="event.stopPropagation(); confirmarEliminar(\'' + item.ruta + '\', \'' + item.nombre + '\', \'carpeta\')"><i class="fas fa-trash" style="font-size:0.75rem;"></i></button>';
        }
        const ctxBtnHtml = ctxBtns ? '<div class="folder-card-actions d-flex justify-content-center" style="gap:4px;margin-top:4px;">' + ctxBtns + '</div>' : '';
        return '<div class="folder-card carpeta" data-ruta="' + item.ruta + '" data-nombre="' + item.nombre + '">' +
            '<div class="folder-icon icon-folder"><i class="fas fa-folder"></i></div>' +
            '<div class="folder-name">' + item.nombre + '</div>' +
            '<div style="font-size:0.62rem;color:#475569;">' + item.items + ' items</div>' +
            ctxBtnHtml +
        '</div>';
    } else {
        let acciones = '';
        const dlUrl = isRemote ? apiBaseUrl + '/descargar?archivo=' + encodeURIComponent(item.ruta) : '/descargar?archivo=' + encodeURIComponent(item.ruta);
        acciones += '<a href="' + dlUrl + '" class="action-btn" title="Descargar" onclick="event.stopPropagation();"><i class="fas fa-download" style="font-size:0.75rem; color:#34d399;"></i></a>';
        if (!isRemote && item.esTexto && item.puedeEditar) {
            acciones += '<a href="/dashboard?modulo=editar_archivo&archivo=' + encodeURIComponent(item.ruta) + '" class="action-btn" title="Editar" onclick="event.stopPropagation();"><i class="fas fa-file-alt" style="font-size:0.75rem; color:#67e8f9;"></i></a>';
        }
        if (item.puedeRenombrar) {
            acciones += '<button type="button" class="action-btn" title="Renombrar" onclick="event.stopPropagation(); abrirModalRenombrarArchivo(\'' + item.ruta + '\', \'' + item.nombre + '\')"><i class="fas fa-edit" style="font-size:0.75rem;"></i></button>';
        }
        if (item.puedeEliminar) {
            acciones += '<button type="button" class="action-btn" style="border-color:rgba(239,68,68,0.3); color:#ef4444;" title="Eliminar" onclick="event.stopPropagation(); confirmarEliminar(\'' + item.ruta + '\', \'' + item.nombre + '\', \'archivo\')"><i class="fas fa-trash" style="font-size:0.75rem;"></i></button>';
        }
        const iconClass = getFileIconClass(item.nombre, item.icono);
        const tipoPreview = item.tipoPreview || 'none';
        const puedePreview = item.puedePrevisualizar ? '1' : '0';
        return '<div class="file-row archivo" data-ruta="' + item.ruta + '" data-nombre="' + item.nombre + '" data-tipo-preview="' + tipoPreview + '" data-puede-preview="' + puedePreview + '" data-size="' + item.size + '" data-fecha="' + item.fecha + '" data-es-texto="' + (item.esTexto ? '1' : '0') + '" data-puede-editar="' + (item.puedeEditar ? '1' : '0') + '">' +
            '<div class="file-icon ' + iconClass + '"><i class="fas ' + item.icono + '"></i></div>' +
            '<div style="flex:1; min-width:0;">' +
                '<div style="font-size:0.82rem; font-weight:500; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + item.nombre + '</div>' +
                '<div style="font-size:0.68rem; color:#475569; margin-top:1px;">' + item.size + ' · ' + item.fecha + '</div>' +
            '</div>' +
            '<div class="d-flex" style="gap:4px; flex-shrink:0;">' + acciones + '</div>' +
        '</div>';
    }
}

function syncSidebarTree() {
    const root = document.getElementById('sidebar-nav');
    if (root) {
        root.dataset.carpetaActual = carpetaActual;
    }
    if (window.SidebarTree && typeof window.SidebarTree.refresh === 'function') {
        window.SidebarTree.refresh();
    }
}

function navegarExplorador(ruta) {
    if (window.FilePreview && typeof window.FilePreview.cerrar === 'function') {
        window.FilePreview.cerrar(true);
    }
    carpetaActual = ruta || '';
    const url = new URL(window.location.href);
    url.searchParams.set('carpeta', carpetaActual);
    url.searchParams.delete('preview');
    window.history.pushState({}, '', url);
    updateBreadcrumb(carpetaActual);
    syncSidebarTree();
    if (typeof cargarExplorador === 'function') {
        cargarExplorador();
    }
}
window.navegarExplorador = navegarExplorador;

function abrirPreviewDesdeUrl() {
    const preview = new URLSearchParams(window.location.search).get('preview');
    if (!preview || !window.FilePreview || typeof window.FilePreview.abrir !== 'function') return;
    let row = null;
    document.querySelectorAll('.file-row.archivo').forEach(function (el) {
        if (el.getAttribute('data-ruta') === preview) row = el;
    });
    if (row) {
        window.FilePreview.abrir({
            ruta: row.getAttribute('data-ruta') || '',
            nombre: row.getAttribute('data-nombre') || '',
            tipoPreview: row.getAttribute('data-tipo-preview') || 'none',
            puedePrevisualizar: row.getAttribute('data-puede-preview') === '1',
            size: row.getAttribute('data-size') || '',
            fecha: row.getAttribute('data-fecha') || '',
            esTexto: row.getAttribute('data-es-texto') === '1',
            puedeEditar: row.getAttribute('data-puede-editar') === '1',
        }, row);
    }
}

function cargarExplorador() {
    if (!actualizacionActiva) return;
    fetch(`${apiBaseUrl}/datos?carpeta=${encodeURIComponent(carpetaActual)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(async r => {
        if (!r.ok) {
            const errText = await r.text();
            throw new Error(`Error HTTP: ${r.status}`);
        }
        return r.json();
    })
    .then(data => {
        if (data.error) {
            document.getElementById('vista-explorador').innerHTML = 
                `<div class="p-4 text-danger text-center"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><h5>Error de conexión</h5><p>${data.error}</p></div>`;
            return;
        }

        let folderHtml = '';
        let fileHtml = '';
        data.carpetas.forEach(c => folderHtml += renderItem(c, 'carpeta'));
        data.archivos.forEach(a => fileHtml += renderItem(a, 'archivo'));

        let html = '';
        if (folderHtml) {
            html += '<div class="folder-grid">' + folderHtml + '</div>';
            if (fileHtml) html += '<hr style="border-color: rgba(255,255,255,0.05); margin: 1rem 0;">';
        }
        if (fileHtml) {
            html += '<div class="file-list">' + fileHtml + '</div>';
        }
        if (!folderHtml && !fileHtml) {
            html = '<div class="p-5 text-center" style="color:#374151;"><i class="fas fa-folder-open fa-3x mb-3" style="color:#1e293b;"></i><p style="font-size:0.85rem;">Esta carpeta está vacía</p></div>';
        }
        document.getElementById('vista-explorador').innerHTML = html;
        document.getElementById('ultima-actualizacion').innerHTML =
            '<i class="fas fa-circle" style="font-size:0.5rem; color:#3AB397;"></i> ' + new Date().toLocaleTimeString();
        if (window.FilePreview && typeof window.FilePreview.onExplorerReload === 'function') {
            window.FilePreview.onExplorerReload();
        }
        abrirPreviewDesdeUrl();
    })
    .catch(err => {
        console.error('Error al actualizar:', err);
        document.getElementById('vista-explorador').innerHTML = 
            `<div class="p-4 text-danger text-center"><i class="fas fa-times-circle fa-3x mb-3"></i><h5>Fallo de comunicación</h5><p>No se pudo recibir respuesta del servidor. Verifica que el FTP de la consola esté encendido.</p></div>`;
    });
}

function iniciarActualizacion() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    cargarExplorador();
    // En modo remoto, aumentamos el tiempo de actualización para no saturar la red SSH
    const tiempoRefresh = isRemote ? 8000 : 3000;
    intervaloActualizacion = setInterval(cargarExplorador, tiempoRefresh);
}

function detenerActualizacion() {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
        intervaloActualizacion = null;
    }
}

document.getElementById('toggle-actualizacion').addEventListener('click', function() {
    actualizacionActiva = !actualizacionActiva;
    if (actualizacionActiva) {
        this.innerHTML = '<i class="fas fa-pause"></i> Pausar actualización';
        this.classList.replace('btn-outline-danger', 'btn-outline-secondary');
        iniciarActualizacion();
    } else {
        this.innerHTML = '<i class="fas fa-play"></i> Reanudar actualización';
        this.classList.replace('btn-outline-secondary', 'btn-outline-danger');
        detenerActualizacion();
    }
});

document.addEventListener('click', function(e) {
    const carpeta = e.target.closest('.carpeta');
    if (carpeta && !e.target.closest('.action-btn') && !e.target.closest('.folder-card-actions')) {
        e.preventDefault();
        if (window.FilePreview && typeof window.FilePreview.cerrar === 'function') {
            window.FilePreview.cerrar(true);
        }
        const nuevaRuta = carpeta.getAttribute('data-ruta');
        if (nuevaRuta) {
            carpetaActual = nuevaRuta;
            const url = new URL(window.location);
            url.searchParams.set('carpeta', nuevaRuta);
            window.history.pushState({}, '', url);
            updateBreadcrumb(carpetaActual);
            syncSidebarTree();
            cargarExplorador();
        }
    }
});

window.addEventListener('popstate', function() {
    carpetaActual = new URLSearchParams(window.location.search).get('carpeta') || '';
    updateBreadcrumb(carpetaActual);
    syncSidebarTree();
    cargarExplorador();
});

document.addEventListener('DOMContentLoaded', iniciarActualizacion);
document.addEventListener('visibilitychange', function() {
    if (document.hidden) detenerActualizacion();
    else if (actualizacionActiva) iniciarActualizacion();
});

let _rutaActual = '', _tipoActual = '';

function confirmarEliminar(ruta, nombre, tipo) {
    _rutaActual = ruta;
    _tipoActual = tipo;
    document.getElementById('nombreElementoEliminar').textContent = nombre;
    
    if (isRemote) {
        document.querySelector('#modalConfirmarEliminar .alert-warning').innerHTML = 
            '<i class="fas fa-exclamation-triangle"></i> <strong>¡Atención!</strong> El elemento será eliminado permanentemente del servidor remoto.';
    } else {
        document.querySelector('#modalConfirmarEliminar .alert-warning').innerHTML = 
            '<i class="fas fa-exclamation-triangle"></i> <strong>¡Atención!</strong> El elemento será movido a la papelera.';
    }
    
    $('#modalConfirmarEliminar').modal('show');
}

$(document).on('click', '#btnConfirmarEliminar', function() {
    $('#modalConfirmarEliminar').modal('hide');
    fetch(`${apiBaseUrl}/eliminar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ruta: _rutaActual, tipo: _tipoActual })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) cargarExplorador();
        else alert('Error: ' + data.message);
    });
});

function abrirModalRenombrarArchivo(ruta, nombre) {
    _rutaActual = ruta;
    const nombreSinExt = nombre.replace(/\.[^/.]+$/, '');
    document.getElementById('nuevo_nombre_archivo').value = nombreSinExt;
    document.querySelector('#rutaActualArchivo span').textContent = nombre;
    $('#modalRenombrarArchivo').modal('show');
}

$(document).on('click', '#btnGuardarRenombrarArchivo', function() {
    const nuevoNombre = document.getElementById('nuevo_nombre_archivo').value.trim();
    if (!nuevoNombre) return alert('Ingresa un nombre válido.');
    $('#modalRenombrarArchivo').modal('hide');
    fetch(`${apiBaseUrl}/renombrar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ruta: _rutaActual, nuevo_nombre: nuevoNombre, tipo_elemento: 'archivo' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) cargarExplorador();
        else alert('Error: ' + data.message);
    });
});

function abrirModalRenombrarCarpeta(ruta, nombre) {
    _rutaActual = ruta;
    document.getElementById('nuevo_nombre').value = nombre;
    document.querySelector('#rutaActual span').textContent = ruta;
    $('#modalRenombrar').modal('show');
}

$(document).on('click', '#btnGuardarRenombrarCarpeta', function() {
    const nuevoNombre = document.getElementById('nuevo_nombre').value.trim();
    if (!nuevoNombre) return alert('Ingresa un nombre válido.');
    $('#modalRenombrar').modal('hide');
    fetch(`${apiBaseUrl}/renombrar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ruta: _rutaActual, nuevo_nombre: nuevoNombre, tipo_elemento: 'carpeta' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) cargarExplorador();
        else alert('Error: ' + data.message);
    });
});

function crearCarpetaRemota() {
    $('#modalCrearCarpetaRemota').modal('show');
    $('#nuevo_nombre_carpeta_remota').val('');
    setTimeout(() => document.getElementById('nuevo_nombre_carpeta_remota').focus(), 500);
}

$(document).on('click', '#btnGuardarCarpetaRemota', function() {
    const nombre = document.getElementById('nuevo_nombre_carpeta_remota').value.trim();
    if (!nombre) return alert('Ingresa un nombre para la carpeta.');
    
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');

    fetch(`${apiBaseUrl}/crear-carpeta`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ nombre: nombre, carpeta_padre: carpetaActual })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#modalCrearCarpetaRemota').modal('hide');
            cargarExplorador();
        } else {
            alert('Error: ' + data.message);
        }
        btn.prop('disabled', false).html('Crear carpeta');
    })
    .catch(() => {
        alert('Error de conexión al crear carpeta.');
        btn.prop('disabled', false).html('Crear carpeta');
    });
});

function abrirModalSubirRemoto() {
    $('#archivo_subir_remoto').val('');
    $('#barra_progreso_remoto').css('width', '0%').text('0%');
    $('#container_progreso_remoto').hide();
    $('#modalSubirRemoto').modal('show');
}

$(document).on('click', '#btnSubirRemoto', function() {
    const input = document.getElementById('archivo_subir_remoto');
    if (!input.files || input.files.length === 0) return alert('Selecciona un archivo.');
    
    const file = input.files[0];
    const formData = new FormData();
    formData.append('archivo', file);
    formData.append('carpeta_destino', carpetaActual);
    
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
    
    $('#container_progreso_remoto').show();
    $('#barra_progreso_remoto').css('width', '50%').text('Subiendo (por favor espera)...');

    fetch(`${apiBaseUrl}/subir`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#barra_progreso_remoto').css('width', '100%').removeClass('bg-primary').addClass('bg-success').text('¡Completado!');
            setTimeout(() => {
                $('#modalSubirRemoto').modal('hide');
                cargarExplorador();
            }, 1000);
        } else {
            alert('Error: ' + data.message);
            $('#container_progreso_remoto').hide();
        }
        btn.prop('disabled', false).html('Subir Archivo');
    })
    .catch(() => {
        alert('Error de conexión o el archivo es demasiado grande.');
        $('#container_progreso_remoto').hide();
    });
});

</script>

{{-- Modal subir archivo remoto --}}
<div class="modal fade" id="modalSubirRemoto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-upload"></i> Subir a Servidor Remoto</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Los archivos se subirán a la carpeta que estás visualizando actualmente.
                </div>
                <div class="form-group">
                    <label>Selecciona un archivo:</label>
                    <input type="file" class="form-control-file" id="archivo_subir_remoto">
                </div>
                <div class="progress mt-3" id="container_progreso_remoto" style="display: none; height: 25px;">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="barra_progreso_remoto" role="progressbar" style="width: 0%;">0%</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnSubirRemoto">Subir Archivo</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal crear carpeta remota --}}
<div class="modal fade" id="modalCrearCarpetaRemota" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-folder-plus"></i> Crear carpeta</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre de la nueva carpeta:</label>
                    <input type="text" class="form-control" id="nuevo_nombre_carpeta_remota" placeholder="Ej: Nueva_Carpeta" autocomplete="off">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCarpetaRemota">Crear carpeta</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal renombrar carpeta --}}
<div class="modal fade" id="modalRenombrar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Renombrar carpeta</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="rutaActual" class="mb-3"><strong>Ruta:</strong> <span class="text-muted"></span></div>
                <div class="form-group">
                    <label>Nuevo nombre:</label>
                    <input type="text" class="form-control" id="nuevo_nombre">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarRenombrarCarpeta">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal renombrar archivo --}}
<div class="modal fade" id="modalRenombrarArchivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Renombrar archivo</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="rutaActualArchivo" class="mb-3"><strong>Archivo:</strong> <span class="text-muted"></span></div>
                <div class="form-group">
                    <label>Nuevo nombre (sin extensión):</label>
                    <input type="text" class="form-control" id="nuevo_nombre_archivo">
                    <small class="form-text text-muted">La extensión se mantendrá automáticamente.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarRenombrarArchivo">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal confirmar eliminar --}}
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡Atención!</strong> El elemento será movido a la papelera.
                </div>
                <p><strong>Elemento:</strong> <span class="text-danger font-weight-bold" id="nombreElementoEliminar"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.FilePreviewConfig = {
    previsualizarUrl : '{{ route("explorador.previsualizar") }}',
    contenidoUrl     : '{{ route("explorador.contenidoArchivo") }}',
    descargarUrl     : '{{ route("descargar") }}',
    editarBaseUrl    : '{{ route("dashboard") }}?modulo=editar_archivo&archivo=',
    isRemote         : {{ isset($servidor) ? 'true' : 'false' }},
};
</script>
<script src="{{ asset('Js/file-preview.js') }}?v={{ filemtime(public_path('Js/file-preview.js')) }}"></script>
<script src="{{ asset('Js/system-overview.js') }}?v={{ filemtime(public_path('Js/system-overview.js')) }}"></script>
@endpush

{{-- ===================== PAPELERA ===================== --}}
@elseif($modulo === 'papelera')
    @include('modulos.papelera')

{{-- ===================== CREAR CARPETA ===================== --}}
@elseif($modulo === 'crear_carpeta')
    @include('modulos.crear_carpeta', ['todasLasCarpetas' => $todasLasCarpetas, 'carpeta' => $carpeta])

{{-- ===================== CREAR ARCHIVO ===================== --}}
@elseif($modulo === 'crear_archivo')
    @include('modulos.crear_archivo', ['todasLasCarpetas' => $todasLasCarpetas])

{{-- ===================== EDITAR ARCHIVO ===================== --}}
@elseif($modulo === 'editar_archivo')
    @include('modulos.editar_archivo', ['datos' => $datos])

{{-- ===================== COPIAR / MOVER ===================== --}}
@elseif($modulo === 'copiarmover')
    @include('modulos.copiarmover', ['todasLasCarpetas' => $todasLasCarpetas])

{{-- ===================== BUSCAR ===================== --}}
@elseif($modulo === 'buscar')
    @include('modulos.busqueda', ['termino' => $termino])

{{-- ===================== USUARIOS ===================== --}}
@elseif($modulo === 'usuarios')
    @include('modulos.usuarios', ['usuarios' => $usuarios])

{{-- ===================== SERVIDORES ===================== --}}
@elseif($modulo === 'servidores')
    @include('modulos.servidores', ['servidores' => $servidores])

{{-- ===================== COMPUTADORAS ===================== --}}
@elseif($modulo === 'computadoras')
    @include('modulos.computadoras', ['computadoras' => $computadoras])

@endif

@endsection