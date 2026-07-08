/**
 * Script para inicializar y gestionar tooltips animados en el Sistema NAS
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips en todos los elementos con atributo data-tooltip
    initTooltips();
    
    // Observador de mutaciones para inicializar tooltips en elementos añadidos dinámicamente
    const observer = new MutationObserver(function(mutations) {
        let shouldInit = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Elemento
                        if (node.hasAttribute && node.hasAttribute('data-tooltip') || 
                            node.querySelector && node.querySelector('[data-tooltip]')) {
                            shouldInit = true;
                        }
                    }
                });
            }
        });
        
        if (shouldInit) {
            initTooltips();
        }
    });
    
    // Configurar el observador
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

/**
 * Inicializa tooltips animados en todos los elementos con atributo data-tooltip
 */
function initTooltips() {
    // Buscar todos los elementos con atributo data-tooltip o data-bs-toggle="tooltip"
    const elements = document.querySelectorAll('[data-tooltip], [data-bs-toggle="tooltip"], [data-toggle="tooltip"]');
    
    elements.forEach(function(element) {
        // Verificar si ya tiene un tooltip inicializado
        if (element.hasAttribute('data-tooltip-initialized')) {
            return;
        }
        
        // Marcar como inicializado
        element.setAttribute('data-tooltip-initialized', 'true');
        
        // Obtener el título del tooltip
        let title = element.getAttribute('data-tooltip') || 
                   element.getAttribute('data-bs-title') || 
                   element.getAttribute('data-bs-original-title') || 
                   element.getAttribute('title') || '';
        
        if (!title) {
            return;
        }
        
        // Eliminar el atributo title para evitar el tooltip nativo
        if (element.hasAttribute('title')) {
            element.setAttribute('data-original-title', element.getAttribute('title'));
            element.removeAttribute('title');
        }
        
        // Obtener la posición del tooltip
        let placement = element.getAttribute('data-tooltip-placement') || 
                       element.getAttribute('data-bs-placement') || 
                       'top';
        
        // Obtener el tipo de tooltip
        let type = element.getAttribute('data-tooltip-type') || 
                  element.getAttribute('data-bs-tooltip-type') || 
                  '';
        
        // Crear el tooltip
        let tooltip = document.createElement('div');
        tooltip.className = 'tooltip tooltip-animated bs-tooltip-' + placement;
        if (type) {
            tooltip.classList.add('tooltip-' + type);
        }
        tooltip.setAttribute('role', 'tooltip');
        
        // Crear la flecha
        let arrow = document.createElement('div');
        arrow.className = 'tooltip-arrow';
        
        // Crear el contenido
        let inner = document.createElement('div');
        inner.className = 'tooltip-inner';
        inner.innerHTML = title;
        
        // Ensamblar el tooltip
        tooltip.appendChild(arrow);
        tooltip.appendChild(inner);
        
        // Añadir el tooltip al body
        document.body.appendChild(tooltip);
        
        // Asociar el tooltip al elemento
        element.tooltip = tooltip;
        
        // Eventos para mostrar/ocultar el tooltip
        element.addEventListener('mouseenter', function() {
            showTooltip(element);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip(element);
        });
        
        element.addEventListener('focus', function() {
            showTooltip(element);
        });
        
        element.addEventListener('blur', function() {
            hideTooltip(element);
        });
    });
}

/**
 * Muestra el tooltip asociado a un elemento
 */
function showTooltip(element) {
    if (!element.tooltip) return;
    
    const tooltip = element.tooltip;
    
    // Obtener la posición del elemento
    const rect = element.getBoundingClientRect();
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Obtener la posición del tooltip
    const placement = tooltip.classList.contains('bs-tooltip-bottom') ? 'bottom' :
                     tooltip.classList.contains('bs-tooltip-left') ? 'left' :
                     tooltip.classList.contains('bs-tooltip-right') ? 'right' : 'top';
    
    // Posicionar el tooltip
    let top, left;
    
    switch (placement) {
        case 'bottom':
            top = rect.bottom + scrollTop + 10;
            left = rect.left + scrollLeft + rect.width / 2 - tooltip.offsetWidth / 2;
            break;
        case 'top':
            top = rect.top + scrollTop - tooltip.offsetHeight - 10;
            left = rect.left + scrollLeft + rect.width / 2 - tooltip.offsetWidth / 2;
            break;
        case 'left':
            top = rect.top + scrollTop + rect.height / 2 - tooltip.offsetHeight / 2;
            left = rect.left + scrollLeft - tooltip.offsetWidth - 10;
            break;
        case 'right':
            top = rect.top + scrollTop + rect.height / 2 - tooltip.offsetHeight / 2;
            left = rect.right + scrollLeft + 10;
            break;
    }
    
    // Ajustar la posición para que no se salga de la ventana
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    if (left < 10) left = 10;
    if (left + tooltip.offsetWidth > viewportWidth - 10) {
        left = viewportWidth - tooltip.offsetWidth - 10;
    }
    
    if (top < 10) top = 10;
    if (top + tooltip.offsetHeight > viewportHeight + scrollTop - 10) {
        top = viewportHeight + scrollTop - tooltip.offsetHeight - 10;
    }
    
    // Aplicar la posición
    tooltip.style.top = top + 'px';
    tooltip.style.left = left + 'px';
    
    // Mostrar el tooltip
    tooltip.classList.add('show');
    
    // Posicionar la flecha
    const arrow = tooltip.querySelector('.tooltip-arrow');
    if (arrow) {
        switch (placement) {
            case 'bottom':
                arrow.style.left = (rect.left + rect.width / 2 - left) + 'px';
                break;
            case 'top':
                arrow.style.left = (rect.left + rect.width / 2 - left) + 'px';
                break;
            case 'left':
                arrow.style.top = (rect.top + rect.height / 2 - top) + 'px';
                break;
            case 'right':
                arrow.style.top = (rect.top + rect.height / 2 - top) + 'px';
                break;
        }
    }
}

/**
 * Oculta el tooltip asociado a un elemento
 */
function hideTooltip(element) {
    if (!element.tooltip) return;
    
    // Ocultar el tooltip
    element.tooltip.classList.remove('show');
}

/**
 * Actualiza el contenido de un tooltip
 */
function updateTooltip(element, content) {
    if (!element.tooltip) return;
    
    const inner = element.tooltip.querySelector('.tooltip-inner');
    if (inner) {
        inner.innerHTML = content;
    }
}

/**
 * Destruye un tooltip
 */
function destroyTooltip(element) {
    if (!element.tooltip) return;
    
    // Eliminar el tooltip del DOM
    document.body.removeChild(element.tooltip);
    
    // Eliminar la referencia
    element.tooltip = null;
    
    // Eliminar la marca de inicializado
    element.removeAttribute('data-tooltip-initialized');
}

// Exponer funciones globalmente
window.NASTooltips = {
    init: initTooltips,
    show: showTooltip,
    hide: hideTooltip,
    update: updateTooltip,
    destroy: destroyTooltip
};
