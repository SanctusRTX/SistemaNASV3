{{-- Módulo: Servidores Externos — Nexus OS Style --}}
<div class="modulo-page modulo-servidores">

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between modulo-header flex-wrap" style="gap:0.75rem;">
    <div class="d-flex align-items-center" style="gap:0.75rem;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(103,232,249,0.12);border:1px solid rgba(103,232,249,0.25);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-network-wired" style="color:#67e8f9;font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0" style="font-weight:700;font-size:1rem;color:#f1f5f9;">Servidores Externos</h5>
            <p class="mb-0" style="font-size:0.72rem;color:#64748b;">Gestiona tus conexiones FTP / SFTP remotas</p>
        </div>
    </div>
    @if(session('rol') === 'administrador')
    <button data-toggle="modal" data-target="#modalCrearServidor"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.1rem;border-radius:9px;background:rgba(103,232,249,0.1);border:1px solid rgba(103,232,249,0.25);color:#67e8f9;font-weight:700;font-size:0.8rem;cursor:pointer;transition:all 0.2s;"
            onmouseover="this.style.background='rgba(103,232,249,0.18)'"
            onmouseout="this.style.background='rgba(103,232,249,0.1)'">
        <i class="fas fa-plus"></i> Agregar Servidor
    </button>
    @endif
</div>

{{-- Grid de servidores --}}
<div class="row modulo-grid">
    @foreach($servidores as $srv)
    <div class="col-md-4 col-lg-4">
        <div class="srv-card">

            {{-- Top bar --}}
            <div style="height:3px;border-radius:3px;background:linear-gradient(90deg,#67e8f9,#3AB397);margin-bottom:0.85rem;opacity:0.6;"></div>

            <div class="d-flex align-items-center mb-2" style="gap:0.75rem;">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(103,232,249,0.1);border:1px solid rgba(103,232,249,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-server" style="color:#67e8f9;font-size:1rem;"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.9rem;font-weight:700;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $srv->nombre }}</div>
                    <div style="font-size:0.7rem;color:#64748b;margin-top:1px;">
                        <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.15rem 0.5rem;border-radius:5px;background:rgba(103,232,249,0.08);color:#67e8f9;font-weight:600;">
                            {{ strtoupper($srv->protocolo) }}
                        </span>
                    </div>
                </div>
            </div>

            <div style="font-size:0.78rem;color:#64748b;margin-bottom:0.75rem;padding:0.45rem 0.65rem;background:rgba(255,255,255,0.02);border-radius:8px;font-family:monospace;">
                <i class="fas fa-globe" style="color:#475569;margin-right:0.4rem;"></i>{{ $srv->ip }}:{{ $srv->puerto }}
            </div>

            <div class="d-flex justify-content-between align-items-center mt-auto" style="gap:0.5rem;">
                <a href="/remoto/{{ $srv->id }}"
                   style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 1rem;border-radius:8px;background:linear-gradient(135deg,#3AB397,#10b981);color:#07090e;font-weight:700;font-size:0.8rem;text-decoration:none;transition:all 0.2s;box-shadow:0 4px 12px rgba(58,179,151,0.2);">
                    <i class="fas fa-plug"></i> Conectar
                </a>
                @if(session('rol') === 'administrador')
                <div class="d-flex" style="gap:0.35rem;">
                    <button class="action-btn" title="Editar servidor"
                        onclick="editarServidor({{ $srv->id }}, '{{ $srv->nombre }}', '{{ $srv->ip }}', {{ $srv->puerto }}, '{{ $srv->usuario }}', '{{ $srv->protocolo }}')">
                        <i class="fas fa-edit" style="font-size:0.75rem;color:#60a5fa;"></i>
                    </button>
                    <button class="action-btn" title="Eliminar servidor" style="border-color:rgba(239,68,68,0.3);"
                        onclick="eliminarServidor({{ $srv->id }}, '{{ $srv->nombre }}')">
                        <i class="fas fa-trash" style="font-size:0.75rem;color:#ef4444;"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    @if(count($servidores) == 0)
    <div class="col-12 text-center py-5" style="color:#374151;">
        <i class="fas fa-network-wired" style="font-size:3rem;display:block;margin-bottom:1rem;color:#1e293b;"></i>
        <p style="font-size:0.9rem;font-weight:500;">No hay servidores externos configurados</p>
        @if(session('rol') === 'administrador')
        <p style="font-size:0.8rem;color:#374151;">Haz clic en <strong style="color:#67e8f9;">Agregar Servidor</strong> para comenzar.</p>
        @endif
    </div>
    @endif
</div>

</div>{{-- /modulo-page --}}

@if(session('rol') === 'administrador')
{{-- MODAL CREAR SERVIDOR --}}
<div class="modal fade" id="modalCrearServidor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(255,255,255,0.07);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <i class="fas fa-plus" style="color:#3AB397;"></i>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Agregar Servidor Remoto</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                @foreach([
                    ['id'=>'nuevo_srv_nombre','label'=>'Nombre identificador','placeholder'=>'Ej: Servidor Principal','type'=>'text'],
                ] as $field)
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">{{ $field['label'] }}</label>
                    <input type="{{ $field['type'] }}" class="form-control mt-1" id="{{ $field['id'] }}" placeholder="{{ $field['placeholder'] }}"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
                @endforeach

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Dirección IP o Host</label>
                            <input type="text" class="form-control mt-1" id="nuevo_srv_ip"
                                   style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Puerto</label>
                            <input type="number" class="form-control mt-1" id="nuevo_srv_puerto" value="21"
                                   style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Protocolo</label>
                    <select class="form-control mt-1" id="nuevo_srv_protocolo"
                            onchange="document.getElementById('nuevo_srv_puerto').value = this.value === 'sftp' ? 22 : 21;"
                            style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        <option value="ftp">FTP</option>
                        <option value="sftp">SFTP</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">
                        Usuario <span style="text-transform:none;font-weight:400;color:#475569;">(Opcional)</span>
                    </label>
                    <input type="text" class="form-control mt-1" id="nuevo_srv_usuario"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">
                        Contraseña <span style="text-transform:none;font-weight:400;color:#475569;">(Opcional)</span>
                    </label>
                    <input type="password" class="form-control mt-1" id="nuevo_srv_password"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnGuardarServidor"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.45rem 1.1rem;border-radius:8px;background:linear-gradient(135deg,#3AB397,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDITAR SERVIDOR --}}
<div class="modal fade" id="modalEditarServidor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(255,255,255,0.07);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <i class="fas fa-edit" style="color:#60a5fa;"></i>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Editar Servidor</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <input type="hidden" id="edit_srv_id">
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Nombre identificador</label>
                    <input type="text" class="form-control mt-1" id="edit_srv_nombre" maxlength="100"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">IP o Host</label>
                            <input type="text" class="form-control mt-1" id="edit_srv_ip"
                                   style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Puerto</label>
                            <input type="number" class="form-control mt-1" id="edit_srv_puerto"
                                   style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Protocolo</label>
                    <select class="form-control mt-1" id="edit_srv_protocolo"
                            style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        <option value="ftp">FTP</option>
                        <option value="sftp">SFTP</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Usuario</label>
                    <input type="text" class="form-control mt-1" id="edit_srv_usuario"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">
                        Contraseña <span style="text-transform:none;font-weight:400;color:#475569;">(vacío = sin cambio)</span>
                    </label>
                    <input type="password" class="form-control mt-1" id="edit_srv_password"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnActualizarServidor"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.45rem 1.1rem;border-radius:8px;background:rgba(96,165,250,0.15);border:1px solid rgba(96,165,250,0.3);color:#60a5fa;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-save"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnGuardarServidor').addEventListener('click', function() {
    const data = {
        nombre: document.getElementById('nuevo_srv_nombre').value,
        ip: document.getElementById('nuevo_srv_ip').value,
        puerto: document.getElementById('nuevo_srv_puerto').value,
        protocolo: document.getElementById('nuevo_srv_protocolo').value,
        usuario: document.getElementById('nuevo_srv_usuario').value,
        password: document.getElementById('nuevo_srv_password').value
    };
    if(!data.nombre || !data.ip) return alert('El nombre y la IP son requeridos');
    fetch('/servidores/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => { if(res.success) location.reload(); else alert('Error: ' + res.message); });
});

function editarServidor(id, nombre, ip, puerto, usuario, protocolo) {
    document.getElementById('edit_srv_id').value = id;
    document.getElementById('edit_srv_nombre').value = nombre;
    document.getElementById('edit_srv_ip').value = ip;
    document.getElementById('edit_srv_puerto').value = puerto;
    document.getElementById('edit_srv_usuario').value = usuario;
    document.getElementById('edit_srv_protocolo').value = protocolo;
    document.getElementById('edit_srv_password').value = '';
    $('#modalEditarServidor').modal('show');
}

document.getElementById('btnActualizarServidor').addEventListener('click', function() {
    const id = document.getElementById('edit_srv_id').value;
    const data = {
        nombre: document.getElementById('edit_srv_nombre').value,
        ip: document.getElementById('edit_srv_ip').value,
        puerto: document.getElementById('edit_srv_puerto').value,
        protocolo: document.getElementById('edit_srv_protocolo').value,
        usuario: document.getElementById('edit_srv_usuario').value,
        password: document.getElementById('edit_srv_password').value
    };
    fetch('/servidores/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => { if(res.success) location.reload(); else alert('Error: ' + res.message); });
});

function eliminarServidor(id, nombre) {
    if(confirm('¿Eliminar servidor "' + nombre + '"?')) {
        fetch('/servidores/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => { if(res.success) location.reload(); else alert('Error: ' + res.message); });
    }
}
</script>
@endif
