/**
 * Script para aplicar estilos a los botones de funciones del Sistema NAS
 * Agrega la clase btn-funcion a todos los botones de acciones
 * Y la clase btn-papelera a los botones de eliminación
 */
document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los botones de acciones
    const botonesAcciones = document.querySelectorAll('.btn-sm');
    
    // Aplicar la clase btn-funcion a todos los botones
    botonesAcciones.forEach(function(boton) {
        boton.classList.add('btn-funcion');
    });
    
    // También aplicar a botones específicos por su función
    const selectoresBotones = [
        // Botones de acciones principales
        '.btn-primary', '.btn-secondary', '.btn-success', 
        '.btn-danger', '.btn-info', '.btn-warning',
        // Botones con contorno
        '.btn-outline-primary', '.btn-outline-secondary', '.btn-outline-success',
        '.btn-outline-danger', '.btn-outline-info', '.btn-outline-warning',
        // Botones por función específica
        '.btn-editar', '.btn-eliminar', '.btn-crear', '.btn-subir',
        '.btn-descargar', '.btn-renombrar', '.btn-editar-carpeta',
        '.btn-editar-archivo', '.renombrar-carpeta-btn', '.renombrar-archivo-btn'
    ];
    
    // Selector combinado para todos los tipos de botones
    const selectorCombinado = selectoresBotones.join(', ');
    
    // Aplicar la clase a todos los botones que coincidan con los selectores
    document.querySelectorAll(selectorCombinado).forEach(function(boton) {
        // No aplicar a botones que ya tienen la clase o a botones dentro de modales
        if (!boton.classList.contains('btn-funcion') && 
            !boton.closest('.modal')) {
            boton.classList.add('btn-funcion');
        }
    });
    
    // Aplicar clase btn-papelera a botones de eliminación
    const selectoresPapelera = [
        // Botones de eliminación por clase
        '.btn-danger', '.btn-eliminar',
        // Botones de eliminación por atributos
        'button[name="eliminar"]', 'button[name="papelera"]',
        // Botones con iconos de papelera
        'button:has(.fa-trash)', 'button:has(.fa-trash-alt)',
        'a:has(.fa-trash)', 'a:has(.fa-trash-alt)',
        'button:has(.icon-trash)', 'button:has(.icon-trash-alt)',
        'a:has(.icon-trash)', 'a:has(.icon-trash-alt)'
    ];
    
    // Intentar usar el selector :has() si está disponible en el navegador
    try {
        document.querySelector('button:has(.fa-trash)');
        // Si llegamos aquí, :has() está soportado
        const selectorPapeleraCombinado = selectoresPapelera.join(', ');
        document.querySelectorAll(selectorPapeleraCombinado).forEach(function(boton) {
            boton.classList.add('btn-papelera');
        });
    } catch (e) {
        // :has() no está soportado, usar enfoque alternativo
        document.querySelectorAll('.btn-danger, .btn-eliminar, button[name="eliminar"], button[name="papelera"]').forEach(function(boton) {
            boton.classList.add('btn-papelera');
        });
        
        // Buscar botones con iconos de papelera
        document.querySelectorAll('button, a').forEach(function(elemento) {
            if (elemento.querySelector('.fa-trash, .fa-trash-alt, .icon-trash, .icon-trash-alt')) {
                elemento.classList.add('btn-papelera');
            }
        });
    }
    
    // Mejorar el contenedor del modo oscuro
    const interruptorModoOscuro = document.querySelector('.custom-switch');
    if (interruptorModoOscuro) {
        // Crear contenedor si no existe
        if (!interruptorModoOscuro.closest('.modo-oscuro-container')) {
            const contenedor = document.createElement('div');
            contenedor.className = 'modo-oscuro-container';
            
            // Agregar etiqueta
            const etiqueta = document.createElement('span');
            etiqueta.textContent = 'Modo Oscuro';
            
            // Obtener el padre del interruptor
            const padreInterruptor = interruptorModoOscuro.parentNode;
            
            // Insertar el contenedor en su lugar
            padreInterruptor.insertBefore(contenedor, interruptorModoOscuro);
            
            // Mover el interruptor dentro del contenedor
            contenedor.appendChild(etiqueta);
            contenedor.appendChild(interruptorModoOscuro);
        }
    }
});
