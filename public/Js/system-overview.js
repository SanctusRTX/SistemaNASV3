/**
 * Widget System Overview — métricas reales del servidor vía API
 */
(function () {
    'use strict';

    const REFRESH_MS = 8000;

    function el(id) {
        return document.getElementById(id);
    }

    function setBadge(status) {
        const badge = el('sys-overview-badge');
        if (!badge) return;

        badge.classList.remove('badge-success', 'badge-warning', 'badge-secondary', 'badge-danger');

        if (status === 'live') {
            badge.classList.add('badge-success');
            badge.textContent = 'LIVE';
        } else if (status === 'partial') {
            badge.classList.add('badge-warning');
            badge.textContent = 'PARCIAL';
        } else {
            badge.classList.add('badge-secondary');
            badge.textContent = 'N/D';
        }
    }

    function setMetric(valueId, detailId, metric, suffix) {
        const valueEl = el(valueId);
        const detailEl = el(detailId);
        if (!valueEl || !detailEl) return;

        if (metric && metric.available && metric.percent !== null && metric.percent !== undefined) {
            valueEl.textContent = metric.percent + suffix;
            detailEl.textContent = metric.detail || '';
        } else if (metric && metric.available && metric.human) {
            valueEl.textContent = metric.human;
            detailEl.textContent = metric.detail || 'Tiempo activo';
        } else {
            valueEl.textContent = 'N/D';
            detailEl.textContent = (metric && metric.detail) ? metric.detail : 'No disponible';
        }
    }

    function setStorage(storage) {
        const labelEl = el('sys-storage-label');
        const barEl = el('sys-storage-bar');
        const detailEl = el('sys-storage-detail');

        if (!labelEl || !barEl) return;

        if (storage && storage.available && storage.percent !== null) {
            labelEl.textContent = storage.label || storage.percent + '% usado';
            barEl.style.width = Math.min(100, Math.max(0, storage.percent)) + '%';
            if (detailEl) {
                detailEl.textContent = storage.detail || '';
            }
        } else {
            labelEl.textContent = 'N/D';
            barEl.style.width = '0%';
            if (detailEl) {
                detailEl.textContent = 'No disponible';
            }
        }
    }

    function setHostInfo(data) {
        const hostEl = el('sys-overview-host');
        if (hostEl) {
            hostEl.textContent = (data.host || 'Servidor') + ' · ' + (data.os || '');
        }
    }

    function syncServerClock(data) {
        if (!data.server_timestamp) return;
        const clockEl = el('system-clock');
        const dateEl = el('system-date');
        if (!clockEl || !dateEl) return;

        const serverDate = new Date(data.server_timestamp * 1000);
        const offset = serverDate.getTime() - Date.now();

        function tick() {
            const now = new Date(Date.now() + offset);
            const hrs = String(now.getHours()).padStart(2, '0');
            const mins = String(now.getMinutes()).padStart(2, '0');
            const secs = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = hrs + ':' + mins + ':' + secs;

            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            dateEl.textContent = diasSemana[now.getDay()] + ', ' + now.getDate() + ' de ' + meses[now.getMonth()] + ' del ' + now.getFullYear();
        }

        tick();
        if (window._sysClockInterval) clearInterval(window._sysClockInterval);
        window._sysClockInterval = setInterval(tick, 1000);
    }

    function renderOverview(data) {
        if (!data || !data.success) return;

        setBadge(data.status || 'partial');
        setHostInfo(data);
        setMetric('sys-cpu-value', 'sys-cpu-detail', data.cpu, '%');
        setMetric('sys-mem-value', 'sys-mem-detail', data.memory, '%');
        setMetric('sys-uptime-value', 'sys-uptime-detail', data.uptime, '');
        setStorage(data.storage);
        syncServerClock(data);
    }

    function fetchOverview() {
        return fetch('/sistema/overview', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(renderOverview)
        .catch(function () {
            setBadge('unavailable');
            const hostEl = el('sys-overview-host');
            if (hostEl) hostEl.textContent = 'Sin conexión con el servidor';
        });
    }

    function init() {
        if (!el('sys-overview-panel')) return;

        fetchOverview();
        setInterval(fetchOverview, REFRESH_MS);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
