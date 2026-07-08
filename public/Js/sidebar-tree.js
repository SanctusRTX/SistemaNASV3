/**
 * Árbol de navegación lateral estilo Explorador de Windows
 * Expandir carpetas y previsualizar archivos sin entrar en la carpeta principal
 */
(function () {
    'use strict';

    let sidebarBrowsePath = '';

    const ICON_CLASS_MAP = {
        pdf: 'icon-pdf',
        jpg: 'icon-img', jpeg: 'icon-img', png: 'icon-img', gif: 'icon-img', webp: 'icon-img', svg: 'icon-img', bmp: 'icon-img',
        doc: 'icon-doc', docx: 'icon-doc',
        xls: 'icon-xls', xlsx: 'icon-xls', csv: 'icon-xls',
        zip: 'icon-zip', rar: 'icon-zip', '7z': 'icon-zip', tar: 'icon-zip', gz: 'icon-zip',
        mp4: 'icon-video', mkv: 'icon-video', avi: 'icon-video', mov: 'icon-video', webm: 'icon-video',
        mp3: 'icon-audio', wav: 'icon-audio', flac: 'icon-audio', ogg: 'icon-audio',
        js: 'icon-code', ts: 'icon-code', php: 'icon-code', py: 'icon-code', html: 'icon-code', css: 'icon-code', json: 'icon-code', xml: 'icon-code',
        txt: 'icon-txt', md: 'icon-txt', log: 'icon-txt', rtf: 'icon-txt'
    };

    function getSidebarRoot() {
        return document.getElementById('sidebar-nav');
    }

    function getIconClass(nombre) {
        const ext = (nombre.split('.').pop() || '').toLowerCase();
        return ICON_CLASS_MAP[ext] || 'icon-other';
    }

    function cssEscape(str) {
        if (window.CSS && typeof CSS.escape === 'function') {
            return CSS.escape(str);
        }
        return String(str).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function dashboardUrl(params) {
        const base = window.location.pathname.includes('dashboard') ? window.location.pathname : '/dashboard';
        const qs = new URLSearchParams(params);
        return base + '?' + qs.toString();
    }

    function getCarpetaActual() {
        const root = getSidebarRoot();
        if (root && root.dataset.carpetaActual !== undefined) {
            return root.dataset.carpetaActual || '';
        }
        return new URLSearchParams(window.location.search).get('carpeta') || '';
    }

    function setSidebarBrowsePath(ruta) {
        sidebarBrowsePath = normalizePath(ruta);
        buildPathTabs();
    }

    function normalizePath(p) {
        return (p || '').replace(/\\/g, '/').replace(/^\/+|\/+$/g, '');
    }

    function isPathActive(ruta) {
        const actual = normalizePath(getCarpetaActual());
        return actual === normalizePath(ruta);
    }

    function isPathAncestor(ruta) {
        const ref = sidebarBrowsePath || normalizePath(getCarpetaActual());
        const check = normalizePath(ruta);
        if (!ref || !check) return false;
        return ref === check || ref.startsWith(check + '/');
    }

    function fetchContenido(carpeta) {
        return fetch('/explorador/subcarpetas?carpeta=' + encodeURIComponent(carpeta), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
    }

    function buildFolderNode(carpeta) {
        const ruta = typeof carpeta === 'string' ? carpeta : carpeta.ruta;
        const nombre = typeof carpeta === 'string' ? carpeta.split('/').pop() : carpeta.nombre;
        const tieneHijos = typeof carpeta === 'object' ? (carpeta.tiene_hijos ?? carpeta.tiene_subcarpetas ?? true) : true;
        const active = isPathActive(ruta);
        const ancestor = isPathAncestor(ruta);
        const expanded = ancestor && normalizePath(sidebarBrowsePath || getCarpetaActual()) !== normalizePath(ruta);

        const li = document.createElement('li');
        li.className = 'tree-node' + (expanded || active ? ' expanded' : '');
        li.dataset.ruta = ruta;
        li.dataset.tipo = 'carpeta';

        li.innerHTML =
            '<div class="tree-row' + (active ? ' active' : '') + '" data-ruta="' + escapeHtml(ruta) + '">' +
                '<button type="button" class="tree-toggle' + (tieneHijos ? (expanded || active ? ' expanded' : '') : ' placeholder') + '" aria-label="Expandir o contraer" title="Expandir / contraer">' +
                    (tieneHijos ? '<i class="fas fa-chevron-right"></i>' : '') +
                '</button>' +
                '<span class="tree-icon icon-folder' + (expanded || active ? ' icon-folder-open' : '') + '">' +
                    '<i class="fas fa-folder' + (expanded || active ? '-open' : '') + '"></i>' +
                '</span>' +
                '<span class="tree-label" role="button" tabindex="0" data-ruta="' + escapeHtml(ruta) + '" title="' + escapeHtml(nombre) + ' (clic: expandir · doble clic: abrir)">' + escapeHtml(nombre) + '</span>' +
            '</div>' +
            '<ul class="tree-children">' +
                (tieneHijos ? '<li class="tree-loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</li>' : '') +
            '</ul>';

        return li;
    }

    function buildFileRow(archivo, carpetaPadre) {
        const iconClass = getIconClass(archivo.nombre);
        const puedePreview = archivo.puedePrevisualizar ? '1' : '0';
        const li = document.createElement('li');
        li.className = 'tree-node';
        li.dataset.ruta = archivo.ruta;
        li.dataset.tipo = 'archivo';
        li.dataset.carpetaPadre = carpetaPadre;

        li.innerHTML =
            '<div class="tree-row is-file" data-archivo="' + escapeHtml(archivo.ruta) + '" ' +
                'data-nombre="' + escapeHtml(archivo.nombre) + '" ' +
                'data-tipo-preview="' + escapeHtml(archivo.tipoPreview || 'none') + '" ' +
                'data-puede-preview="' + puedePreview + '" ' +
                'data-size="' + escapeHtml(archivo.size || '') + '">' +
                '<button type="button" class="tree-toggle placeholder" tabindex="-1"></button>' +
                '<span class="tree-icon ' + iconClass + '"><i class="fas ' + escapeHtml(archivo.icono || 'fa-file') + '"></i></span>' +
                '<span class="tree-label" role="button" tabindex="0" title="' + escapeHtml(archivo.nombre + ' · ' + (archivo.size || '') + ' — clic: vista previa') + '">' + escapeHtml(archivo.nombre) + '</span>' +
            '</div>';

        return li;
    }

    function renderContenido(container, data, carpetaPadre) {
        container.innerHTML = '';
        const carpetas = data.carpetas || data.subcarpetas || [];
        const archivos = data.archivos || [];

        if (carpetas.length === 0 && archivos.length === 0) {
            container.innerHTML = '<li class="tree-empty">Carpeta vacía</li>';
            return;
        }

        carpetas.forEach(function (c) {
            container.appendChild(buildFolderNode(c));
        });

        archivos.forEach(function (a) {
            container.appendChild(buildFileRow(a, carpetaPadre));
        });

        if (data.archivos_truncados) {
            const more = document.createElement('li');
            more.className = 'tree-more';
            const restantes = (data.total_archivos || 0) - archivos.length;
            more.innerHTML = '<span class="tree-label" role="button" tabindex="0" data-abrir-carpeta="' + escapeHtml(carpetaPadre) + '">+' + restantes + ' archivos más…</span>';
            container.appendChild(more);
        }
    }

    function loadNodeChildren(node, force) {
        const ruta = node.dataset.ruta;
        const container = node.querySelector(':scope > .tree-children');
        if (!container) return Promise.resolve();

        if (container.dataset.loaded === '1' && !force) {
            return Promise.resolve();
        }

        container.innerHTML = '<li class="tree-loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</li>';

        return fetchContenido(ruta).then(function (data) {
            if (!data.success) throw new Error(data.message || 'Error');
            renderContenido(container, data, ruta);
            container.dataset.loaded = '1';
        }).catch(function (err) {
            container.innerHTML = '<li class="tree-error">' + escapeHtml(err.message || 'Error de conexión') + '</li>';
        });
    }

    function expandNode(node, loadChildren) {
        node.classList.add('expanded');
        const toggle = node.querySelector(':scope > .tree-row > .tree-toggle');
        const icon = node.querySelector(':scope > .tree-row > .tree-icon');
        if (toggle && !toggle.classList.contains('placeholder')) {
            toggle.classList.add('expanded');
        }
        if (icon) {
            icon.classList.add('icon-folder-open');
            icon.innerHTML = '<i class="fas fa-folder-open"></i>';
        }
        setSidebarBrowsePath(node.dataset.ruta);
        if (loadChildren !== false) {
            return loadNodeChildren(node);
        }
        return Promise.resolve();
    }

    function collapseNode(node) {
        node.classList.remove('expanded');
        const toggle = node.querySelector(':scope > .tree-row > .tree-toggle');
        const icon = node.querySelector(':scope > .tree-row > .tree-icon');
        if (toggle) toggle.classList.remove('expanded');
        if (icon) {
            icon.classList.remove('icon-folder-open');
            icon.innerHTML = '<i class="fas fa-folder"></i>';
        }
    }

    function findFolderNode(ruta) {
        return document.querySelector('.tree-node[data-tipo="carpeta"][data-ruta="' + cssEscape(normalizePath(ruta)) + '"]');
    }

    function expandToPath(targetRuta, scroll) {
        const target = normalizePath(targetRuta);
        setSidebarBrowsePath(target);

        if (!target) {
            updateActiveStates();
            if (scroll !== false) scrollToActive();
            return Promise.resolve();
        }

        const parts = target.split('/');
        let chain = Promise.resolve();
        let acum = '';

        parts.forEach(function (part) {
            acum = acum ? acum + '/' + part : part;
            const pathToExpand = acum;
            chain = chain.then(function () {
                const node = findFolderNode(pathToExpand);
                if (node) {
                    return expandNode(node, true);
                }
                return Promise.resolve();
            });
        });

        return chain.then(function () {
            updateActiveStates();
            if (scroll !== false) scrollToActive();
        });
    }

    function updateActiveStates() {
        document.querySelectorAll('.tree-row').forEach(function (row) {
            row.classList.remove('active');
        });
        const actual = normalizePath(getCarpetaActual());
        if (!actual) return;

        document.querySelectorAll('.tree-node[data-tipo="carpeta"]').forEach(function (node) {
            if (normalizePath(node.dataset.ruta) === actual) {
                const row = node.querySelector(':scope > .tree-row');
                if (row) row.classList.add('active');
            }
        });
    }

    function buildPathTabs() {
        const tabsEl = document.getElementById('sidebar-path-tabs');
        if (!tabsEl) return;

        const refPath = sidebarBrowsePath || normalizePath(getCarpetaActual());
        let html = '<button type="button" class="sidebar-path-tab' + (!refPath ? ' active' : '') + '" data-ruta="">' +
            '<i class="fas fa-home"></i> Raíz</button>';

        if (refPath) {
            const parts = refPath.split('/');
            let acum = '';
            parts.forEach(function (part, i) {
                acum = acum ? acum + '/' + part : part;
                const isLast = i === parts.length - 1;
                html += '<button type="button" class="sidebar-path-tab' + (isLast ? ' active' : '') + '" data-ruta="' + escapeHtml(acum) + '">' +
                    '<i class="fas fa-folder"></i> ' + escapeHtml(part) + '</button>';
            });
        }

        tabsEl.innerHTML = html;
    }

    function expandPathToCurrent() {
        sidebarBrowsePath = normalizePath(getCarpetaActual());
        buildPathTabs();
        updateActiveStates();
        return expandToPath(sidebarBrowsePath, true);
    }

    function scrollToActive() {
        const refPath = sidebarBrowsePath || normalizePath(getCarpetaActual());
        let target = document.querySelector('.tree-row.active');
        if (!target && refPath) {
            const node = findFolderNode(refPath);
            target = node ? node.querySelector(':scope > .tree-row') : null;
        }
        if (target) {
            target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    function applyFilter(term) {
        const q = (term || '').trim().toLowerCase();
        const root = getSidebarRoot();
        if (!root) return;

        root.querySelectorAll('.tree-node').forEach(function (node) {
            if (!q) {
                node.classList.remove('filtered-out');
                return;
            }
            const label = node.querySelector('.tree-label');
            const text = (label ? label.textContent : '').toLowerCase();
            node.classList.toggle('filtered-out', text.indexOf(q) === -1);
        });
    }

    function navigateToFolder(ruta) {
        if (typeof window.navegarExplorador === 'function') {
            window.navegarExplorador(ruta);
            return;
        }
        window.location.href = dashboardUrl({ carpeta: ruta });
    }

    function abrirPreviewArchivo(row) {
        const meta = {
            ruta: row.dataset.archivo || row.dataset.ruta || '',
            nombre: row.dataset.nombre || row.querySelector('.tree-label')?.textContent || '',
            tipoPreview: row.dataset.tipoPreview || 'none',
            puedePrevisualizar: row.dataset.puedePreview === '1',
            size: row.dataset.size || '',
            fecha: row.dataset.fecha || '',
            esTexto: row.dataset.esTexto === '1',
            puedeEditar: row.dataset.puedeEditar === '1',
        };

        document.querySelectorAll('.tree-row.is-file.previewing').forEach(function (el) {
            el.classList.remove('previewing');
        });
        row.classList.add('previewing');

        if (window.FilePreview && typeof window.FilePreview.abrir === 'function') {
            window.FilePreview.abrir(meta, null);
            return;
        }

        window.location.href = dashboardUrl({
            carpeta: row.closest('.tree-node')?.dataset.carpetaPadre || '',
            preview: meta.ruta
        });
    }

    function onSidebarClick(e) {
        const toggle = e.target.closest('.tree-toggle');
        if (toggle && !toggle.classList.contains('placeholder')) {
            e.preventDefault();
            e.stopPropagation();
            const node = toggle.closest('.tree-node');
            if (!node) return;
            if (node.classList.contains('expanded')) {
                collapseNode(node);
            } else {
                expandNode(node, true);
            }
            return;
        }

        const moreBtn = e.target.closest('[data-abrir-carpeta]');
        if (moreBtn) {
            e.preventDefault();
            expandToPath(moreBtn.dataset.abrirCarpeta, true);
            return;
        }

        const pathTab = e.target.closest('.sidebar-path-tab');
        if (pathTab) {
            e.preventDefault();
            document.querySelectorAll('.sidebar-path-tab').forEach(function (t) { t.classList.remove('active'); });
            pathTab.classList.add('active');
            expandToPath(pathTab.dataset.ruta || '', true);
            return;
        }

        const quickTab = e.target.closest('.sidebar-quick-tab');
        if (quickTab) {
            e.preventDefault();
            document.querySelectorAll('.sidebar-quick-tab').forEach(function (t) { t.classList.remove('active'); });
            quickTab.classList.add('active');
            expandToPath(quickTab.dataset.ruta || '', true);
            return;
        }

        const fileRow = e.target.closest('.tree-row.is-file');
        if (fileRow) {
            e.preventDefault();
            e.stopPropagation();
            abrirPreviewArchivo(fileRow);
            return;
        }

        const folderLabel = e.target.closest('.tree-node[data-tipo="carpeta"] .tree-label');
        if (folderLabel) {
            e.preventDefault();
            const node = folderLabel.closest('.tree-node');
            if (!node) return;

            if (e.detail >= 2) {
                navigateToFolder(node.dataset.ruta);
                return;
            }

            if (node.classList.contains('expanded')) {
                collapseNode(node);
            } else {
                expandNode(node, true);
            }
        }
    }

    function onSidebarKeydown(e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const label = e.target.closest('.tree-label');
        if (!label) return;
        e.preventDefault();
        label.click();
    }

    function init() {
        const root = getSidebarRoot();
        if (!root) return;

        sidebarBrowsePath = normalizePath(getCarpetaActual());

        root.addEventListener('click', onSidebarClick);
        root.addEventListener('keydown', onSidebarKeydown);

        const filterInput = document.getElementById('sidebar-filter');
        if (filterInput) {
            filterInput.addEventListener('input', function () {
                applyFilter(filterInput.value);
            });
        }

        buildPathTabs();
        updateActiveStates();

        setTimeout(function () {
            if (sidebarBrowsePath) {
                expandToPath(sidebarBrowsePath, true);
            }
        }, 120);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.SidebarTree = {
        refresh: expandPathToCurrent,
        expandTo: expandToPath,
        getCarpetaActual: getCarpetaActual,
        previewFile: abrirPreviewArchivo
    };
})();
