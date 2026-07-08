// Script para inicializar tooltips y modificar botones
$(document).ready(function(){
    // Inicializar tooltips de Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Función para remover texto de botones y dejar solo iconos
    function limpiarBotones() {
        // Buscar todos los botones con iconos
        $('.btn').each(function() {
            // Si el botón tiene un icono
            if ($(this).find('i.fas, i.far, i.fab').length > 0) {
                // Obtener el texto del botón
                let textoBoton = $(this).text().trim();
                
                // Si hay texto después del icono, guardarlo como título para el tooltip
                if (textoBoton && !$(this).attr('data-original-title')) {
                    $(this).attr('data-toggle', 'tooltip');
                    $(this).attr('title', textoBoton);
                }
                
                // Limpiar el contenido del botón y mantener solo el icono
                let icono = $(this).find('i.fas, i.far, i.fab').clone();
                $(this).empty().append(icono);
            }
        });
        
        // Reinicializar tooltips después de modificar los botones
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Ejecutar la función cuando la página esté lista
    limpiarBotones();
});
