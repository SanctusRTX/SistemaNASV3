/**
 * Script para manejar las vistas de explorador (cuadrícula y lista)
 * Sistema NAS v3
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const vistaCuadriculaBtn = document.getElementById('vista-cuadricula-btn');
    const vistaListaBtn = document.getElementById('vista-lista-btn');
    const contenedorArchivos = document.getElementById('vista-explorador');
    
    // Comprobar si hay una preferencia guardada
    const vistaPreferida = localStorage.getItem('vistaExplorador') || 'cuadricula';
    
    // Función para cambiar la vista
    function cambiarVista(tipo) {
        // Guardar preferencia
        localStorage.setItem('vistaExplorador', tipo);
        
        // Actualizar botones activos
        if (tipo === 'cuadricula') {
            vistaCuadriculaBtn.classList.add('active');
            vistaListaBtn.classList.remove('active');
            contenedorArchivos.classList.remove('vista-lista');
            contenedorArchivos.classList.add('vista-cuadricula');
        } else {
            vistaListaBtn.classList.add('active');
            vistaCuadriculaBtn.classList.remove('active');
            contenedorArchivos.classList.remove('vista-cuadricula');
            contenedorArchivos.classList.add('vista-lista');
        }
    }
    
    // Establecer la vista inicial según la preferencia
    cambiarVista(vistaPreferida);
    
    // Asignar eventos a los botones
    if (vistaCuadriculaBtn) {
        vistaCuadriculaBtn.addEventListener('click', function() {
            cambiarVista('cuadricula');
        });
    }
    
    if (vistaListaBtn) {
        vistaListaBtn.addEventListener('click', function() {
            cambiarVista('lista');
        });
    }
    
    // Manejar clics en los elementos (carpetas y archivos)
    document.querySelectorAll('.item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            // Solo manejar el clic si no fue en un botón de acción
            if (!e.target.closest('.item-actions') && !e.target.closest('.btn')) {
                const enlace = item.querySelector('a');
                if (enlace) {
                    window.location.href = enlace.href;
                }
            }
        });
    });
    
    // Manejar menú contextual (clic derecho)
    document.querySelectorAll('.item').forEach(function(item) {
        item.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            
            // Obtener información del elemento
            const tipo = item.classList.contains('carpeta') ? 'carpeta' : 'archivo';
            const ruta = item.dataset.ruta;
            const nombre = item.dataset.nombre;
            
            console.log('Clic derecho en:', { tipo, ruta, nombre });
            
            // Mostrar menú contextual
            const menu = document.getElementById('context-menu');
            if (menu) {
                // Limpiar menú existente
                menu.innerHTML = '';
                
                // Configurar acciones del menú según el tipo
                configurarMenuContextual(menu, tipo, ruta, nombre);
                
                // Posicionar el menú - Enfoque simple
                menu.style.left = e.pageX + 'px';
                menu.style.top = e.pageY + 'px';
                
                // Hacer visible el menú
                menu.style.display = 'block';
                
                // Verificar si el menú se sale de la pantalla
                const rect = menu.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                
                if (rect.right > viewportWidth) {
                    menu.style.left = (e.pageX - rect.width) + 'px';
                }
                
                if (rect.bottom > viewportHeight) {
                    menu.style.top = (e.pageY - rect.height) + 'px';
                }
                
                console.log('Menú contextual mostrado en:', { 
                    left: menu.style.left, 
                    top: menu.style.top,
                    width: rect.width,
                    height: rect.height
                });
            }
        });
    });
    
    // Cerrar menú contextual al hacer clic en cualquier parte
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('context-menu');
        // Verificar si el clic fue en un elemento que tiene o está dentro de un elemento con clase 'item'
        const clickedOnItem = e.target.closest('.item');
        
        // Si el clic no fue en un elemento del menú y no fue un clic derecho en un elemento
        if (menu && !menu.contains(e.target) && !e.button === 2) {
            console.log('Cerrando menú contextual por clic fuera');
            menu.classList.remove('active');
            menu.style.display = 'none';
        }
    });
    
    // Cerrar menú contextual al tocar en dispositivos móviles
    document.addEventListener('touchstart', function(e) {
        const menu = document.getElementById('context-menu');
        if (menu && !menu.contains(e.target)) {
            console.log('Cerrando menú contextual por toque fuera');
            menu.classList.remove('active');
            menu.style.display = 'none';
        }
    });
    
    // Cerrar menú contextual al presionar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const menu = document.getElementById('context-menu');
            if (menu && menu.style.display !== 'none') {
                console.log('Cerrando menú contextual por tecla ESC');
                menu.classList.remove('active');
                menu.style.display = 'none';
            }
        }
    });
    
    // Función para configurar el menú contextual
    function configurarMenuContextual(menu, tipo, ruta, nombre) {
        console.log('Configurando menú contextual:', { tipo, ruta, nombre });
        
        // Limpiar menú existente
        menu.innerHTML = '';
        
        // Acciones comunes
        agregarOpcionMenu(menu, 'Abrir', 'fas fa-folder-open', function() {
            const enlace = document.querySelector(`.item[data-ruta="${ruta}"] a`);
            if (enlace) {
                window.location.href = enlace.href;
            }
        });
        
        agregarOpcionMenu(menu, 'Renombrar', 'fas fa-edit', function() {
            if (tipo === 'carpeta') {
                const boton = document.querySelector(`.item[data-ruta="${ruta}"] .btn-editar-carpeta`);
                if (boton) {
                    boton.click();
                }
            } else {
                abrirModalRenombrarArchivo(ruta, nombre);
            }
        });
        
        agregarOpcionMenu(menu, 'Eliminar', 'fas fa-trash', function() {
            confirmarEliminar(ruta, nombre, tipo);
        });
        
        // Acciones específicas para archivos
        if (tipo === 'archivo') {
            const extension = nombre.split('.').pop().toLowerCase();
            const extensionesTexto = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'doc', 'docx', 'rtf', 'odt', 'tex'];
            
            if (extensionesTexto.includes(extension)) {
                agregarOpcionMenu(menu, 'Editar contenido', 'fas fa-file-alt', function() {
                    window.location.href = `index.php?modulo=editar_archivo&archivo=${encodeURIComponent(ruta)}`;
                });
            }
            
            agregarOpcionMenu(menu, 'Descargar', 'fas fa-download', function() {
                window.location.href = `index.php?modulo=descargar&archivo=${encodeURIComponent(ruta)}`;
            });
        }
        
        // Verificar si se agregaron elementos al menú
        if (menu.children.length === 0) {
            // Si no hay elementos, agregar un mensaje
            const noOpciones = document.createElement('div');
            noOpciones.className = 'context-menu-item';
            noOpciones.innerHTML = '<i class="fas fa-info-circle"></i> No hay opciones disponibles';
            menu.appendChild(noOpciones);
        }
        
        console.log('Menú contextual configurado con', menu.children.length, 'elementos');
    }
    
    // Función para agregar opciones al menú contextual
    function agregarOpcionMenu(menu, texto, icono, accion) {
        const item = document.createElement('div');
        item.className = 'context-menu-item';
        item.innerHTML = `<i class="${icono}"></i> ${texto}`;
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Ocultar el menú antes de ejecutar la acción
            const contextMenu = document.getElementById('context-menu');
            if (contextMenu) {
                contextMenu.classList.remove('active');
                contextMenu.style.display = 'none';
            }
            
            // Ejecutar la acción después de un breve retraso
            setTimeout(() => {
                accion();
            }, 50);
        });
        menu.appendChild(item);
        
        console.log('Opción agregada al menú:', texto);
    }
});
