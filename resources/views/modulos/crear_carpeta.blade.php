{{-- Módulo: Crear Carpeta — Nexus OS Style --}}
<div style="max-width: 560px; margin: 0 auto;">

    {{-- Header del módulo --}}
    <div class="d-flex align-items-center mb-4" style="gap: 0.75rem;">
        <div style="width:38px; height:38px; border-radius:10px; background:rgba(96,165,250,0.12); border:1px solid rgba(96,165,250,0.25); display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-folder-plus" style="color:#60a5fa; font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0" style="font-weight:700; font-size:1rem; color:#f1f5f9;">Crear nueva carpeta</h5>
            <p class="mb-0" style="font-size:0.72rem; color:#64748b;">Se creará dentro del almacenamiento seleccionado</p>
        </div>
    </div>

    {{-- Alerta --}}
    <div id="alerta-crear-carpeta" class="mb-3"></div>

    {{-- Card formulario --}}
    <div style="background:rgba(15,19,28,0.8); border:1px solid rgba(255,255,255,0.06); border-radius:16px; padding:1.75rem; backdrop-filter:blur(10px);">
        <form id="formCrearCarpeta">
            @csrf

            <div class="mb-4">
                <label style="font-size:0.78rem; font-weight:600; color:#94a3b8; letter-spacing:0.04em; text-transform:uppercase;">
                    <i class="fas fa-folder mr-1" style="color:#60a5fa;"></i> Nombre de la carpeta
                </label>
                <input type="text" class="form-control mt-1" id="nombre_carpeta" name="nombre"
                       placeholder="Ej: Documentos 2025" autocomplete="off"
                       style="background:rgba(255,255,255,0.04)!important; border:1px solid rgba(255,255,255,0.08)!important; border-radius:10px!important; color:#f1f5f9!important; font-size:0.9rem; height:42px; padding: 0.5rem 1rem;">
                <small style="font-size:0.7rem; color:#475569; margin-top:0.4rem; display:block;">Solo letras, números, guiones, puntos y espacios.</small>
            </div>

            <div class="mb-4">
                <label style="font-size:0.78rem; font-weight:600; color:#94a3b8; letter-spacing:0.04em; text-transform:uppercase;">
                    <i class="fas fa-sitemap mr-1" style="color:#3AB397;"></i> Ubicación (carpeta padre)
                </label>
                <select class="form-control mt-1" id="carpeta_padre" name="carpeta_padre"
                        style="background:rgba(255,255,255,0.04)!important; border:1px solid rgba(255,255,255,0.08)!important; border-radius:10px!important; color:#f1f5f9!important; font-size:0.85rem; height:42px;">
                    <option value="">📁 Almacenamiento (Raíz)</option>
                    @foreach($todasLasCarpetas as $c)
                        @php $indent = str_repeat('&nbsp;', $c['level'] * 4); @endphp
                        <option value="{{ $c['name'] }}" {{ $carpeta === $c['name'] ? 'selected' : '' }}>
                            {!! $indent !!}{{ $c['level'] > 0 ? '└─ ' : '' }}{{ basename($c['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2" style="gap:0.75rem;">
                <a href="{{ route('dashboard') }}"
                   style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem; border-radius:9px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); color:#94a3b8; font-size:0.82rem; text-decoration:none; transition:all 0.2s;"
                   onmouseover="this.style.color='#f1f5f9'; this.style.background='rgba(255,255,255,0.07)'"
                   onmouseout="this.style.color='#94a3b8'; this.style.background='rgba(255,255,255,0.04)'">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" id="btnCrearCarpeta"
                        style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.55rem 1.4rem; border-radius:9px; background:linear-gradient(135deg,#3AB397,#10b981); border:none; color:#07090e; font-weight:700; font-size:0.85rem; cursor:pointer; transition:all 0.2s; box-shadow:0 4px 14px rgba(58,179,151,0.25);">
                    <i class="fas fa-folder-plus"></i> Crear carpeta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$('#formCrearCarpeta').on('submit', function(e) {
    e.preventDefault();
    const nombre = $('#nombre_carpeta').val().trim();
    const carpetaPadre = $('#carpeta_padre').val();
    const btn = $('#btnCrearCarpeta');

    if (!nombre) {
        $('#alerta-crear-carpeta').html('<div style="background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#fbbf24;"><i class="fas fa-exclamation-triangle mr-2"></i>Ingresa un nombre para la carpeta.</div>');
        return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');

    fetch('{{ route("explorador.crearCarpeta") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ nombre: nombre, carpeta_padre: carpetaPadre })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#alerta-crear-carpeta').html('<div style="background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#34d399;"><i class="fas fa-check-circle mr-2"></i>' + data.message + '</div>');
            $('#nombre_carpeta').val('');
            setTimeout(() => { window.location.href = '/dashboard'; }, 1500);
        } else {
            $('#alerta-crear-carpeta').html('<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#f87171;"><i class="fas fa-times-circle mr-2"></i>' + data.message + '</div>');
        }
        btn.prop('disabled', false).html('<i class="fas fa-folder-plus"></i> Crear carpeta');
    })
    .catch(() => {
        $('#alerta-crear-carpeta').html('<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#f87171;"><i class="fas fa-times-circle mr-2"></i>Error de conexión.</div>');
        btn.prop('disabled', false).html('<i class="fas fa-folder-plus"></i> Crear carpeta');
    });
});
</script>
