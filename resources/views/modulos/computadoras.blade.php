{{-- ========================= HEADER ========================= --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap" style="gap:0.75rem;">
    <div class="d-flex align-items-center" style="gap:0.75rem;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(103,232,249,0.1);border:1px solid rgba(103,232,249,0.2);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-desktop" style="color:#67e8f9;font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0" style="font-weight:700;font-size:1rem;color:#f1f5f9;">Gestión de Computadoras</h5>
            <p class="mb-0" style="font-size:0.72rem;color:#64748b;">Inventario y registro de equipos del sistema</p>
        </div>
    </div>
    @if(session('rol') === 'administrador' || session('rol') === 'supervisor')
    <button data-toggle="modal" data-target="#modalCrearComputadora"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.1rem;border-radius:9px;background:linear-gradient(135deg,#3AB397,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.82rem;cursor:pointer;box-shadow:0 4px 14px rgba(58,179,151,0.25);">
        <i class="fas fa-plus"></i> Nueva Computadora
    </button>
    @endif
</div>

{{-- ========================= FILTROS ========================= --}}
<div style="background:rgba(15,19,28,0.8);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:0.85rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;flex-wrap:wrap;gap:0.75rem;">
    <div style="flex:1;min-width:200px;position:relative;">
        <i class="fas fa-search" style="position:absolute;left:0.65rem;top:50%;transform:translateY(-50%);color:#475569;font-size:0.75rem;"></i>
        <input type="text" id="filtroComputadora"
               placeholder="Buscar por nombre, operador, depto..."
               style="width:100%;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:8px;color:#f1f5f9;font-size:0.8rem;padding:0.4rem 0.75rem 0.4rem 2rem;outline:none;">
    </div>
    <select id="filtroDepartamento"
            style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:8px;color:#94a3b8;font-size:0.8rem;padding:0.4rem 0.75rem;outline:none;min-width:180px;">
        <option value="">— Todos los departamentos —</option>
        @foreach($computadoras->pluck('departamento')->unique()->sort() as $dep)
            <option value="{{ $dep }}">{{ $dep }}</option>
        @endforeach
    </select>
    <select id="filtroEstado"
            style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:8px;color:#94a3b8;font-size:0.8rem;padding:0.4rem 0.75rem;outline:none;min-width:150px;">
        <option value="">— Todos los estados —</option>
        <option value="Activo">Activo</option>
        <option value="En reparación">En reparación</option>
        <option value="Dado de baja">Dado de baja</option>
    </select>
    <span id="contadorRegistros" style="font-size:0.72rem;color:#475569;margin-left:auto;white-space:nowrap;">
        <i class="fas fa-database" style="margin-right:0.3rem;"></i>{{ $computadoras->count() }} registros
    </span>
</div>

{{-- ========================= TABLA ========================= --}}
<div style="background:rgba(15,19,28,0.8);border:1px solid rgba(255,255,255,0.06);border-radius:16px;overflow:hidden;">
    <div style="padding:0.65rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.04);">
        <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#475569;">
            <i class="fas fa-desktop" style="margin-right:0.4rem;color:#67e8f9;"></i>Equipos Registrados
        </span>
    </div>
    <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;" id="tablaComputadoras">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;width:40px;">#</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Nombre / Código</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Tipo</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Marca / Modelo</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Operador</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Departamento</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">IP / MAC</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Estado</th>
                    <th style="padding:0.65rem 1rem;font-size:0.68rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#475569;text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyComputadoras">
                @forelse($computadoras as $pc)
                <tr class="fila-computadora"
                    data-nombre="{{ strtolower($pc->nombre) }}"
                    data-operador="{{ strtolower($pc->operador) }}"
                    data-departamento="{{ $pc->departamento }}"
                    data-estado="{{ $pc->estado }}"
                    style="border-bottom:1px solid rgba(255,255,255,0.03);transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:0.8rem 1rem;font-size:0.75rem;color:#475569;">{{ $pc->id }}</td>
                    <td style="padding:0.8rem 1rem;">
                        <div style="font-size:0.83rem;font-weight:600;color:#e2e8f0;">{{ $pc->nombre }}</div>
                        @if($pc->numero_serie)
                            <div style="font-size:0.68rem;color:#475569;margin-top:1px;"><i class="fas fa-barcode" style="margin-right:0.25rem;"></i>{{ $pc->numero_serie }}</div>
                        @endif
                    </td>
                    <td style="padding:0.8rem 1rem;">
                        @php
                            $iconoTipo = match($pc->tipo) {
                                'Laptop'      => 'fa-laptop',
                                'All-in-One'  => 'fa-desktop',
                                'Servidor'    => 'fa-server',
                                'Workstation' => 'fa-microchip',
                                default       => 'fa-desktop',
                            };
                        @endphp
                        <span style="font-size:0.8rem;color:#94a3b8;">
                            <i class="fas {{ $iconoTipo }}" style="color:#67e8f9;margin-right:0.3rem;"></i>{{ $pc->tipo }}
                        </span>
                    </td>
                    <td style="padding:0.8rem 1rem;">
                        <div style="font-size:0.8rem;color:#cbd5e1;">{{ $pc->marca ?? '—' }}</div>
                        @if($pc->modelo)<div style="font-size:0.68rem;color:#475569;">{{ $pc->modelo }}</div>@endif
                    </td>
                    <td style="padding:0.8rem 1rem;">
                        <div style="font-size:0.8rem;color:#cbd5e1;"><i class="fas fa-user" style="color:#a78bfa;margin-right:0.3rem;font-size:0.7rem;"></i>{{ $pc->operador }}</div>
                        @if($pc->cargo_operador)<div style="font-size:0.68rem;color:#475569;">{{ $pc->cargo_operador }}</div>@endif
                    </td>
                    <td style="padding:0.8rem 1rem;">
                        <span style="font-size:0.8rem;color:#94a3b8;">
                            <i class="fas fa-building" style="color:#fbbf24;margin-right:0.3rem;font-size:0.7rem;"></i>{{ $pc->departamento }}
                        </span>
                    </td>
                    <td style="padding:0.8rem 1rem;font-size:0.72rem;">
                        @if($pc->direccion_ip)
                            <div style="color:#67e8f9;"><i class="fas fa-network-wired" style="margin-right:0.25rem;"></i>{{ $pc->direccion_ip }}</div>
                        @endif
                        @if($pc->direccion_mac)
                            <div style="color:#64748b;"><i class="fas fa-ethernet" style="margin-right:0.25rem;"></i>{{ $pc->direccion_mac }}</div>
                        @endif
                        @if(!$pc->direccion_ip && !$pc->direccion_mac)
                            <span style="color:#374151;">—</span>
                        @endif
                    </td>
                    <td style="padding:0.8rem 1rem;">
                        @if($pc->estado === 'Activo')
                            <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.7rem;font-weight:600;background:rgba(52,211,153,0.1);color:#34d399;border:1px solid rgba(52,211,153,0.2);">
                                <i class="fas fa-check-circle"></i> Activo
                            </span>
                        @elseif($pc->estado === 'En reparación')
                            <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.7rem;font-weight:600;background:rgba(251,191,36,0.1);color:#fbbf24;border:1px solid rgba(251,191,36,0.2);">
                                <i class="fas fa-tools"></i> En reparación
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.7rem;font-weight:600;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);">
                                <i class="fas fa-times-circle"></i> Dado de baja
                            </span>
                        @endif
                    </td>
                    <td style="padding:0.8rem 1rem;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:0.3rem;">
                            <button onclick="verDetallesComputadora({{ $pc->id }})" title="Ver detalles"
                                    class="action-btn" style="color:#67e8f9;">
                                <i class="fas fa-eye" style="font-size:0.75rem;"></i>
                            </button>
                            @if(session('rol') === 'administrador' || session('rol') === 'supervisor')
                            <button onclick="abrirModalEditarComputadora({{ $pc->id }})" title="Editar"
                                    class="action-btn" style="color:#60a5fa;">
                                <i class="fas fa-edit" style="font-size:0.75rem;"></i>
                            </button>
                            @endif
                            @if(session('rol') === 'administrador')
                            <button onclick="confirmarEliminarComputadora({{ $pc->id }}, '{{ addslashes($pc->nombre) }}')" title="Eliminar"
                                    class="action-btn" style="color:#ef4444;border-color:rgba(239,68,68,0.3);">
                                <i class="fas fa-trash" style="font-size:0.75rem;"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="filaVacia">
                    <td colspan="9" style="padding:3rem;text-align:center;color:#374151;">
                        <i class="fas fa-desktop" style="font-size:2.5rem;display:block;margin-bottom:0.75rem;color:#1e293b;"></i>
                        <div style="font-size:0.85rem;font-weight:500;">No hay computadoras registradas</div>
                        <div style="font-size:0.72rem;margin-top:0.25rem;color:#1e293b;">Usa el botón "Nueva Computadora" para agregar equipos</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ========================= JSON DATA ========================= --}}
<script>const _computadorasData = @json($computadoras->keyBy('id'));</script>

{{-- ========================= MODAL VER DETALLES ========================= --}}
<div class="modal fade" id="modalVerComputadora" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(103,232,249,0.15);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(103,232,249,0.1);border:1px solid rgba(103,232,249,0.2);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-desktop" style="color:#67e8f9;font-size:0.85rem;"></i>
                    </div>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Detalles del Equipo</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;font-size:1.2rem;">&times;</button>
            </div>
            <div class="modal-body" id="detallesComputadoraBody" style="padding:1.5rem;"></div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:0.85rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================= MODAL CREAR ========================= --}}
<div class="modal fade" id="modalCrearComputadora" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(58,179,151,0.2);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(58,179,151,0.12);border:1px solid rgba(58,179,151,0.25);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-plus-circle" style="color:#3AB397;font-size:0.85rem;"></i>
                    </div>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Registrar Computadora</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div id="alerta-crear-pc" class="mb-3"></div>
                @include('modulos.partials.computadora_form', ['prefijo' => 'nuevo'])
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:0.85rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                <button type="button" id="btnAutoDetectarPC"
                        style="display:inline-flex;align-items:center;gap:0.45rem;padding:0.45rem 1rem;border-radius:8px;background:rgba(103,232,249,0.08);border:1px solid rgba(103,232,249,0.25);color:#67e8f9;font-weight:600;font-size:0.8rem;cursor:pointer;transition:all 0.2s;"
                        onmouseover="this.style.background='rgba(103,232,249,0.15)'"
                        onmouseout="this.style.background='rgba(103,232,249,0.08)'"
                        title="Rellena automáticamente con los datos detectables de esta computadora">
                    <i class="fas fa-magic"></i> Detectar mi PC
                </button>
                <div style="display:flex;gap:0.5rem;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                    <button type="button" id="btnGuardarNuevaComputadora"
                            style="display:inline-flex;align-items:center;gap:0.45rem;padding:0.45rem 1.1rem;border-radius:8px;background:linear-gradient(135deg,#3AB397,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.82rem;cursor:pointer;">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================= MODAL EDITAR ========================= --}}
<div class="modal fade" id="modalEditarComputadora" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(96,165,250,0.2);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(96,165,250,0.1);border:1px solid rgba(96,165,250,0.25);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-edit" style="color:#60a5fa;font-size:0.85rem;"></i>
                    </div>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Editar Computadora</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div id="alerta-editar-pc" class="mb-3"></div>
                <input type="hidden" id="edit_computadora_id">
                @include('modulos.partials.computadora_form', ['prefijo' => 'edit'])
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:0.85rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnGuardarEdicionComputadora"
                        style="display:inline-flex;align-items:center;gap:0.45rem;padding:0.45rem 1.1rem;border-radius:8px;background:rgba(96,165,250,0.12);border:1px solid rgba(96,165,250,0.3);color:#60a5fa;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-save"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================= MODAL ELIMINAR ========================= --}}
<div class="modal fade" id="modalEliminarComputadora" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background:#0f131c;border:1px solid rgba(239,68,68,0.2);border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.06);padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:0.65rem;">
                    <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
                    <h5 class="modal-title mb-0" style="font-size:0.95rem;font-weight:700;color:#f1f5f9;">Eliminar Computadora</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#64748b;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:1rem;font-size:0.83rem;color:#f87171;">
                    <i class="fas fa-exclamation-circle" style="margin-right:0.4rem;"></i>
                    <strong>¿Eliminar el equipo "<span id="nombrePcEliminar"></span>"?</strong><br>
                    <span style="opacity:0.8;">Esta acción no se puede deshacer.</span>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:0.85rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:8px;font-size:0.82rem;">Cancelar</button>
                <button type="button" id="btnConfirmarEliminarPc"
                        style="display:inline-flex;align-items:center;gap:0.45rem;padding:0.45rem 1rem;border-radius:8px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;font-weight:700;font-size:0.82rem;cursor:pointer;">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================= JAVASCRIPT ========================= --}}
<script>
// ── Formato MAC ──────────────────────────────────────────────────────────────
function formatearMac(input) {
    let val = input.value.replace(/[^0-9A-Fa-f]/g, '').toUpperCase();
    val = val.match(/.{1,2}/g)?.join(':') ?? '';
    if (val.length > 17) val = val.substring(0, 17);
    input.value = val;
}

// ── Filtros ──────────────────────────────────────────────────────────────────
function aplicarFiltros() {
    const texto = document.getElementById('filtroComputadora').value.toLowerCase();
    const depto = document.getElementById('filtroDepartamento').value;
    const estado = document.getElementById('filtroEstado').value;
    let visibles = 0;
    document.querySelectorAll('.fila-computadora').forEach(fila => {
        const matchTexto = !texto || fila.dataset.nombre.includes(texto) || fila.dataset.operador.includes(texto) || fila.dataset.departamento.toLowerCase().includes(texto);
        const matchDep   = !depto  || fila.dataset.departamento === depto;
        const matchEst   = !estado || fila.dataset.estado === estado;
        const mostrar = matchTexto && matchDep && matchEst;
        fila.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });
    document.getElementById('contadorRegistros').innerHTML = '<i class="fas fa-database" style="margin-right:0.3rem;"></i>' + visibles + ' registros';
}
document.getElementById('filtroComputadora').addEventListener('input', aplicarFiltros);
document.getElementById('filtroDepartamento').addEventListener('change', aplicarFiltros);
document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);

// ── Ver detalles ─────────────────────────────────────────────────────────────
function verDetallesComputadora(id) {
    const pc = _computadorasData[id];
    if (!pc) return;

    const badgeEstado = pc.estado === 'Activo'
        ? '<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.75rem;font-weight:600;background:rgba(52,211,153,0.12);color:#34d399;border:1px solid rgba(52,211,153,0.25);"><i class="fas fa-check-circle"></i> Activo</span>'
        : pc.estado === 'En reparación'
            ? '<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.75rem;font-weight:600;background:rgba(251,191,36,0.12);color:#fbbf24;border:1px solid rgba(251,191,36,0.25);"><i class="fas fa-tools"></i> En reparación</span>'
            : '<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.75rem;font-weight:600;background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.25);"><i class="fas fa-times-circle"></i> Dado de baja</span>';

    const fila = (label, valor) => `
        <tr>
            <td style="padding:0.45rem 0.5rem;font-size:0.75rem;color:#475569;white-space:nowrap;font-weight:600;width:160px;">${label}</td>
            <td style="padding:0.45rem 0.5rem;font-size:0.8rem;color:#cbd5e1;">${valor ? valor : '<span style="color:#475569;font-weight:normal;">—</span>'}</td>
        </tr>`;

    const seccion = (titulo, color, icono, filas) => `
        <div style="margin-bottom:1.25rem;">
            <div style="font-size:0.68rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:${color};margin-bottom:0.5rem;display:flex;align-items:center;gap:0.4rem;padding-bottom:0.35rem;border-bottom:1px solid rgba(255,255,255,0.05);">
                <i class="fas ${icono}"></i> ${titulo}
            </div>
            <table style="width:100%;">${filas}</table>
        </div>`;

    document.getElementById('detallesComputadoraBody').innerHTML =
        '<div class="row">' +
        '<div class="col-md-6">' +
        seccion('Identificación', '#67e8f9', 'fa-desktop',
            fila('Nombre / Código', pc.nombre) +
            fila('N° de Serie', pc.numero_serie) +
            fila('Cód. Inventario', pc.codigo_inventario) +
            fila('Tipo', pc.tipo) +
            `<tr><td style="padding:0.45rem 0.5rem;font-size:0.75rem;color:#475569;font-weight:600;">Estado</td><td style="padding:0.45rem 0.5rem;">${badgeEstado}</td></tr>`
        ) +
        seccion('Red', '#67e8f9', 'fa-network-wired',
            fila('Dirección IP', pc.direccion_ip) +
            fila('Dirección MAC', pc.direccion_mac)
        ) +
        '</div>' +
        '<div class="col-md-6">' +
        seccion('Hardware', '#34d399', 'fa-microchip',
            fila('Marca', pc.marca) +
            fila('Modelo', pc.modelo) +
            fila('Procesador', pc.procesador) +
            fila('RAM', pc.ram) +
            fila('Almacenamiento', pc.almacenamiento) +
            fila('Tarjeta Gráfica', pc.tarjeta_grafica) +
            fila('Sistema Oper.', pc.sistema_operativo)
        ) +
        seccion('Operador', '#a78bfa', 'fa-user',
            fila('Nombre', pc.operador) +
            fila('Cargo', pc.cargo_operador) +
            fila('Departamento', pc.departamento)
        ) +
        '</div>' +
        '</div>' +
        (pc.observaciones ? `<div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:0.75rem 1rem;font-size:0.8rem;color:#94a3b8;"><i class="fas fa-sticky-note" style="color:#fbbf24;margin-right:0.4rem;"></i><strong style="color:#cbd5e1;">Observaciones:</strong><br>${pc.observaciones}</div>` : '');

    $('#modalVerComputadora').modal('show');
}

// ── Helpers formulario ───────────────────────────────────────────────────────
function leerFormulario(prefijo) {
    const v = id => document.getElementById(prefijo + '_' + id)?.value?.trim() || '';
    return {
        nombre: v('nombre'), numero_serie: v('numero_serie'), codigo_inventario: v('codigo_inventario'),
        marca: v('marca'), modelo: v('modelo'), tipo: v('tipo'), procesador: v('procesador'),
        ram: v('ram'), almacenamiento: v('almacenamiento'), tarjeta_grafica: v('tarjeta_grafica'),
        sistema_operativo: v('sistema_operativo'), operador: v('operador'), cargo_operador: v('cargo_operador'),
        departamento: v('departamento'), estado: v('estado'), direccion_ip: v('direccion_ip'),
        direccion_mac: v('direccion_mac'), observaciones: v('observaciones'),
    };
}
function escribirFormulario(prefijo, pc) {
    const set = (id, val) => { const el = document.getElementById(prefijo + '_' + id); if (el) el.value = val ?? ''; };
    ['nombre','numero_serie','codigo_inventario','marca','modelo','tipo','procesador','ram','almacenamiento',
     'tarjeta_grafica','sistema_operativo','operador','cargo_operador','departamento','direccion_ip',
     'direccion_mac','estado','observaciones'].forEach(k => set(k, pc[k]));
}

function mostrarAlerta(id, msg, tipo='error') {
    const color = tipo==='success' ? '#34d399' : '#f87171';
    const bg = tipo==='success' ? 'rgba(52,211,153,0.1)' : 'rgba(239,68,68,0.1)';
    const border = tipo==='success' ? 'rgba(52,211,153,0.3)' : 'rgba(239,68,68,0.3)';
    const icon = tipo==='success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    document.getElementById(id).innerHTML = `<div style="background:${bg};border:1px solid ${border};border-radius:9px;padding:0.6rem 0.9rem;font-size:0.8rem;color:${color};"><i class="fas ${icon}" style="margin-right:0.4rem;"></i>${msg}</div>`;
}

// ── Crear ────────────────────────────────────────────────────────────────────
document.getElementById('btnGuardarNuevaComputadora').addEventListener('click', function () {
    const datos = leerFormulario('nuevo');
    if (!datos.nombre || !datos.operador || !datos.departamento) {
        return mostrarAlerta('alerta-crear-pc', 'Los campos Nombre, Operador y Departamento son obligatorios.');
    }
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    fetch('/computadoras', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.href = '/dashboard?modulo=computadoras'; }
        else { mostrarAlerta('alerta-crear-pc', 'Error: ' + (data.message || 'No se pudo guardar.')); }
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
    })
    .catch(() => { mostrarAlerta('alerta-crear-pc', 'Error de conexión.'); btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar'); });
});

// ── Editar ───────────────────────────────────────────────────────────────────
function abrirModalEditarComputadora(id) {
    const pc = _computadorasData[id];
    if (!pc) return;
    document.getElementById('edit_computadora_id').value = id;
    document.getElementById('alerta-editar-pc').innerHTML = '';
    escribirFormulario('edit', pc);
    $('#modalEditarComputadora').modal('show');
}

document.getElementById('btnGuardarEdicionComputadora').addEventListener('click', function () {
    const id = document.getElementById('edit_computadora_id').value;
    const datos = leerFormulario('edit');
    if (!datos.nombre || !datos.operador || !datos.departamento) {
        return mostrarAlerta('alerta-editar-pc', 'Los campos Nombre, Operador y Departamento son obligatorios.');
    }
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

    fetch('/computadoras/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.href = '/dashboard?modulo=computadoras'; }
        else { mostrarAlerta('alerta-editar-pc', 'Error: ' + (data.message || 'No se pudo actualizar.')); }
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar');
    })
    .catch(() => { mostrarAlerta('alerta-editar-pc', 'Error de conexión.'); btn.prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar'); });
});

// ── Eliminar ─────────────────────────────────────────────────────────────────
let _pcIdEliminar = null;
function confirmarEliminarComputadora(id, nombre) {
    _pcIdEliminar = id;
    document.getElementById('nombrePcEliminar').textContent = nombre;
    $('#modalEliminarComputadora').modal('show');
}
document.getElementById('btnConfirmarEliminarPc').addEventListener('click', function () {
    if (!_pcIdEliminar) return;
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
    fetch('/computadoras/' + _pcIdEliminar, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.href = '/dashboard?modulo=computadoras'; }
        else { $('#modalEliminarComputadora').modal('hide'); alert('Error: ' + data.message); }
        btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Eliminar');
    })
    .catch(() => { alert('Error de conexión.'); btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Eliminar'); });
});

// ── Auto-detectar PC ─────────────────────────────────────────────────────────
document.getElementById('btnAutoDetectarPC').addEventListener('click', function () {
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Detectando...');

    // 1. Parsear OS desde userAgent
    function detectarOS(ua) {
        if (/Windows NT 11\.0|Windows NT 10\.0/.test(ua)) {
            // Windows 10 y 11 tienen el mismo NT 10.0; se diferencia por User-Agent Hints
            // Con UA clásico solo podemos saber "Windows 10/11"
            return 'Windows 10 / 11';
        }
        if (/Windows NT 6\.3/.test(ua)) return 'Windows 8.1';
        if (/Windows NT 6\.2/.test(ua)) return 'Windows 8';
        if (/Windows NT 6\.1/.test(ua)) return 'Windows 7';
        if (/Mac OS X ([\d_]+)/.test(ua)) return 'macOS ' + RegExp.$1.replace(/_/g, '.');
        if (/Android ([\d.]+)/.test(ua)) return 'Android ' + RegExp.$1;
        if (/iPhone OS ([\d_]+)/.test(ua)) return 'iOS ' + RegExp.$1.replace(/_/g, '.');
        if (/Linux/.test(ua)) return 'Linux';
        return '';
    }

    // 2. Inferir tipo de dispositivo
    function detectarTipo() {
        const ua = navigator.userAgent;
        if (/tablet|ipad/i.test(ua)) return 'Workstation';
        if (/mobile|android|iphone/i.test(ua)) return 'Laptop'; // móvil = laptop como aproximación
        if (navigator.maxTouchPoints > 1) return 'Laptop';       // táctil de escritorio
        return 'Desktop';
    }

    // 3. RAM aproximada (1 | 2 | 4 | 8 GB)
    function detectarRAM() {
        const mem = navigator.deviceMemory; // puede ser undefined
        if (!mem) return '';
        return mem + ' GB (aprox.)';
    }

    // 4. Intentar obtener IP del servidor y rellenar todo
    fetch('{{ route("computadoras.detectarInfo") }}')
        .then(r => r.json())
        .then(serverData => {
            const ua  = navigator.userAgent;
            const os  = detectarOS(ua);
            const tip = detectarTipo();
            const ram = detectarRAM();

            // Rellenar campos del formulario 'nuevo_'
            const set = (id, val) => {
                const el = document.getElementById('nuevo_' + id);
                if (el && val) el.value = val;
            };

            set('direccion_ip', serverData.ip || '');
            set('sistema_operativo', os);
            set('ram', ram);

            // Tipo: solo rellenar si el select no ha sido tocado
            const tipoEl = document.getElementById('nuevo_tipo');
            if (tipoEl && tipoEl.value === 'Desktop') tipoEl.value = tip;

            // Banner informativo
            document.getElementById('alerta-crear-pc').innerHTML =
                `<div style="background:rgba(103,232,249,0.08);border:1px solid rgba(103,232,249,0.25);border-radius:9px;padding:0.6rem 0.9rem;font-size:0.78rem;color:#67e8f9;line-height:1.6;">
                    <i class="fas fa-info-circle" style="margin-right:0.4rem;"></i>
                    <strong>Detección completada.</strong> IP, sistema operativo, RAM y tipo de equipo fueron rellenados automáticamente.<br>
                    <span style="opacity:0.75;">El procesador, modelo exacto, MAC y almacenamiento deben ingresarse manualmente.</span>
                </div>`;
        })
        .catch(() => {
            const ua  = navigator.userAgent;
            const set = (id, val) => { const el = document.getElementById('nuevo_' + id); if (el && val) el.value = val; };
            set('sistema_operativo', detectarOS(ua));
            set('ram', detectarRAM());
            const tipoEl = document.getElementById('nuevo_tipo');
            if (tipoEl && tipoEl.value === 'Desktop') tipoEl.value = detectarTipo();

            document.getElementById('alerta-crear-pc').innerHTML =
                `<div style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.25);border-radius:9px;padding:0.55rem 0.85rem;font-size:0.78rem;color:#fbbf24;">
                    <i class="fas fa-exclamation-triangle" style="margin-right:0.4rem;"></i>
                    Datos parcialmente detectados. La IP no pudo obtenerse — ingrésala manualmente.
                </div>`;
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="fas fa-magic"></i> Detectar mi PC');
        });
});

</script>

