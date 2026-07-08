<style>
.cm-item {
    display: flex; align-items: center; gap: 12px;
    padding: 0.65rem 0.9rem; border-radius: 10px;
    cursor: pointer; transition: all 0.18s ease;
    border: 1px solid transparent;
    margin-bottom: 5px;
    background: rgba(255,255,255,0.02);
}
.cm-item:hover { background: rgba(58,179,151,0.06); border-color: rgba(58,179,151,0.15); }
.cm-item.selected {
    background: rgba(58,179,151,0.1);
    border-color: rgba(58,179,151,0.35);
    box-shadow: 0 0 0 2px rgba(58,179,151,0.08);
}
.cm-item .cm-icon { font-size: 1.3rem; flex-shrink: 0; }
.cm-item .cm-info { flex: 1; min-width: 0; }
.cm-item .cm-name { font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem; }
.cm-item .cm-meta { font-size: 0.72rem; color: #475569; margin-top: 1px; }
.cm-item .cm-check { color: #3AB397; font-size: 1rem; display: none; }
.cm-item.selected .cm-check { display: block; }
.cm-breadcrumb { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; font-size: 0.78rem; margin-bottom: 0.5rem; }
.cm-bc-item { cursor: pointer; color: #3AB397; font-weight: 500; transition: opacity 0.2s; }
.cm-bc-item:hover { opacity: 0.75; }
.cm-bc-sep { color: #374151; margin: 0 2px; }
.cm-selected-preview {
    display: flex; align-items: center; gap: 12px;
    padding: 0.75rem 1rem; border-radius: 10px;
    background: rgba(58,179,151,0.08);
    border: 1px solid rgba(58,179,151,0.25);
}
</style>

<div class="modulo-page modulo-copiarmover">

{{-- Header --}}
<div class="d-flex align-items-center modulo-header" style="gap:0.75rem;">
    <div style="width:38px;height:38px;border-radius:10px;background:rgba(148,163,184,0.1);border:1px solid rgba(148,163,184,0.2);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-copy" style="color:#94a3b8;font-size:1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0" style="font-weight:700;font-size:1rem;color:#f1f5f9;">Copiar / Mover elementos</h5>
        <p class="mb-0" style="font-size:0.72rem;color:#64748b;">Selecciona un archivo o carpeta y elige su destino</p>
    </div>
</div>

<div id="alerta-cm" class="modulo-alert"></div>

<div class="row modulo-grid">

    {{-- Panel izquierdo: Explorador --}}
    <div class="col-md-6">
        <div class="modulo-panel">
            <div class="modulo-panel-head">
                <span><i class="fas fa-folder-open" style="color:#3AB397;margin-right:0.4rem;"></i> Selecciona el elemento</span>
            </div>
            <div class="modulo-panel-body" style="max-height:380px;overflow-y:auto;">
                <div class="cm-breadcrumb" id="cm-breadcrumb">
                    <span class="cm-bc-item" onclick="cargarCM('')">
                        <i class="fas fa-home" style="font-size:0.8rem;"></i> Raíz
                    </span>
                </div>
                <div id="cm-explorador">
                    <div class="text-center p-3" style="color:#475569;">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel derecho: Config --}}
    <div class="col-md-6">
        <div class="modulo-panel">
            <div class="modulo-panel-head">
                <span><i class="fas fa-cogs" style="color:#94a3b8;margin-right:0.4rem;"></i> Configurar operación</span>
            </div>
            <div class="modulo-panel-body">

                {{-- Paso 1: Elemento seleccionado --}}
                <div class="modulo-section">
                    <label style="font-size:0.72rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.08em;display:flex;align-items:center;gap:0.4rem;margin-bottom:0.6rem;">
                        <span style="width:18px;height:18px;border-radius:50%;background:rgba(58,179,151,0.15);border:1px solid rgba(58,179,151,0.3);display:inline-flex;align-items:center;justify-content:center;font-size:0.65rem;color:#3AB397;">1</span>
                        Elemento seleccionado
                    </label>
                    <div id="cm-preview-vacio" class="text-center" style="padding:1rem;border:1px dashed rgba(255,255,255,0.08);border-radius:10px;color:#374151;">
                        <i class="fas fa-hand-pointer" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;color:#1e293b;"></i>
                        <small>Haz clic en un archivo o carpeta del panel izquierdo</small>
                    </div>
                    <div id="cm-preview" class="cm-selected-preview d-none">
                        <span id="cm-preview-icon" style="font-size:1.75rem;"></span>
                        <div style="flex:1;min-width:0;">
                            <div id="cm-preview-nombre" style="font-weight:600;color:#3AB397;font-size:0.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                            <small id="cm-preview-meta" style="color:#64748b;font-size:0.72rem;"></small>
                        </div>
                        <button onclick="limpiarSeleccion()" title="Deseleccionar"
                                style="width:28px;height:28px;border-radius:7px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-times" style="font-size:0.75rem;"></i>
                        </button>
                    </div>
                </div>

                {{-- Paso 2: Destino --}}
                <div class="modulo-section">
                    <label style="font-size:0.72rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.08em;display:flex;align-items:center;gap:0.4rem;margin-bottom:0.6rem;">
                        <span style="width:18px;height:18px;border-radius:50%;background:rgba(52,211,153,0.15);border:1px solid rgba(52,211,153,0.3);display:inline-flex;align-items:center;justify-content:center;font-size:0.65rem;color:#34d399;">2</span>
                        Carpeta destino
                    </label>
                    <select class="form-control" id="cm-destino"
                            style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:10px!important;color:#f1f5f9!important;font-size:0.83rem;height:40px;">
                        <option value="">📁 Almacenamiento (Raíz)</option>
                        @foreach($todasLasCarpetas as $c)
                            @php $indent = str_repeat('&nbsp;', $c['level'] * 4); @endphp
                            <option value="{{ $c['name'] }}">{!! $indent !!}{{ $c['level'] > 0 ? '└─ ' : '' }}{{ basename($c['name']) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Paso 3: Acción --}}
                <div class="modulo-section">
                    <label style="font-size:0.72rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.08em;display:flex;align-items:center;gap:0.4rem;margin-bottom:0.5rem;">
                        <span style="width:18px;height:18px;border-radius:50%;background:rgba(234,179,8,0.15);border:1px solid rgba(234,179,8,0.3);display:inline-flex;align-items:center;justify-content:center;font-size:0.65rem;color:#fbbf24;">3</span>
                        Acción
                    </label>
                    <div class="d-flex" style="gap:0.5rem;">
                        <label style="display:flex;align-items:flex-start;gap:0.5rem;flex:1;padding:0.6rem;border-radius:10px;border:1px solid rgba(255,255,255,0.06);cursor:pointer;transition:all 0.2s;background:rgba(255,255,255,0.02);">
                            <input type="radio" name="accion_cm" id="accion_copiar" value="copiar" checked style="margin-top:2px;accent-color:#3AB397;">
                            <div>
                                <div style="font-size:0.83rem;font-weight:600;color:#94a3b8;"><i class="fas fa-copy" style="color:#67e8f9;margin-right:0.3rem;"></i> Copiar</div>
                                <div style="font-size:0.7rem;color:#475569;">El original permanece</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:flex-start;gap:0.5rem;flex:1;padding:0.6rem;border-radius:10px;border:1px solid rgba(255,255,255,0.06);cursor:pointer;transition:all 0.2s;background:rgba(255,255,255,0.02);">
                            <input type="radio" name="accion_cm" id="accion_mover" value="mover" style="margin-top:2px;accent-color:#fbbf24;">
                            <div>
                                <div style="font-size:0.83rem;font-weight:600;color:#94a3b8;"><i class="fas fa-arrows-alt" style="color:#fbbf24;margin-right:0.3rem;"></i> Mover</div>
                                <div style="font-size:0.7rem;color:#475569;">El original se elimina</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center" style="gap:0.75rem;">
                    <a href="{{ route('dashboard') }}"
                       style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;border-radius:9px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;font-size:0.82rem;text-decoration:none;"
                       onmouseover="this.style.color='#f1f5f9'" onmouseout="this.style.color='#94a3b8'">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button id="btnEjecutarCM" disabled
                            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.4rem;border-radius:9px;background:linear-gradient(135deg,#3AB397,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.85rem;cursor:pointer;opacity:0.4;transition:all 0.2s;">
                        <i class="fas fa-play"></i> Ejecutar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /modulo-page --}}

<script>
let cmRutaActual = '';
let cmSeleccionado = null;

function cargarCM(carpeta) {
    cmRutaActual = carpeta;
    updateBreadcrumbCM(carpeta);
    const contenedor = document.getElementById('cm-explorador');
    contenedor.innerHTML = '<div class="text-center p-3" style="color:#475569;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

    fetch(`/explorador/datos?carpeta=${encodeURIComponent(carpeta)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        let html = '';
        if (carpeta) {
            const padre = carpeta.includes('/') ? carpeta.substring(0, carpeta.lastIndexOf('/')) : '';
            html += `<div class="cm-item" onclick="cargarCM('${padre}')">
                        <span class="cm-icon" style="color:#475569;"><i class="fas fa-level-up-alt"></i></span>
                        <div class="cm-info"><div class="cm-name" style="color:#475569;">.. (subir nivel)</div></div>
                     </div>`;
        }
        if (data.carpetas.length === 0 && data.archivos.length === 0 && !carpeta) {
            html += '<div class="text-center p-3" style="color:#374151;font-size:0.82rem;"><i class="fas fa-folder-open"></i> Carpeta vacía</div>';
        }
        data.carpetas.forEach(item => {
            const isSelected = cmSeleccionado && cmSeleccionado.ruta === item.ruta;
            html += `<div class="cm-item ${isSelected ? 'selected' : ''}"
                          onclick="event.stopPropagation(); seleccionarCM('${item.ruta}', '${escaparJS(item.nombre)}', 'carpeta', '${item.size}', '${item.fecha}')"
                          ondblclick="cargarCM('${item.ruta}')">
                        <span class="cm-icon" style="color:#3AB397;"><i class="fas fa-folder"></i></span>
                        <div class="cm-info">
                            <div class="cm-name">${item.nombre}</div>
                            <div class="cm-meta"><i class="fas fa-layer-group"></i> ${item.items} · ${item.size}</div>
                        </div>
                        <i class="fas fa-check-circle cm-check"></i>
                     </div>`;
        });
        data.archivos.forEach(item => {
            const isSelected = cmSeleccionado && cmSeleccionado.ruta === item.ruta;
            html += `<div class="cm-item ${isSelected ? 'selected' : ''}"
                          onclick="seleccionarCM('${item.ruta}', '${escaparJS(item.nombre)}', 'archivo', '${item.size}', '${item.fecha}')">
                        <span class="cm-icon" style="color:#64748b;"><i class="fas ${item.icono}"></i></span>
                        <div class="cm-info">
                            <div class="cm-name">${item.nombre}</div>
                            <div class="cm-meta">${item.size} · ${item.fecha}</div>
                        </div>
                        <i class="fas fa-check-circle cm-check"></i>
                     </div>`;
        });
        contenedor.innerHTML = html;
    })
    .catch(() => {
        contenedor.innerHTML = '<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem;font-size:0.82rem;color:#f87171;">Error al cargar.</div>';
    });
}

function updateBreadcrumbCM(ruta) {
    const bc = document.getElementById('cm-breadcrumb');
    let html = `<span class="cm-bc-item" onclick="cargarCM('')"><i class="fas fa-home" style="font-size:0.8rem;"></i> Raíz</span>`;
    if (ruta) {
        const segmentos = ruta.split('/');
        let acum = '';
        segmentos.forEach((seg, i) => {
            acum += (acum ? '/' : '') + seg;
            html += `<span class="cm-bc-sep"><i class="fas fa-chevron-right" style="font-size:0.65rem;"></i></span>`;
            const r = acum;
            if (i === segmentos.length - 1) {
                html += `<span style="font-weight:600;color:#f1f5f9;font-size:0.78rem;"><i class="fas fa-folder" style="color:#3AB397;"></i> ${seg}</span>`;
            } else {
                html += `<span class="cm-bc-item" onclick="cargarCM('${r}')"><i class="fas fa-folder" style="color:#3AB397;"></i> ${seg}</span>`;
            }
        });
    }
    bc.innerHTML = html;
}

function seleccionarCM(ruta, nombre, tipo, size, fecha) {
    cmSeleccionado = { ruta, nombre, tipo, size, fecha };
    document.getElementById('cm-preview-vacio').classList.add('d-none');
    document.getElementById('cm-preview').classList.remove('d-none');
    const icon = tipo === 'carpeta' ? '<i class="fas fa-folder" style="color:#3AB397;"></i>' : '<i class="fas fa-file" style="color:#67e8f9;"></i>';
    document.getElementById('cm-preview-icon').innerHTML = icon;
    document.getElementById('cm-preview-nombre').textContent = nombre;
    document.getElementById('cm-preview-meta').textContent = (tipo === 'carpeta' ? 'Carpeta' : 'Archivo') + ' · ' + size + ' · ' + fecha;
    const btn = document.getElementById('btnEjecutarCM');
    btn.disabled = false;
    btn.style.opacity = '1';
    document.querySelectorAll('.cm-item').forEach(el => el.classList.remove('selected'));
    if(event && event.currentTarget) event.currentTarget.classList.add('selected');
}

function limpiarSeleccion() {
    cmSeleccionado = null;
    document.getElementById('cm-preview-vacio').classList.remove('d-none');
    document.getElementById('cm-preview').classList.add('d-none');
    const btn = document.getElementById('btnEjecutarCM');
    btn.disabled = true;
    btn.style.opacity = '0.4';
    document.querySelectorAll('.cm-item').forEach(el => el.classList.remove('selected'));
}

function escaparJS(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
}

document.getElementById('btnEjecutarCM').addEventListener('click', function() {
    if (!cmSeleccionado) return;
    const destino = document.getElementById('cm-destino').value;
    const accion  = document.querySelector('input[name="accion_cm"]:checked').value;
    const btn     = $(this);

    if (cmSeleccionado.ruta === destino || (destino && destino.startsWith(cmSeleccionado.ruta + '/'))) {
        $('#alerta-cm').html('<div style="background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#fbbf24;"><i class="fas fa-exclamation-triangle mr-2"></i>El destino no puede ser el mismo elemento o una subcarpeta de él.</div>');
        return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    const url = accion === 'copiar' ? '{{ route("explorador.copiar") }}' : '{{ route("explorador.mover") }}';

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ ruta_origen: cmSeleccionado.ruta, carpeta_destino: destino })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#alerta-cm').html('<div style="background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#34d399;"><i class="fas fa-check-circle mr-2"></i>' + data.message + '</div>');
            limpiarSeleccion();
            cargarCM(cmRutaActual);
        } else {
            $('#alerta-cm').html('<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#f87171;"><i class="fas fa-times-circle mr-2"></i>' + data.message + '</div>');
        }
        btn.prop('disabled', false).css('opacity','1').html('<i class="fas fa-play"></i> Ejecutar');
    })
    .catch(() => {
        $('#alerta-cm').html('<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;font-size:0.82rem;color:#f87171;"><i class="fas fa-times-circle mr-2"></i>Error de conexión.</div>');
        btn.prop('disabled', false).css('opacity','1').html('<i class="fas fa-play"></i> Ejecutar');
    });
});

document.addEventListener('DOMContentLoaded', function() { cargarCM(''); });
</script>
