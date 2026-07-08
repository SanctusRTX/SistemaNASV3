{{-- Módulo: Gestión de Usuarios — Nexus OS Style --}}

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap" style="gap:0.75rem;">
    <div class="d-flex align-items-center" style="gap:0.75rem;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(192,132,252,0.12);border:1px solid rgba(192,132,252,0.25);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-users" style="color:#c084fc;font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0" style="font-weight:700;font-size:1rem;color:#f1f5f9;">Gestión de Usuarios</h5>
            <p class="mb-0" style="font-size:0.72rem;color:#64748b;">Administra cuentas y permisos del sistema</p>
        </div>
    </div>
    <button data-toggle="modal" data-target="#modalCrearUsuario"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.1rem;border-radius:9px;background:linear-gradient(135deg,#3AB397,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.8rem;cursor:pointer;box-shadow:0 4px 14px rgba(58,179,151,0.2);">
        <i class="fas fa-user-plus"></i> Nuevo Usuario
    </button>
</div>

{{-- Tabla de usuarios --}}
<div style="background:rgba(15,19,28,0.8);border:1px solid rgba(255,255,255,0.06);border-radius:16px;overflow:hidden;backdrop-filter:blur(10px);">
    <div style="padding:0.75rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.04);display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-list" style="color:#475569;font-size:0.75rem;"></i>
        <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#475569;">{{ count($usuarios) }} usuario(s) registrado(s)</span>
    </div>
    <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <th style="padding:0.75rem 1.25rem;font-size:0.7rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">#</th>
                    <th style="padding:0.75rem 1.25rem;font-size:0.7rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Usuario</th>
                    <th style="padding:0.75rem 1.25rem;font-size:0.7rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Rol</th>
                    <th style="padding:0.75rem 1.25rem;font-size:0.7rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $user)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.03);transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:0.85rem 1.25rem;font-size:0.8rem;color:#475569;">{{ $user->id }}</td>
                    <td style="padding:0.85rem 1.25rem;">
                        <div class="d-flex align-items-center" style="gap:0.65rem;">
                            <div style="width:30px;height:30px;border-radius:50%;background:rgba(192,132,252,0.12);border:1px solid rgba(192,132,252,0.25);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-user" style="font-size:0.65rem;color:#c084fc;"></i>
                            </div>
                            <span style="font-size:0.85rem;font-weight:600;color:#e2e8f0;">{{ $user->username }}</span>
                        </div>
                    </td>
                    <td style="padding:0.85rem 1.25rem;">
                        @if($user->rol_id == 1)
                            <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.65rem;border-radius:6px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#f87171;font-size:0.72rem;font-weight:600;">
                                <i class="fas fa-user-shield"></i> Administrador
                            </span>
                        @elseif($user->rol_id == 2)
                            <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.65rem;border-radius:6px;background:rgba(96,165,250,0.12);border:1px solid rgba(96,165,250,0.25);color:#60a5fa;font-size:0.72rem;font-weight:600;">
                                <i class="fas fa-user-tie"></i> Supervisor
                            </span>
                        @elseif($user->rol_id == 3)
                            <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.65rem;border-radius:6px;background:rgba(103,232,249,0.12);border:1px solid rgba(103,232,249,0.25);color:#67e8f9;font-size:0.72rem;font-weight:600;">
                                <i class="fas fa-user"></i> Secretario
                            </span>
                        @else
                            <span style="padding:0.2rem 0.65rem;border-radius:6px;background:rgba(100,116,139,0.15);color:#64748b;font-size:0.72rem;">Desconocido</span>
                        @endif
                    </td>
                    <td style="padding:0.85rem 1.25rem;text-align:center;">
                        <div class="d-flex justify-content-center" style="gap:0.4rem;">
                            <button class="action-btn" title="Editar usuario"
                                onclick="abrirModalEditarUsuario({{ $user->id }}, '{{ $user->username }}', {{ $user->rol_id }})">
                                <i class="fas fa-edit" style="font-size:0.75rem;color:#60a5fa;"></i>
                            </button>
                            @if(session('user_id') != $user->id)
                            <button class="action-btn" title="Eliminar usuario"
                                style="border-color:rgba(239,68,68,0.3);"
                                onclick="confirmarEliminarUsuario({{ $user->id }}, '{{ $user->username }}')">
                                <i class="fas fa-trash" style="font-size:0.75rem;color:#ef4444;"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @if(count($usuarios) == 0)
                <tr>
                    <td colspan="4" style="padding:3rem;text-align:center;color:#374151;">
                        <i class="fas fa-users" style="font-size:2.5rem;display:block;margin-bottom:0.75rem;color:#1e293b;"></i>
                        <p style="font-size:0.85rem;">No hay usuarios registrados.</p>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL CREAR USUARIO --}}
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(255,255,255,0.07);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <i class="fas fa-user-plus" style="color:#3AB397;"></i>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Crear Usuario</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Nombre de Usuario</label>
                    <input type="text" class="form-control mt-1" id="nuevo_username" maxlength="32" placeholder="Ej: jperez"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Rol</label>
                    <select class="form-control mt-1" id="nuevo_rol"
                            style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        <option value="1">Administrador</option>
                        <option value="2">Supervisor</option>
                        <option value="3" selected>Secretario</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Contraseña</label>
                    <input type="password" class="form-control mt-1" id="nuevo_password" maxlength="12" placeholder="Máx. 12 caracteres"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnGuardarNuevoUsuario"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.45rem 1.1rem;border-radius:8px;background:linear-gradient(135deg,#3AB397,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDITAR USUARIO --}}
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(255,255,255,0.07);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <i class="fas fa-user-edit" style="color:#60a5fa;"></i>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Editar Usuario</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <input type="hidden" id="edit_user_id">
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Nombre de Usuario</label>
                    <input type="text" class="form-control mt-1" id="edit_username" maxlength="32"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
                <div class="form-group mb-3">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Rol</label>
                    <select class="form-control mt-1" id="edit_rol"
                            style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                        <option value="1">Administrador</option>
                        <option value="2">Supervisor</option>
                        <option value="3">Secretario</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">
                        Contraseña <span style="text-transform:none;font-weight:400;color:#475569;">(vacío = sin cambio)</span>
                    </label>
                    <input type="password" class="form-control mt-1" id="edit_password" maxlength="12" placeholder="Nueva contraseña"
                           style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:9px!important;color:#f1f5f9!important;height:40px;">
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnGuardarEdicionUsuario"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.45rem 1.1rem;border-radius:8px;background:rgba(96,165,250,0.15);border:1px solid rgba(96,165,250,0.3);color:#60a5fa;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-save"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ---------- CREAR ----------
$('#btnGuardarNuevoUsuario').click(function() {
    const username = document.getElementById('nuevo_username').value.trim();
    const rol = document.getElementById('nuevo_rol').value;
    const password = document.getElementById('nuevo_password').value;
    if(!username || !password) return alert('Usuario y contraseña son requeridos.');
    fetch('/usuarios/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ username, rol_id: rol, password })
    })
    .then(r => r.json())
    .then(data => { if(data.success) location.reload(); else alert('Error: ' + (data.message || 'Verifica que el usuario no exista.')); })
    .catch(() => alert('Error en la petición.'));
});

// ---------- EDITAR ----------
function abrirModalEditarUsuario(id, username, rol) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_rol').value = rol;
    document.getElementById('edit_password').value = '';
    $('#modalEditarUsuario').modal('show');
}

$('#btnGuardarEdicionUsuario').click(function() {
    const id = document.getElementById('edit_user_id').value;
    const username = document.getElementById('edit_username').value.trim();
    const rol = document.getElementById('edit_rol').value;
    const password = document.getElementById('edit_password').value;
    if(!username) return alert('El nombre de usuario es requerido.');
    fetch('/usuarios/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ username, rol_id: rol, password })
    })
    .then(r => r.json())
    .then(data => { if(data.success) location.reload(); else alert('Error: ' + (data.message || 'Verifica que el usuario no exista.')); })
    .catch(() => alert('Error en la petición.'));
});

// ---------- ELIMINAR ----------
function confirmarEliminarUsuario(id, username) {
    if(confirm('¿Eliminar permanentemente al usuario "' + username + '"?')) {
        fetch('/usuarios/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else alert('Error: ' + data.message); });
    }
}
</script>
