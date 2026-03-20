/**
 * Controlador unificado para la barra lateral
 * Sistema NAS v3
 * Funciona tanto en dispositivos móviles como en escritorio
 */

// Función global para alternar la barra lateral (accesible desde cualquier parte)
function toggleSidebarGlobal() {
    console.log('Función toggleSidebarGlobal ejecutada');
    
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarLabel = document.getElementById('sidebar-label');
    const isMobile = window.innerWidth <= 768;
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar || !mainContent) {
        console.error('No se encontraron los elementos necesarios para la barra lateral');
        return;
    }
    
    if (isMobile) {
        // En móviles
        sidebar.classList.toggle('show-mobile');
        if (sidebarOverlay) {
            sidebarOverlay.classList.toggle('show');
        }
        
        // Controlar el scroll del body
        if (sidebar.classList.contains('show-mobile')) {
            document.body.style.overflow = 'hidden';
            localStorage.setItem('sidebarVisible', 'true');
        } else {
            document.body.style.overflow = '';
            localStorage.setItem('sidebarVisible', 'false');
        }
    } else {
        // En escritorio
        console.log('Ejecutando toggle en modo escritorio');
        
        // Verificar el estado actual de la barra lateral
        const isHidden = sidebar.classList.contains('d-none');
        console.log('Estado actual: ' + (isHidden ? 'oculto' : 'visible'));
        
        // Forzar la eliminación de cualquier estilo inline que pueda interferir
        sidebar.style.display = '';
        
        // Alternar la clase d-none
        if (isHidden) {
            // Si está oculta, mostrarla
            sidebar.classList.remove('d-none');
            mainContent.classList.remove('col-md-12');
            mainContent.classList.add('col-md-9');
            // Mostrar la etiqueta "Navegador"
            if (sidebarLabel) sidebarLabel.style.display = '';
            localStorage.setItem('sidebarVisible', 'true');
        } else {
            // Si está visible, ocultarla
            sidebar.classList.add('d-none');
            mainContent.classList.remove('col-md-9');
            mainContent.classList.add('col-md-12');
            // Ocultar la etiqueta "Navegador"
            if (sidebarLabel) sidebarLabel.style.display = 'none';
            localStorage.setItem('sidebarVisible', 'false');
        }
        
        // Forzar un reflow para asegurar que los cambios se apliquen
        void sidebar.offsetWidth;
        
        console.log('Nuevo estado: ' + (sidebar.classList.contains('d-none') ? 'oculto' : 'visible'));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando controlador de barra lateral');
    
    // Detectar si es un dispositivo móvil
    const isMobile = window.innerWidth <= 768;
    
    // Elementos del DOM
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarLabel = document.getElementById('sidebar-label');
    
    if (!sidebar || !mainContent) {
        console.error('No se encontraron los elementos necesarios para la barra lateral');
        return;
    }
    
    // Crear overlay para el sidebar en móviles si no existe
    let sidebarOverlay = document.querySelector('.sidebar-overlay');
    if (!sidebarOverlay) {
        sidebarOverlay = document.createElement('div');
        sidebarOverlay.className = 'sidebar-overlay';
        document.body.appendChild(sidebarOverlay);
    }
    
    console.log('Configurando estado inicial de la barra lateral');
    
    // IMPORTANTE: Forzar que la barra lateral esté siempre visible en escritorio por defecto
    if (!isMobile) {
        // En escritorio, siempre mostrar la barra lateral por defecto
        console.log('Modo escritorio: Mostrando barra lateral por defecto');
        
        // Eliminar cualquier clase que pueda ocultar la barra lateral
        sidebar.classList.remove('d-none');
        
        // Asegurar que el contenido principal tenga el ancho correcto
        mainContent.classList.remove('col-md-12');
        mainContent.classList.add('col-md-9');
        
        // Asegurar que la etiqueta "Navegador" esté visible
        if (sidebarLabel) sidebarLabel.style.display = '';
        
        // Guardar esta preferencia
        localStorage.setItem('sidebarVisible', 'true');
    } else {
        // En móvil, mantener la barra lateral oculta inicialmente
        console.log('Modo móvil: Ocultando barra lateral inicialmente');
        sidebar.classList.remove('show-mobile');
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('show');
        }
    }
    
    // Asignar evento a todos los botones de toggle que existan
    const toggleButtons = document.querySelectorAll('#toggleSidebar');
    toggleButtons.forEach(function(button) {
        console.log('Botón de toggle encontrado, asignando evento');
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebarGlobal();
        });
    });
    
    // Cerrar sidebar al hacer clic en el overlay
    sidebarOverlay.addEventListener('click', function() {
        if (sidebar.classList.contains('show-mobile')) {
            toggleSidebarGlobal();
        }
    });
    
    // Detectar cambios en el tamaño de la ventana
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const wasIsMobile = isMobile;
            const isNowMobile = window.innerWidth <= 768;
            
            // Si cambia entre móvil y escritorio, recargar la página
            if (wasIsMobile !== isNowMobile) {
                location.reload();
            }
        }, 250);
    });
    
    // Exponer la función globalmente para que pueda ser llamada desde cualquier parte
    window.toggleSidebar = toggleSidebarGlobal;
});
