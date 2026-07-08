/**
 * file-preview.js — Vista previa de archivos en el explorador local
 */
(function () {
    'use strict';

    const config = window.FilePreviewConfig || {};
    if (config.isRemote) return;

    const DESKTOP_MQ = window.matchMedia('(min-width: 992px)');

    let estadoActual = null;
    let abierto = false;
    let inicializado = false;

    function el(id) {
        return document.getElementById(id);
    }

    function getPanel() { return el('preview-panel'); }
    function getWidgets() { return el('preview-widgets'); }
    function getPanelBody() { return el('preview-panel-body'); }
    function getModalBody() { return el('preview-modal-body'); }
    function getModal() { return el('modalPreview'); }

    function esDesktop() {
        return DESKTOP_MQ.matches;
    }

    function contenedorActivo() {
        return esDesktop() ? getPanelBody() : getModalBody();
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function getIconClass(nombre) {
        const ext = (nombre.split('.').pop() || '').toLowerCase();
        const map = {
            pdf: 'file-icon-pdf',
            jpg: 'file-icon-img', jpeg: 'file-icon-img', png: 'file-icon-img', gif: 'file-icon-img',
            webp: 'file-icon-img', svg: 'file-icon-img', bmp: 'file-icon-img',
            doc: 'file-icon-doc', docx: 'file-icon-doc',
            xls: 'file-icon-xls', xlsx: 'file-icon-xls', csv: 'file-icon-xls',
            zip: 'file-icon-zip', rar: 'file-icon-zip', tar: 'file-icon-zip', gz: 'file-icon-zip', '7z': 'file-icon-zip',
            mp4: 'file-icon-video', mkv: 'file-icon-video', avi: 'file-icon-video', mov: 'file-icon-video', webm: 'file-icon-video',
            mp3: 'file-icon-audio', wav: 'file-icon-audio', flac: 'file-icon-audio', ogg: 'file-icon-audio',
            js: 'file-icon-code', ts: 'file-icon-code', php: 'file-icon-code', py: 'file-icon-code',
            html: 'file-icon-code', css: 'file-icon-code', json: 'file-icon-code', xml: 'file-icon-code',
        };
        return map[ext] || 'file-icon-other';
    }

    function leerMetadata(row) {
        return {
            ruta: row.getAttribute('data-ruta') || '',
            nombre: row.getAttribute('data-nombre') || '',
            tipoPreview: row.getAttribute('data-tipo-preview') || 'none',
            puedePrevisualizar: row.getAttribute('data-puede-preview') === '1',
            size: row.getAttribute('data-size') || '',
            fecha: row.getAttribute('data-fecha') || '',
            esTexto: row.getAttribute('data-es-texto') === '1',
            puedeEditar: row.getAttribute('data-puede-editar') === '1',
        };
    }

    function deseleccionarFilas() {
        document.querySelectorAll('.file-row.archivo.selected').forEach(function (node) {
            node.classList.remove('selected');
        });
    }

    function actualizarHeader(meta, shell) {
        if (!shell) return;
        const iconWrap = shell.querySelector('.preview-header-icon');
        const nameEl = shell.querySelector('.preview-header-name');
        const metaEl = shell.querySelector('.preview-header-meta');
        const iconClass = getIconClass(meta.nombre);

        if (iconWrap) {
            iconWrap.className = 'preview-header-icon file-icon ' + iconClass;
            iconWrap.innerHTML = '<i class="fas fa-file"></i>';
        }
        if (nameEl) {
            nameEl.textContent = meta.nombre;
            nameEl.title = meta.nombre;
        }
        if (metaEl) {
            metaEl.textContent = meta.size + ' · ' + meta.fecha;
        }

        shell.querySelectorAll('.preview-btn-download').forEach(function (btn) {
            btn.href = config.descargarUrl + '?archivo=' + encodeURIComponent(meta.ruta);
        });

        shell.querySelectorAll('.preview-btn-editar').forEach(function (btn) {
            if (meta.esTexto && meta.puedeEditar) {
                btn.classList.remove('d-none');
                btn.href = config.editarBaseUrl + encodeURIComponent(meta.ruta);
            } else {
                btn.classList.add('d-none');
            }
        });
    }

    function mostrarLoading(container) {
        container.innerHTML =
            '<div class="preview-skeleton text-center py-4">' +
                '<i class="fas fa-spinner fa-spin" style="font-size:1.5rem; color:#3AB397;"></i>' +
                '<p class="mt-2 mb-0" style="font-size:0.8rem; color:#64748b;">Cargando vista previa...</p>' +
            '</div>';
    }

    function mostrarError(container, mensaje) {
        container.innerHTML =
            '<div class="preview-empty text-center py-4">' +
                '<i class="fas fa-exclamation-circle" style="font-size:2rem; color:#ef4444; opacity:0.7;"></i>' +
                '<p class="mt-2 mb-0" style="font-size:0.82rem; color:#94a3b8;">' + escapeHtml(mensaje) + '</p>' +
            '</div>';
    }

    function mostrarNoSoportado(container, meta) {
        const iconClass = getIconClass(meta.nombre);
        container.innerHTML =
            '<div class="preview-empty text-center py-4">' +
                '<div class="file-icon ' + iconClass + '" style="width:56px;height:56px;font-size:1.4rem;margin:0 auto 1rem;border-radius:12px;">' +
                    '<i class="fas fa-file"></i>' +
                '</div>' +
                '<p class="mb-1" style="font-size:0.85rem; color:#e2e8f0; font-weight:500;">Vista previa no disponible</p>' +
                '<p class="mb-0" style="font-size:0.75rem; color:#64748b;">Este tipo de archivo no se puede previsualizar aquí.</p>' +
            '</div>';
    }

    function urlPrevisualizar(ruta) {
        return config.previsualizarUrl + '?archivo=' + encodeURIComponent(ruta);
    }

    function renderContenido(container, meta) {
        const tipo = meta.tipoPreview;

        if (!meta.puedePrevisualizar || tipo === 'none') {
            mostrarNoSoportado(container, meta);
            return Promise.resolve();
        }

        mostrarLoading(container);

        if (tipo === 'text') {
            return fetch(config.contenidoUrl + '?ruta=' + encodeURIComponent(meta.ruta) + '&preview=1', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    mostrarError(container, data.message || 'No se pudo cargar el contenido.');
                    return;
                }
                let aviso = '';
                if (data.truncado) {
                    aviso = '<div class="preview-truncado"><i class="fas fa-info-circle"></i> Mostrando los primeros 200 KB del archivo.</div>';
                }
                container.innerHTML = aviso + '<pre class="preview-code"><code></code></pre>';
                container.querySelector('code').textContent = data.contenido || '';
            })
            .catch(function () {
                mostrarError(container, 'Error al cargar el contenido del archivo.');
            });
        }

        const src = urlPrevisualizar(meta.ruta);

        if (tipo === 'image') {
            container.innerHTML = '<div class="preview-media-wrap"><img src="' + escapeHtml(src) + '" alt="' + escapeHtml(meta.nombre) + '"></div>';
            return Promise.resolve();
        }

        if (tipo === 'pdf') {
            container.innerHTML =
                '<div class="preview-pdf-wrap">' +
                    '<iframe src="' + escapeHtml(src) + '" title="' + escapeHtml(meta.nombre) + '" sandbox="allow-same-origin allow-scripts"></iframe>' +
                '</div>';
            return Promise.resolve();
        }

        if (tipo === 'video') {
            container.innerHTML =
                '<div class="preview-media-wrap">' +
                    '<video controls preload="metadata" src="' + escapeHtml(src) + '">Tu navegador no soporta video.</video>' +
                '</div>';
            return Promise.resolve();
        }

        if (tipo === 'audio') {
            container.innerHTML =
                '<div class="preview-audio-wrap">' +
                    '<div class="preview-audio-icon"><i class="fas fa-music"></i></div>' +
                    '<audio controls preload="metadata" src="' + escapeHtml(src) + '">Tu navegador no soporta audio.</audio>' +
                '</div>';
            return Promise.resolve();
        }

        mostrarNoSoportado(container, meta);
        return Promise.resolve();
    }

    function abrirUI() {
        const panel = getPanel();
        const widgets = getWidgets();
        const modal = getModal();

        if (esDesktop()) {
            if (panel) panel.classList.remove('d-none');
            if (widgets) widgets.classList.add('d-none');
            if (modal && typeof $ !== 'undefined') {
                $('#modalPreview').modal('hide');
            }
        } else if (modal && typeof $ !== 'undefined') {
            $('#modalPreview').modal('show');
            if (panel) panel.classList.add('d-none');
            if (widgets) widgets.classList.remove('d-none');
        }
        abierto = true;
    }

    function cerrarUI(silencioso) {
        const panel = getPanel();
        const widgets = getWidgets();
        const modal = getModal();

        if (panel) panel.classList.add('d-none');
        if (widgets) widgets.classList.remove('d-none');
        if (modal && typeof $ !== 'undefined') {
            $('#modalPreview').modal('hide');
        }
        if (!silencioso) {
            deseleccionarFilas();
        }
        estadoActual = null;
        abierto = false;
    }

    function abrir(meta, row) {
        estadoActual = meta;
        deseleccionarFilas();
        if (row) row.classList.add('selected');

        abrirUI();
        actualizarHeader(meta, getPanel());
        actualizarHeader(meta, getModal());

        const container = contenedorActivo();
        if (container) {
            renderContenido(container, meta);
        }
    }

    function cerrar(silencioso) {
        cerrarUI(silencioso);
        if (!silencioso) deseleccionarFilas();
    }

    function onExplorerReload() {
        if (!abierto || !estadoActual) return;

        if (document.querySelector('.tree-row.is-file.previewing')) {
            return;
        }

        const ruta = estadoActual.ruta;
        let row = null;
        document.querySelectorAll('.file-row.archivo').forEach(function (node) {
            if (node.getAttribute('data-ruta') === ruta) row = node;
        });

        if (!row) {
            cerrar(true);
            return;
        }

        deseleccionarFilas();
        row.classList.add('selected');
    }

    function initEventos() {
        if (inicializado) return;

        const explorador = el('vista-explorador');
        const panel = getPanel();
        const modal = getModal();

        if (!panel && !modal) return;

        inicializado = true;

        if (explorador) {
            explorador.addEventListener('click', function (e) {
                const row = e.target.closest('.file-row.archivo');
                if (!row) return;
                if (e.target.closest('.action-btn') || e.target.closest('a') || e.target.closest('button')) return;

                e.preventDefault();
                e.stopPropagation();
                abrir(leerMetadata(row), row);
            });
        }

        el('preview-panel-close')?.addEventListener('click', function () { cerrar(); });
        el('preview-panel-close-footer')?.addEventListener('click', function () { cerrar(); });

        if (modal && typeof $ !== 'undefined') {
            $(modal).on('hidden.bs.modal', function () {
                if (!esDesktop() && abierto) {
                    cerrar();
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && abierto) {
                cerrar();
            }
        });

        DESKTOP_MQ.addEventListener('change', function () {
            if (!abierto || !estadoActual) return;
            abrir(estadoActual, document.querySelector('.file-row.archivo.selected'));
        });
    }

    function boot() {
        initEventos();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.FilePreview = {
        abrir: abrir,
        cerrar: cerrar,
        onExplorerReload: onExplorerReload,
    };
})();
