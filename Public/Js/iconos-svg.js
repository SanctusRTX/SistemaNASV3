/**
 * Script para reemplazar los iconos de Font Awesome por iconos SVG
 * Esto garantiza que los iconos se muestren correctamente incluso sin conexión a internet
 */
document.addEventListener('DOMContentLoaded', function() {
    // Primero, eliminar cualquier icono SVG duplicado que pudiera haberse creado anteriormente
    document.querySelectorAll('.icon').forEach(function(elemento) {
        // Si el elemento siguiente es un icono de Font Awesome oculto, eliminar este icono SVG
        const siguienteElemento = elemento.nextElementSibling;
        if (siguienteElemento && 
            (siguienteElemento.classList.contains('fas') || 
             siguienteElemento.classList.contains('far') || 
             siguienteElemento.classList.contains('fab') || 
             siguienteElemento.classList.contains('fa')) && 
            siguienteElemento.style.display === 'none') {
            elemento.remove();
        }
    });
    
    // Mapeo de clases de Font Awesome a clases de iconos SVG
    const iconosMap = {
        'fa-folder': 'icon-folder',
        'fa-folder-open': 'icon-folder-open',
        'fa-file': 'icon-file',
        'fa-file-alt': 'icon-file-alt',
        'fa-edit': 'icon-edit',
        'fa-trash': 'icon-trash',
        'fa-trash-alt': 'icon-trash-alt',
        'fa-download': 'icon-download',
        'fa-upload': 'icon-upload',
        'fa-sync-alt': 'icon-sync-alt',
        'fa-folder-plus': 'icon-folder-plus',
        'fa-copy': 'icon-copy',
        'fa-sign-out-alt': 'icon-sign-out-alt',
        'fa-user-tag': 'icon-user-tag',
        'fa-home': 'icon-home',
        'fa-chevron-right': 'icon-chevron-right',
        'fa-search': 'icon-search'
    };

    // Buscar todos los elementos con clases de Font Awesome que no estén ya ocultos
    document.querySelectorAll('.fas:not([style*="display: none"]), .far:not([style*="display: none"]), .fab:not([style*="display: none"]), .fa:not([style*="display: none"])').forEach(function(elemento) {
        // Verificar si ya tiene un icono SVG como hermano anterior
        const prevSibling = elemento.previousElementSibling;
        if (prevSibling && prevSibling.classList.contains('icon')) {
            // Ya tiene un icono SVG, ocultar este
            elemento.style.display = 'none';
            return;
        }
        
        // Obtener todas las clases del elemento
        const clases = elemento.className.split(' ');
        
        // Variable para almacenar la clase de icono SVG a usar
        let iconoSvgClass = null;
        
        // Buscar clases de iconos de Font Awesome
        for (let i = 0; i < clases.length; i++) {
            if (iconosMap[clases[i]]) {
                iconoSvgClass = iconosMap[clases[i]];
                break;
            }
        }
        
        // Si encontramos una clase de icono SVG, crear y reemplazar
        if (iconoSvgClass) {
            // Crear un nuevo elemento span para el icono SVG
            const nuevoIcono = document.createElement('span');
            nuevoIcono.className = 'icon ' + iconoSvgClass;
            
            // Conservar el título si existe
            if (elemento.title) {
                nuevoIcono.title = elemento.title;
            }
            
            // Reemplazar el elemento original por el nuevo icono
            elemento.parentNode.insertBefore(nuevoIcono, elemento);
            elemento.style.display = 'none';
        }
    });
});

