@if(!empty($datos) && isset($datos['ruta']))

{{-- Header editor --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap:0.75rem;">
    <div class="d-flex align-items-center" style="gap:0.75rem;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(103,232,249,0.12);border:1px solid rgba(103,232,249,0.25);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-file-code" style="color:#67e8f9;font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0" style="font-weight:700;font-size:0.95rem;color:#f1f5f9;">
                Editando: <span style="color:#67e8f9;">{{ $datos['nombre'] }}</span>
            </h5>
            <p class="mb-0" style="font-size:0.7rem;color:#475569;font-family:monospace;">{{ $datos['ruta'] }}</p>
        </div>
    </div>
    <div class="d-flex align-items-center" style="gap:0.6rem;">
        <span id="estado-guardado" style="font-size:0.72rem;color:#475569;">Sin cambios</span>
        <button id="btnGuardarArchivo"
                style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 1rem;border-radius:8px;background:linear-gradient(135deg,#67e8f9,#22d3ee);border:none;color:#07090e;font-weight:700;font-size:0.8rem;cursor:pointer;box-shadow:0 4px 12px rgba(103,232,249,0.2);">
            <i class="fas fa-save"></i> Guardar
        </button>
        <a href="{{ route('dashboard') }}"
           style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 0.9rem;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;font-size:0.8rem;text-decoration:none;"
           onmouseover="this.style.color='#f1f5f9'" onmouseout="this.style.color='#94a3b8'">
            <i class="fas fa-times"></i> Cerrar
        </a>
    </div>
</div>

<div id="alerta-editar" class="mb-2"></div>

{{-- Editor area --}}
<div style="background:rgba(5,7,11,0.95);border:1px solid rgba(255,255,255,0.06);border-radius:16px;overflow:hidden;">
    {{-- Barra superior del editor --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 1rem;background:rgba(255,255,255,0.02);border-bottom:1px solid rgba(255,255,255,0.04);">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:#fbbf24;display:inline-block;"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:#34d399;display:inline-block;"></span>
        </div>
        <span style="font-size:0.7rem;color:#475569;font-family:monospace;">{{ $datos['nombre'] }}</span>
        <span style="font-size:0.65rem;color:#374151;">Ctrl+S para guardar</span>
    </div>
    <textarea id="editor_contenido"
              rows="28"
              style="width:100%;background:transparent!important;border:none!important;color:#a7f3d0;font-family:'Courier New',Courier,monospace;font-size:0.9rem;line-height:1.7;padding:1.25rem 1.5rem;resize:vertical;outline:none;display:block;">{{ $datos['contenido'] }}</textarea>
</div>

<script>
const rutaArchivo = '{{ $datos['ruta'] }}';

document.getElementById('editor_contenido').addEventListener('input', function() {
    const estado = document.getElementById('estado-guardado');
    estado.textContent = '● Sin guardar';
    estado.style.color = '#f87171';
});

document.getElementById('btnGuardarArchivo').addEventListener('click', guardarArchivo);

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        guardarArchivo();
    }
});

function guardarArchivo() {
    const contenido = document.getElementById('editor_contenido').value;
    const btn = document.getElementById('btnGuardarArchivo');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    fetch('{{ route("explorador.guardarArchivo") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ruta: rutaArchivo, contenido: contenido })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('alerta-editar').innerHTML =
                '<div style="background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.3);border-radius:10px;padding:0.6rem 1rem;font-size:0.8rem;color:#34d399;margin-bottom:0.5rem;"><i class="fas fa-check-circle mr-2"></i>' + data.message + '</div>';
            const estado = document.getElementById('estado-guardado');
            estado.textContent = '✔ Guardado';
            estado.style.color = '#34d399';
            setTimeout(() => { document.getElementById('alerta-editar').innerHTML = ''; }, 3000);
        } else {
            document.getElementById('alerta-editar').innerHTML =
                '<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.6rem 1rem;font-size:0.8rem;color:#f87171;margin-bottom:0.5rem;"><i class="fas fa-times-circle mr-2"></i>' + data.message + '</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
    })
    .catch(() => {
        document.getElementById('alerta-editar').innerHTML =
            '<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.6rem 1rem;font-size:0.8rem;color:#f87171;margin-bottom:0.5rem;">Error de conexión.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
    });
}
</script>

@else
<div style="background:rgba(234,179,8,0.08);border:1px solid rgba(234,179,8,0.25);border-radius:12px;padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;">
    <i class="fas fa-exclamation-triangle" style="color:#fbbf24;font-size:1.25rem;flex-shrink:0;"></i>
    <div style="flex:1;">
        <div style="font-size:0.85rem;color:#fbbf24;font-weight:600;">Archivo no encontrado</div>
        <div style="font-size:0.75rem;color:#64748b;margin-top:0.25rem;">No se especificó ningún archivo para editar, o el archivo no existe.</div>
    </div>
    <a href="{{ route('dashboard') }}"
       style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 1rem;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#94a3b8;font-size:0.8rem;text-decoration:none;flex-shrink:0;">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>
@endif
