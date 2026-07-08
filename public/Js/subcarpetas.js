$(document).ready(function() {
    $(document).on('click', '.folder-toggle-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const carpeta = $(this).data('carpeta');
        const carpetaId = $(this).data('id');
        const subcarpetasContainer = $(`#subcarpetas-${carpetaId}`);
        const loadingDiv = subcarpetasContainer.find('.loading-subcarpetas');
        
        if (subcarpetasContainer.hasClass('loaded')) {
            subcarpetasContainer.slideToggle(200);
            $(this).toggleClass('expanded');
            return;
        }
        
        subcarpetasContainer.show();
        loadingDiv.show();
        
        console.log('Solicitando subcarpetas para:', carpeta);
        
        $.ajax({
            url: `/explorador/subcarpetas?carpeta=${encodeURIComponent(carpeta)}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta recibida:', response);
                
                if (response.success) {
                    let subcarpetasHtml = '';
                    const subcarpetas = response.subcarpetas || [];
                    
                    if (subcarpetas.length === 0) {
                        subcarpetasHtml = '<div class="no-subcarpetas" style="color: #6c757d; font-size: 0.9em; padding:5px 0;">No hay subcarpetas</div>';
                    } else {
                        subcarpetas.forEach(function(subcarpeta) {
                            const iconoClass = subcarpeta.tiene_subcarpetas ? 'folder-toggle-icon' : '';
                            const iconoStyle = subcarpeta.tiene_subcarpetas ? 'cursor: pointer; color: #3AB397;' : 'color: #ffc107;';
                            const dataId = btoa(subcarpeta.ruta).replace(/=/g, '');
                            const dataAttr = subcarpeta.tiene_subcarpetas ? 
                                `data-carpeta="${subcarpeta.ruta}" data-id="${dataId}"` : '';
                            
                            subcarpetasHtml += `
                                <div class="nav-item my-1" style="margin-left: 10px;">
                                    <div class="d-flex align-items-center">
                                        <ion-icon name="folder-outline" 
                                                  class="${iconoClass}" 
                                                  style="margin-right: 6px; font-size: 1.0rem; ${iconoStyle}" 
                                                  ${dataAttr}
                                                  title="Mostrar subcarpetas">
                                        </ion-icon>
                                        <a class="nav-link" href="/dashboard?carpeta=${encodeURIComponent(subcarpeta.ruta)}" style="color: black; font-size: 0.9em;">
                                            ${subcarpeta.nombre}
                                        </a>
                                    </div>
                                    ${subcarpeta.tiene_subcarpetas ? 
                                        `<div class="subcarpetas-container" id="subcarpetas-${dataId}" style="display:none; margin-left:25px;">
                                            <div class="loading-subcarpetas" style="color:#6c757d; font-size:0.9em;">
                                                <i class="fas fa-spinner fa-spin"></i> Cargando...
                                            </div>
                                        </div>` 
                                    : ''}
                                </div>
                            `;
                        });
                    }
                    
                    loadingDiv.hide();
                    subcarpetasContainer.empty().html(subcarpetasHtml);
                    subcarpetasContainer.addClass('loaded');
                } else {
                    console.error('Error en respuesta:', response);
                    loadingDiv.hide();
                    subcarpetasContainer.html(`<div class="error-subcarpetas" style="color: #dc3545; font-size: 0.9em;">Error: ${response.message || 'Error desconocido'}</div>`);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', {xhr, status, error});
                loadingDiv.hide();
                let errorMessage = 'Error de conexión';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMessage = 'No autorizado';
                } else if (xhr.status === 404) {
                    errorMessage = 'Carpeta no encontrada';
                }
                subcarpetasContainer.html(`<div class="error-subcarpetas" style="color: #dc3545; font-size: 0.9em;">${errorMessage}</div>`);
            }
        });
    });
    
    $(document).on('click', '.nav-link', function(e) {
        e.stopPropagation();
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.nav-item').length) {
            $('.subcarpetas-container').not('.loaded').hide();
            $('.folder-toggle-icon').removeClass('expanded');
        }
    });
});