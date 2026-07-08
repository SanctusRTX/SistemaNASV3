{{-- Módulo: Crear Archivo — Nexus OS Style --}}
<div style="max-width: 720px; margin: 0 auto;">

    <div class="d-flex align-items-center mb-4" style="gap:0.75rem;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(52,211,153,0.12);border:1px solid rgba(52,211,153,0.25);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-file-alt" style="color:#34d399;font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0" style="font-weight:700;font-size:1rem;color:#f1f5f9;">Crear nuevo archivo de texto</h5>
            <p class="mb-0" style="font-size:0.72rem;color:#64748b;">Crea un archivo .txt u otro formato de texto editable</p>
        </div>
    </div>

    <div id="alerta-crear-archivo" class="mb-3"></div>

    <div style="background:rgba(15,19,28,0.8);border:1px solid rgba(255,255,255,0.06);border-radius:16px;padding:1.75rem;backdrop-filter:blur(10px);">
        <form id="formCrearArchivo">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label style="font-size:0.78rem;font-weight:600;color:#94a3b8;letter-spacing:0.04em;text-transform:uppercase;">
                        <i class="fas fa-file mr-1" style="color:#34d399;"></i> Nombre del archivo
                    </label>
                    <input type="text" class="form-control mt-1" id="nombre_archivo" name="nombre"
                           placeholder="Ej: notas.txt" autocomplete="off"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:10px!important;color:#f1f5f9!important;font-size:0.9rem;height:42px;padding:0.5rem 1rem;">
                    <small style="font-size:0.7rem;color:#475569;margin-top:0.4rem;display:block;">Incluye la extensión: .txt .html .csv</small>
                </div>
                <div class="col-md-6">
                    <label style="font-size:0.78rem;font-weight:600;color:#94a3b8;letter-spacing:0.04em;text-transform:uppercase;">
                        <i class="fas fa-folder mr-1" style="color:#3AB397;"></i> Carpeta destino
                    </label>
                    <select class="form-control mt-1" id="carpeta_destino_archivo" name="carpeta_destino"
                            style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:10px!important;color:#f1f5f9!important;font-size:0.85rem;height:42px;">
                        <option value="">📁 Almacenamiento (Raíz)</option>
                        @foreach($todasLasCarpetas as $c)
                            @php $indent = str_repeat('&nbsp;', $c['level'] * 4); @endphp
                            <option value="{{ $c['name'] }}">
                                {!! $indent !!}{{ $c['level'] > 0 ? '└─ ' : '' }}{{ basename($c['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label style="font-size:0.78rem;font-weight:600;color:#94a3b8;letter-spacing:0.04em;text-transform:uppercase;">
                    <i class="fas fa-align-left mr-1" style="color:#67e8f9;"></i> Contenido inicial <span style="text-transform:none;font-weight:400;color:#475569;">(opcional)</span>
                </label>
                <textarea class="form-control mt-1" id="contenido_archivo" name="contenido" rows="10"
                          placeholder="Escribe el contenido del archivo aquí..."
                          style="background:rgba(5,7,11,0.8)!important;border:1px solid rgba(255,255,255,0.06)!important;border-radius:12px!important;color:#a7f3d0!important;font-family:'Courier New',monospace;font-size:0.9rem;line-height:1.6;padding:1rem 1.25rem;resize:vertical;"></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center" style="gap:0.75rem;">
                <a href="{{ route('dashboard') }}"
                   style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;border-radius:9px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;font-size:0.82rem;text-decoration:none;transition:all 0.2s;"
                   onmouseover="this.style.color='#f1f5f9';this.style.background='rgba(255,255,255,0.07)'"
                   onmouseout="this.style.color='#94a3b8';this.style.background='rgba(255,255,255,0.04)'">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" id="btnCrearArchivo"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.4rem;border-radius:9px;background:linear-gradient(135deg,#34d399,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.85rem;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 14px rgba(52,211,153,0.25);">
                    <i class="fas fa-file-alt"></i> Crear archivo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$('#formCrearArchivo').on('submit', function(e) {
    e.preventDefault();
    const nombre = $('#nombre_archivo').val().trim();
    const carpeta = $('#carpeta_destino_archivo').val();
    const contenido = $('#contenido_archivo').val();
    const btn = $('#btnCrearArchivo');

    if (!nombre) {
        $('#alerta-crear-archivo').html('<div style="background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#fbbf24;"><i class="fas fa-exclamation-triangle mr-2"></i>Ingresa un nombre para el archivo.</div>');
        return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');

    fetch('{{ route("explorador.crearArchivo") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ nombre: nombre, carpeta_destino: carpeta, contenido: contenido })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#alerta-crear-archivo').html('<div style="background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#34d399;"><i class="fas fa-check-circle mr-2"></i>' + data.message + '</div>');
            setTimeout(() => { window.location.href = '/dashboard'; }, 1500);
        } else {
            $('#alerta-crear-archivo').html('<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#f87171;"><i class="fas fa-times-circle mr-2"></i>' + data.message + '</div>');
            btn.prop('disabled', false).html('<i class="fas fa-file-alt"></i> Crear archivo');
        }
    })
    .catch(() => {
        $('#alerta-crear-archivo').html('<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#f87171;"><i class="fas fa-times-circle mr-2"></i>Error de conexión.</div>');
        btn.prop('disabled', false).html('<i class="fas fa-file-alt"></i> Crear archivo');
    });
});
</script>
