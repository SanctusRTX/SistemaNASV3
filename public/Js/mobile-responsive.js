/**
 * Script para mejorar la experiencia en dispositivos móviles
 * Sistema NAS v3
 * 
 * Nota: La funcionalidad de la barra lateral se ha movido a sidebar-controller.js
 * para unificar el comportamiento en dispositivos móviles y escritorio
 */

document.addEventListener('DOMContentLoaded', function() {
    // Detectar si es un dispositivo móvil
    const isMobile = window.innerWidth <= 768;
    
    // Mejorar las tablas para móviles
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        // Añadir clase para hacer las tablas responsive
        table.classList.add('table-responsive');
        
        if (isMobile) {
            // Convertir tablas a formato móvil
            table.classList.add('table-mobile-view');
            
            // Agregar atributos data-label a las celdas para mostrar el nombre de la columna
            const headerCells = table.querySelectorAll('thead th');
            const headerTexts = Array.from(headerCells).map(th => th.textContent.trim());
            
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (index < headerTexts.length) {
                        cell.setAttribute('data-label', headerTexts[index]);
                    }
                });
            });
        }
    });
    
    // Optimizar listas de archivos y carpetas para móviles
    if (isMobile) {
        // Mejorar visualización de archivos
        const archivosContainers = document.querySelectorAll('.archivos-section');
        archivosContainers.forEach(container => {
            container.classList.add('mobile-file-list');
            
            // Mejorar estructura de items de archivo
            const archivoItems = container.querySelectorAll('.archivo-item');
            archivoItems.forEach(item => {
                // Verificar si ya tiene la estructura mejorada
                if (!item.querySelector('.file-header')) {
                    // Obtener elementos existentes
                    const icon = item.querySelector('i') || item.querySelector('ion-icon');
                    const nameLink = item.querySelector('a');
                    const actionButtons = item.querySelectorAll('.btn');
                    
                    // Crear nueva estructura
                    const fileHeader = document.createElement('div');
                    fileHeader.className = 'file-header';
                    
                    if (icon) {
                        icon.className += ' file-icon';
                        fileHeader.appendChild(icon.cloneNode(true));
                    }
                    
                    if (nameLink) {
                        const fileName = document.createElement('div');
                        fileName.className = 'file-name';
                        fileName.innerHTML = nameLink.innerHTML;
                        fileHeader.appendChild(fileName);
                    }
                    
                    // Crear contenedor para acciones
                    const fileActions = document.createElement('div');
                    fileActions.className = 'file-actions';
                    
                    // Mover botones de acción al nuevo contenedor
                    actionButtons.forEach(button => {
                        button.classList.add('btn-mobile-touch');
                        fileActions.appendChild(button.cloneNode(true));
                    });
                    
                    // Limpiar y reconstruir el item
                    item.innerHTML = '';
                    item.appendChild(fileHeader);
                    item.appendChild(fileActions);
                }
            });
        });
        
        // Mejorar visualización de carpetas
        const carpetasContainers = document.querySelectorAll('.carpetas-container');
        carpetasContainers.forEach(container => {
            container.classList.add('mobile-file-list');
            
            // Mejorar estructura de items de carpeta
            const carpetaItems = container.querySelectorAll('.carpeta-item');
            carpetaItems.forEach(item => {
                // Verificar si ya tiene la estructura mejorada
                if (!item.querySelector('.folder-header')) {
                    // Obtener elementos existentes
                    const icon = item.querySelector('i') || item.querySelector('ion-icon');
                    const nameLink = item.querySelector('a');
                    const actionButtons = item.querySelectorAll('.btn');
                    
                    // Crear nueva estructura
                    const folderHeader = document.createElement('div');
                    folderHeader.className = 'folder-header';
                    
                    if (icon) {
                        icon.className += ' folder-icon';
                        folderHeader.appendChild(icon.cloneNode(true));
                    }
                    
                    if (nameLink) {
                        const folderName = document.createElement('div');
                        folderName.className = 'folder-name';
                        folderName.innerHTML = nameLink.innerHTML;
                        folderHeader.appendChild(folderName);
                    }
                    
                    // Crear contenedor para acciones
                    const folderActions = document.createElement('div');
                    folderActions.className = 'folder-actions';
                    
                    // Mover botones de acción al nuevo contenedor
                    actionButtons.forEach(button => {
                        button.classList.add('btn-mobile-touch');
                        folderActions.appendChild(button.cloneNode(true));
                    });
                    
                    // Limpiar y reconstruir el item
                    item.innerHTML = '';
                    item.appendChild(folderHeader);
                    item.appendChild(folderActions);
                }
            });
        });
        
        // Ajustar tamaño de todos los botones para facilitar toque en móviles
        const actionButtons = document.querySelectorAll('.btn-funcion');
        actionButtons.forEach(button => {
            button.classList.add('btn-mobile-touch');
        });
    }
    
    // Mejorar la experiencia de los modales en móviles
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (isMobile) {
            const modalDialog = modal.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.margin = '10px';
                modalDialog.style.width = 'calc(100% - 20px)';
                modalDialog.style.maxWidth = 'none';
            }
        }
    });
    
    // Ajustar breadcrumb para móviles
    const breadcrumbs = document.querySelectorAll('.breadcrumb');
    breadcrumbs.forEach(breadcrumb => {
        if (isMobile) {
            breadcrumb.style.overflowX = 'auto';
            breadcrumb.style.whiteSpace = 'nowrap';
            breadcrumb.style.display = 'block';
            breadcrumb.style.padding = '0.5rem';
        }
    });
    
    // Mejorar la experiencia de formularios en móviles
    if (isMobile) {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.classList.add('form-control-mobile');
            });
        });
    }
    
    // Detectar cambios en el tamaño de la ventana
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const isNowMobile = window.innerWidth <= 768;
            
            // Si cambia de escritorio a móvil o viceversa, recargar la página
            if (isNowMobile !== isMobile) {
                location.reload();
            }
        }, 250);
    });
});
