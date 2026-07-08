/**
 * session-manager.js
 * Gestor de sesión por inactividad del usuario.
 *
 * Comportamiento:
 *  - Timeout total : 3 minutos de inactividad
 *  - Aviso         : 1 minuto antes de expirar (a los 2 min)
 *  - Al cerrar pestaña/navegador: logout silencioso via sendBeacon
 *
 * Actividad detectada: mousemove, mousedown, keydown, touchstart, scroll, click
 */

(function () {
    'use strict';

    // ── Configuración ──────────────────────────────────────────────────────────
    const TOTAL_SECONDS   = 3 * 60;   // 3 minutos = 180 s
    const WARNING_SECONDS = 1 * 60;   // Avisar cuando quede 1 minuto = 60 s
    const PING_INTERVAL   = 45 * 1000; // Ping al servidor cada 45 s si hay actividad

    // Rutas (inyectadas desde Blade vía window.SessionManager)
    const PING_URL  = window.SessionManagerConfig?.pingUrl  || '/session/ping';
    const CLOSE_URL = window.SessionManagerConfig?.closeUrl || '/session/close';
    const LOGIN_URL = window.SessionManagerConfig?.loginUrl || '/';
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ── Estado interno ─────────────────────────────────────────────────────────
    let secondsLeft     = TOTAL_SECONDS;
    let warningVisible  = false;
    let countdownTimer  = null;   // setInterval para el contador
    let pingTimer       = null;   // setInterval para el ping al servidor
    let hadRecentActivity = false; // bandera entre pings

    // ── Elementos del DOM ──────────────────────────────────────────────────────
    const overlay  = document.getElementById('session-warning-overlay');
    const countEl  = document.getElementById('session-countdown-display');
    const btnKeep  = document.getElementById('session-btn-continuar');
    const btnExit  = document.getElementById('session-btn-salir');

    if (!overlay) {
        console.warn('[SessionManager] Modal de advertencia no encontrado en el DOM.');
        return;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    function formatTime(s) {
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
    }

    function showWarning() {
        if (warningVisible) return;
        warningVisible = true;
        overlay.classList.add('visible');
        updateCountdownDisplay();
    }

    function hideWarning() {
        warningVisible = false;
        overlay.classList.remove('visible');
    }

    function updateCountdownDisplay() {
        if (!countEl) return;
        countEl.textContent = formatTime(secondsLeft);

        // Color del contador cambia según urgencia
        countEl.classList.remove('urgent', 'critical');
        if (secondsLeft <= 10) {
            countEl.classList.add('critical');
        } else if (secondsLeft <= 30) {
            countEl.classList.add('urgent');
        }
    }

    // ── Logout ─────────────────────────────────────────────────────────────────
    function doLogout(motivo) {
        clearInterval(countdownTimer);
        clearInterval(pingTimer);
        hideWarning();

        // Mostrar overlay de "cerrando sesión..." para evitar clics extra
        overlay.innerHTML = `
            <div class="session-warning-card" style="text-align:center;">
                <div class="session-icon" style="background:rgba(239,68,68,0.15); border-color:rgba(239,68,68,0.4);">
                    <i class="fas fa-sign-out-alt" style="color:#ef4444;"></i>
                </div>
                <h4>Sesión cerrada</h4>
                <p style="color:#94a3b8;">${motivo}</p>
                <div class="session-spinner"><i class="fas fa-spinner fa-spin"></i> Redirigiendo...</div>
            </div>`;
        overlay.classList.add('visible');

        fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
        }).finally(() => {
            setTimeout(() => { window.location.href = LOGIN_URL; }, 800);
        });
    }

    // ── Reiniciar contador ─────────────────────────────────────────────────────
    function resetTimer() {
        secondsLeft = TOTAL_SECONDS;
        hadRecentActivity = true;

        if (warningVisible) {
            hideWarning();
        }
    }

    function pingServer() {
        if (!hadRecentActivity) return; // No molestar al servidor si no hubo actividad
        hadRecentActivity = false;

        fetch(PING_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
        })
        .then(res => {
            if (res.status === 401) {
                // El servidor ya expiró la sesión
                doLogout('Tu sesión expiró en el servidor.');
            }
        })
        .catch(() => {
            // Error de red — no cerrar sesión, podría ser temporal
        });
    }

    // ── Countdown principal ────────────────────────────────────────────────────
    function startCountdown() {
        clearInterval(countdownTimer);

        countdownTimer = setInterval(() => {
            secondsLeft--;

            // Mostrar aviso cuando quede WARNING_SECONDS o menos
            if (secondsLeft <= WARNING_SECONDS && !warningVisible) {
                showWarning();
            }

            // Actualizar display si el modal está visible
            if (warningVisible) {
                updateCountdownDisplay();
            }

            // Tiempo agotado → cerrar sesión
            if (secondsLeft <= 0) {
                doLogout('Tu sesión expiró por inactividad.');
            }
        }, 1000);
    }

    // ── Ping periódico al servidor ─────────────────────────────────────────────
    function startPingLoop() {
        clearInterval(pingTimer);
        pingTimer = setInterval(pingServer, PING_INTERVAL);
    }

    // ── Escuchar actividad del usuario ─────────────────────────────────────────
    const ACTIVITY_EVENTS = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];

    ACTIVITY_EVENTS.forEach(ev => {
        document.addEventListener(ev, resetTimer, { passive: true });
    });

    // ── Botones del modal ──────────────────────────────────────────────────────
    if (btnKeep) {
        btnKeep.addEventListener('click', function () {
            resetTimer();
            pingServer(); // Forzar ping inmediato al hacer clic en "Continuar"
        });
    }

    if (btnExit) {
        btnExit.addEventListener('click', function () {
            doLogout('Cerraste sesión manualmente.');
        });
    }

    // ── Cierre de pestaña ──────────────────────────────────────────────────────
    // NOTA: El cierre de sesión por cierre de pestaña fue eliminado porque los
    // navegadores no distinguen de forma fiable entre "cerrar" y "recargar",
    // lo que causaba cierres de sesión inesperados al presionar F5.
    // La sesión se cierra únicamente por:
    //   1. Inactividad de 3 minutos (timeout arriba)
    //   2. Botón "Cerrar Sesión" manual
    //   3. Expiración server-side detectada por el ping

    // ── Arrancar ───────────────────────────────────────────────────────────────
    startCountdown();
    startPingLoop();

    console.info('[SessionManager] Iniciado. Timeout: 3 min. Aviso: 1 min antes.');

})();
