<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-search"></i> Resultados de búsqueda
            @if(!empty($termino))
                para: <em>"{{ $termino }}"</em>
            @endif
        </h5>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">
            <i class="fas fa-times"></i> Limpiar
        </a>
    </div>
    <div class="card-body p-0">
        <div id="resultados-busqueda-container">
            @if(empty($termino))
            <div class="text-center p-5 text-muted">
                <i class="fas fa-search fa-3x mb-3 d-block"></i>
                Usa el campo de búsqueda en el panel izquierdo.
            </div>
            @else
            <div class="text-center p-4">
                <i class="fas fa-spinner fa-spin"></i> Buscando...
            </div>
            @endif
        </div>
    </div>
</div>

@if(!empty($termino))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const termino = '{{ addslashes($termino) }}';
    const contenedor = document.getElementById('resultados-busqueda-container');

    fetch('/explorador/buscar?q=' + encodeURIComponent(termino), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.resultados || data.resultados.length === 0) {
            contenedor.innerHTML = `
                <div class="text-center p-5 text-muted">
                    <i class="fas fa-search fa-3x mb-3 d-block"></i>
                    No se encontraron resultados para "<strong>${termino}</strong>".
                </div>`;
            return;
        }

        let html = `
            <div class="p-3 border-bottom bg-light">
                <small class="text-muted"><i class="fas fa-info-circle"></i> Se encontraron <strong>${data.total}</strong> resultado(s).</small>
            </div>
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th><i class="fas fa-file"></i> Nombre</th>
                        <th><i class="fas fa-tag"></i> Tipo</th>
                        <th><i class="fas fa-hdd"></i> Tamaño</th>
                        <th><i class="fas fa-calendar"></i> Fecha</th>
                        <th><i class="fas fa-map-marker-alt"></i> Ubicación</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>`;

        data.resultados.forEach(item => {
            const iconClass = item.tipo === 'carpeta' ? 'fa-folder text-warning' : item.icono + ' text-secondary';
            const badgeClass = item.tipo === 'carpeta' ? 'warning' : 'secondary';
            const rutaPadre = item.ruta.includes('/') ? item.ruta.substring(0, item.ruta.lastIndexOf('/')) : '';

            let acciones = '';
            if (item.tipo === 'carpeta') {
                acciones = `<a href="/dashboard?carpeta=${encodeURIComponent(item.ruta)}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-folder-open"></i> Abrir
                </a>`;
            } else {
                acciones = `<a href="/descargar?archivo=${encodeURIComponent(item.ruta)}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-download"></i> Descargar
                </a>`;
            }

            html += `
                <tr>
                    <td><i class="fas ${iconClass} mr-1"></i> ${item.nombre}</td>
                    <td><span class="badge badge-${badgeClass}">${item.tipo}</span></td>
                    <td>${item.size}</td>
                    <td><small>${item.fecha}</small></td>
                    <td><small class="text-muted">${rutaPadre || 'Raíz'}</small></td>
                    <td class="text-center">${acciones}</td>
                </tr>`;
        });

        html += '</tbody></table></div>';
        contenedor.innerHTML = html;
    })
    .catch(() => {
        contenedor.innerHTML = '<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle"></i> Error al realizar la búsqueda.</div>';
    });
});
</script>
@endif
