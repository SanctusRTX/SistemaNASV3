{{-- Módulo: Papelera — Nexus OS Style --}}

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap" style="gap:0.75rem;">
    <div class="d-flex align-items-center" style="gap:0.75rem;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-trash-alt" style="color:#ef4444;font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0" style="font-weight:700;font-size:1rem;color:#f1f5f9;">Papelera de Reciclaje</h5>
            <p class="mb-0" style="font-size:0.72rem;color:#64748b;">Elementos eliminados recientemente — Puedes restaurarlos</p>
        </div>
    </div>
    @if(session('rol') === 'administrador')
    <button id="btnVaciarPapelera"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.1rem;border-radius:9px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;font-weight:700;font-size:0.8rem;cursor:pointer;transition:all 0.2s;"
            onmouseover="this.style.background='rgba(239,68,68,0.18)'"
            onmouseout="this.style.background='rgba(239,68,68,0.1)'">
        <i class="fas fa-fire"></i> Vaciar papelera
    </button>
    @endif
</div>

<div style="background:rgba(15,19,28,0.8);border:1px solid rgba(255,255,255,0.06);border-radius:16px;overflow:hidden;backdrop-filter:blur(10px);">

    {{-- Loading --}}
    <div id="papelera-cargando" class="text-center py-5" style="color:#475569;">
        <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;margin-bottom:0.75rem;display:block;"></i>
        <span style="font-size:0.85rem;">Cargando papelera...</span>
    </div>

    {{-- Vacía --}}
    <div id="papelera-vacia" class="text-center py-5 d-none" style="color:#374151;">
        <i class="fas fa-trash" style="font-size:3rem;display:block;margin-bottom:1rem;color:#1e293b;"></i>
        <p style="font-size:0.9rem;font-weight:500;">La papelera está vacía</p>
        <p style="font-size:0.75rem;color:#374151;">Los elementos eliminados aparecerán aquí</p>
    </div>

    {{-- Tabla --}}
    <div id="papelera-tabla" class="d-none">
        <div style="padding:0.65rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.04);">
            <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#475569;">Elementos en papelera</span>
        </div>
        <div class="table-responsive">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                        <th style="padding:0.65rem 1.25rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Nombre original</th>
                        <th style="padding:0.65rem 1.25rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Tipo</th>
                        <th style="padding:0.65rem 1.25rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Eliminado por</th>
                        <th style="padding:0.65rem 1.25rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Fecha</th>
                        <th style="padding:0.65rem 1.25rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="papelera-body"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal confirmar vaciar papelera --}}
<div class="modal fade" id="modalVaciarPapelera" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(239,68,68,0.2);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Vaciar papelera</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:1rem;font-size:0.83rem;color:#f87171;">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>¡Esta acción es irreversible!</strong><br>
                    <span style="opacity:0.8;">Todos los elementos en la papelera serán eliminados permanentemente del sistema.</span>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnConfirmarVaciar"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.45rem 1.1rem;border-radius:8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.35);color:#ef4444;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-fire"></i> Sí, vaciar todo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal confirmar eliminar elemento individual --}}
<div class="modal fade" id="modalEliminarItem" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(239,68,68,0.2);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Eliminar permanentemente</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:1rem;font-size:0.83rem;color:#f87171;">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>¡Esta acción es irreversible!</strong><br>
                    <span style="opacity:0.8;">El elemento <strong id="eliminarItemNombre" style="color:#fca5a5;"></strong> será eliminado permanentemente del sistema.</span>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnConfirmarEliminarItem"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.45rem 1.1rem;border-radius:8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.35);color:#ef4444;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-trash-alt"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarPapelera() {
    fetch('/explorador/papelera', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('papelera-cargando').classList.add('d-none');

        if (!data || data.length === 0) {
            document.getElementById('papelera-vacia').classList.remove('d-none');
            document.getElementById('papelera-tabla').classList.add('d-none');
            return;
        }

        document.getElementById('papelera-vacia').classList.add('d-none');
        document.getElementById('papelera-tabla').classList.remove('d-none');

        let html = '';
        data.forEach(item => {
            const esCarpeta = item.tipo === 'carpeta';
            const iconoStyle = esCarpeta ? 'color:#3AB397' : 'color:#94a3b8';
            const iconoClass = esCarpeta ? 'fa-folder' : 'fa-file';
            html += `
                <tr style="border-bottom:1px solid rgba(255,255,255,0.03);transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:0.8rem 1.25rem;">
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <i class="fas ${iconoClass}" style="${iconoStyle};font-size:0.9rem;"></i>
                            <span style="font-size:0.83rem;color:#e2e8f0;font-weight:500;">${item.nombre_original}</span>
                        </div>
                    </td>
                    <td style="padding:0.8rem 1.25rem;">
                        <span style="display:inline-flex;align-items:center;padding:0.15rem 0.5rem;border-radius:5px;font-size:0.7rem;font-weight:600;background:${esCarpeta ? 'rgba(58,179,151,0.1)' : 'rgba(100,116,139,0.12)'};color:${esCarpeta ? '#3AB397' : '#64748b'};">
                            ${item.tipo}
                        </span>
                    </td>
                    <td style="padding:0.8rem 1.25rem;font-size:0.78rem;color:#64748b;">
                        <i class="fas fa-user-circle mr-1"></i>${item.usuario}
                    </td>
                    <td style="padding:0.8rem 1.25rem;font-size:0.75rem;color:#475569;">${item.fecha_eliminacion}</td>
                    <td style="padding:0.8rem 1.25rem;text-align:center;">
                        <div style="display:inline-flex;align-items:center;gap:0.5rem;">
                            <button class="btn-restaurar"
                                    data-elemento="${item.nombre}"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.85rem;border-radius:7px;background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.25);color:#34d399;font-size:0.75rem;font-weight:600;cursor:pointer;transition:all 0.2s;"
                                    onmouseover="this.style.background='rgba(52,211,153,0.18)'"
                                    onmouseout="this.style.background='rgba(52,211,153,0.1)'">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                            @if(session('rol') === 'administrador')
                            <button class="btn-eliminar-item"
                                    data-elemento="${item.nombre}"
                                    data-nombre="${item.nombre_original}"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.85rem;border-radius:7px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#f87171;font-size:0.75rem;font-weight:600;cursor:pointer;transition:all 0.2s;"
                                    onmouseover="this.style.background='rgba(239,68,68,0.2)'"
                                    onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>`;
        });
        document.getElementById('papelera-body').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('papelera-cargando').innerHTML =
            '<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;margin:1rem;font-size:0.82rem;color:#f87171;"><i class="fas fa-exclamation-circle mr-2"></i>Error al cargar la papelera.</div>';
    });
}

$(document).on('click', '.btn-restaurar', function() {
    const elemento = $(this).data('elemento');
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    fetch('/explorador/restaurar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ elemento: elemento })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.closest('tr').fadeOut(400, function() { $(this).remove(); });
            if ($('#papelera-body tr').length <= 1) {
                document.getElementById('papelera-tabla').classList.add('d-none');
                document.getElementById('papelera-vacia').classList.remove('d-none');
            }
        } else {
            alert('Error: ' + data.message);
            btn.prop('disabled', false).html('<i class="fas fa-undo"></i> Restaurar');
        }
    });
});

@if(session('rol') === 'administrador')
let _eliminarItemElemento = null;

$(document).on('click', '.btn-eliminar-item', function() {
    _eliminarItemElemento = $(this).data('elemento');
    const nombre = $(this).data('nombre');
    document.getElementById('eliminarItemNombre').textContent = nombre;
    $('#modalEliminarItem').modal('show');
});

document.getElementById('btnConfirmarEliminarItem').addEventListener('click', function() {
    if (!_eliminarItemElemento) return;
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');

    fetch('/explorador/eliminar-permanente', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ elemento: _eliminarItemElemento })
    })
    .then(r => r.json())
    .then(data => {
        $('#modalEliminarItem').modal('hide');
        btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Sí, eliminar');
        if (data.success) {
            // Eliminar la fila de la tabla
            $(`.btn-eliminar-item[data-elemento="${_eliminarItemElemento}"]`)
                .closest('tr')
                .fadeOut(400, function() {
                    $(this).remove();
                    if ($('#papelera-body tr').length === 0) {
                        document.getElementById('papelera-tabla').classList.add('d-none');
                        document.getElementById('papelera-vacia').classList.remove('d-none');
                    }
                });
            _eliminarItemElemento = null;
        } else {
            alert('Error: ' + (data.message || 'No se pudo eliminar.'));
        }
    })
    .catch(() => {
        $('#modalEliminarItem').modal('hide');
        btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Sí, eliminar');
        alert('Error de conexión.');
    });
});
@endif

@if(session('rol') === 'administrador')
document.getElementById('btnVaciarPapelera').addEventListener('click', function() {
    $('#modalVaciarPapelera').modal('show');
});

document.getElementById('btnConfirmarVaciar').addEventListener('click', function() {
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Vaciando...');
    fetch('/explorador/vaciar-papelera', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        $('#modalVaciarPapelera').modal('hide');
        if (data.success) {
            document.getElementById('papelera-tabla').classList.add('d-none');
            document.getElementById('papelera-vacia').classList.remove('d-none');
        } else { alert('Error: ' + data.message); }
        btn.prop('disabled', false).html('<i class="fas fa-fire"></i> Sí, vaciar todo');
    });
});
@endif

document.addEventListener('DOMContentLoaded', cargarPapelera);
</script>
