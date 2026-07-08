/**
 * Script para mejorar la funcionalidad del buscador
 */
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const searchInput = document.getElementById('buscarInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const searchForm = document.querySelector('.search-form');
    
    // Función para mostrar sugerencias mientras se escribe
    if (searchInput && searchSuggestions) {
        let typingTimer;
        const doneTypingInterval = 300; // tiempo en ms
        
        // Evento al escribir en el campo de búsqueda
        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            
            const query = this.value.trim();
            if (query.length < 2) {
                searchSuggestions.style.display = 'none';
                return;
            }
            
            typingTimer = setTimeout(function() {
                // Aquí se podrían cargar sugerencias desde el servidor
                // Por ahora usamos un enfoque simple basado en el historial local
                const recentSearches = getRecentSearchesFromDOM();
                
                // Filtrar búsquedas que coincidan con lo que está escribiendo el usuario
                const matchingSearches = recentSearches.filter(search => 
                    search.toLowerCase().includes(query.toLowerCase())
                );
                
                // Mostrar sugerencias
                if (matchingSearches.length > 0) {
                    showSuggestions(matchingSearches, query);
                } else {
                    searchSuggestions.style.display = 'none';
                }
            }, doneTypingInterval);
        });
        
        // Ocultar sugerencias al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!searchForm.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });
        
        // Navegar por las sugerencias con teclado
        searchInput.addEventListener('keydown', function(e) {
            if (searchSuggestions.style.display === 'none') return;
            
            const items = searchSuggestions.querySelectorAll('.suggestion-item');
            if (items.length === 0) return;
            
            let activeIndex = -1;
            for (let i = 0; i < items.length; i++) {
                if (items[i].classList.contains('active')) {
                    activeIndex = i;
                    break;
                }
            }
            
            // Tecla flecha abajo
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (activeIndex < items.length - 1) {
                    if (activeIndex >= 0) items[activeIndex].classList.remove('active');
                    items[activeIndex + 1].classList.add('active');
                }
            }
            // Tecla flecha arriba
            else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (activeIndex > 0) {
                    items[activeIndex].classList.remove('active');
                    items[activeIndex - 1].classList.add('active');
                }
            }
            // Tecla Enter para seleccionar sugerencia
            else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                searchInput.value = items[activeIndex].textContent.trim();
                searchSuggestions.style.display = 'none';
                searchForm.submit();
            }
            // Tecla Escape para cerrar sugerencias
            else if (e.key === 'Escape') {
                searchSuggestions.style.display = 'none';
            }
        });
    }
    
    // Función para extraer búsquedas recientes del DOM
    function getRecentSearchesFromDOM() {
        const recentSearches = [];
        const recentSearchesContainer = document.querySelector('.recent-searches');
        
        if (recentSearchesContainer) {
            const badges = recentSearchesContainer.querySelectorAll('.badge');
            badges.forEach(badge => {
                recentSearches.push(badge.textContent.trim());
            });
        }
        
        return recentSearches;
    }
    
    // Función para mostrar sugerencias
    function showSuggestions(suggestions, query) {
        searchSuggestions.innerHTML = '';
        
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            
            // Resaltar la parte que coincide con la consulta
            const regex = new RegExp('(' + escapeRegExp(query) + ')', 'gi');
            const highlightedText = suggestion.replace(regex, '<span class="highlight-match">$1</span>');
            
            item.innerHTML = highlightedText;
            
            // Evento al hacer clic en una sugerencia
            item.addEventListener('click', function() {
                searchInput.value = suggestion;
                searchSuggestions.style.display = 'none';
                searchForm.submit();
            });
            
            searchSuggestions.appendChild(item);
        });
        
        searchSuggestions.style.display = 'block';
    }
    
    // Función auxiliar para escapar caracteres especiales en expresiones regulares
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    // Inicialización de filtros en la página de resultados de búsqueda
    const filtroTipo = document.getElementById('filtroTipo');
    const filtroOrden = document.getElementById('filtroOrden');
    
    if (filtroTipo && filtroOrden) {
        // Aplicar filtros al cambiar
        filtroTipo.addEventListener('change', aplicarFiltros);
        filtroOrden.addEventListener('change', aplicarFiltros);
        
        // Función para aplicar filtros
        function aplicarFiltros() {
            const currentUrl = new URL(window.location.href);
            const params = currentUrl.searchParams;
            
            // Actualizar parámetros
            params.set('tipo', filtroTipo.value);
            params.set('ordenar', filtroOrden.value);
            
            // Redirigir con los nuevos parámetros
            window.location.href = currentUrl.toString();
        }
        
        // Preseleccionar filtros basados en la URL actual
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('tipo')) {
            filtroTipo.value = urlParams.get('tipo');
        }
        if (urlParams.has('ordenar')) {
            filtroOrden.value = urlParams.get('ordenar');
        }
    }
    
    // Manejo de filas clicables en la tabla de resultados
    const resultadosTabla = document.querySelector('.search-results-table');
    if (resultadosTabla) {
        const filas = resultadosTabla.querySelectorAll('tbody tr');
        
        filas.forEach(fila => {
            // Obtener la URL de la acción "Abrir" para esta fila
            const abrirBtn = fila.querySelector('.btn-abrir');
            if (abrirBtn) {
                const url = abrirBtn.getAttribute('href');
                
                // Hacer que la fila sea clicable (excepto en los botones de acción)
                fila.addEventListener('click', function(e) {
                    // No navegar si se hizo clic en un botón o enlace
                    if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                        e.target.closest('a') || e.target.closest('button')) {
                        return;
                    }
                    
                    // Navegar a la URL de "Abrir"
                    if (url) {
                        window.location.href = url;
                    }
                });
            }
        });
    }
    
    // Tooltips para los botones de acción
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top',
            trigger: 'hover'
        });
    });
});
