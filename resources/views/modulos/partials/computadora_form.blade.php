{{--
    Formulario reutilizable para Computadora — Nexus OS Style
    Parámetro requerido: $prefijo ('nuevo' | 'edit')
--}}

{{-- Estilos de inputs del formulario --}}
<style>
.pc-form-input {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 9px;
    color: #f1f5f9;
    font-size: 0.82rem;
    padding: 0.5rem 0.75rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.pc-form-input:focus {
    border-color: rgba(58,179,151,0.5);
    box-shadow: 0 0 0 3px rgba(58,179,151,0.08);
}
.pc-form-input::placeholder { color: #374151; }
.pc-form-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.3rem;
    letter-spacing: 0.03em;
}
.pc-form-label .req { color: #ef4444; margin-left: 2px; }
.pc-section-title {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    margin-bottom: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.pc-input-group {
    display: flex;
    align-items: stretch;
}
.pc-input-group .pc-input-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-right: none;
    border-radius: 9px 0 0 9px;
    color: #475569;
    font-size: 0.75rem;
    flex-shrink: 0;
}
.pc-input-group .pc-form-input {
    border-radius: 0 9px 9px 0;
}
</style>

<div class="row" style="row-gap: 0.25rem;">

    {{-- Honeypot oculto: absorbe el autofill del navegador antes de llegar a los campos reales --}}
    <div style="display:none;" aria-hidden="true">
        <input type="text"     name="fake_user_{{ $prefijo }}" tabindex="-1" autocomplete="username">
        <input type="password" name="fake_pass_{{ $prefijo }}" tabindex="-1" autocomplete="new-password">
    </div>

    {{-- ── Sección Identificación ── --}}
    <div class="col-12">
        <div class="pc-section-title" style="color:#67e8f9;">
            <i class="fas fa-desktop"></i> Identificación
        </div>
    </div>

    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Nombre / Código <span class="req">*</span></label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_nombre"
                   maxlength="100" placeholder="Ej: PC-ADMIN-01"
                   autocomplete="off">
        </div>
    </div>
    <div class="col-md-3">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Tipo <span class="req">*</span></label>
            <select class="pc-form-input" id="{{ $prefijo }}_tipo" autocomplete="off">
                <option value="Desktop">Desktop</option>
                <option value="Laptop">Laptop</option>
                <option value="All-in-One">All-in-One</option>
                <option value="Servidor">Servidor</option>
                <option value="Workstation">Workstation</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Estado <span class="req">*</span></label>
            <select class="pc-form-input" id="{{ $prefijo }}_estado" autocomplete="off">
                <option value="Activo">Activo</option>
                <option value="En reparación">En reparación</option>
                <option value="Dado de baja">Dado de baja</option>
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Número de Serie</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_numero_serie"
                   maxlength="100" placeholder="Ej: SN123456789"
                   autocomplete="off">
        </div>
    </div>
    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Código de Inventario</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_codigo_inventario"
                   maxlength="100" placeholder="Ej: INV-2024-0042"
                   autocomplete="off">
        </div>
    </div>

    {{-- ── Sección Hardware ── --}}
    <div class="col-12">
        <div class="pc-section-title" style="color:#34d399;">
            <i class="fas fa-microchip"></i> Hardware
        </div>
    </div>

    <div class="col-md-4">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Marca</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_marca"
                   maxlength="80" placeholder="Ej: Dell, HP, Lenovo"
                   autocomplete="off">
        </div>
    </div>
    <div class="col-md-4">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Modelo</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_modelo"
                   maxlength="100" placeholder="Ej: OptiPlex 7090"
                   autocomplete="off">
        </div>
    </div>
    <div class="col-md-4">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Sistema Operativo</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_sistema_operativo"
                   maxlength="100" placeholder="Ej: Windows 11 Pro"
                   autocomplete="off">
        </div>
    </div>

    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Procesador</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_procesador"
                   maxlength="150" placeholder="Ej: Intel Core i7-12700"
                   autocomplete="off">
        </div>
    </div>
    <div class="col-md-3">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">RAM</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_ram"
                   maxlength="50" placeholder="Ej: 16 GB DDR4"
                   autocomplete="off">
        </div>
    </div>
    <div class="col-md-3">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Almacenamiento</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_almacenamiento"
                   maxlength="100" placeholder="Ej: 512 GB SSD"
                   autocomplete="off">
        </div>
    </div>

    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Tarjeta Gráfica</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_tarjeta_grafica"
                   maxlength="150" placeholder="Ej: NVIDIA RTX 3060"
                   autocomplete="off">
        </div>
    </div>

    {{-- ── Sección Operador y Departamento ── --}}
    <div class="col-12">
        <div class="pc-section-title" style="color:#a78bfa;">
            <i class="fas fa-user"></i> Operador y Departamento
        </div>
    </div>

    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Nombre del Operador <span class="req">*</span></label>
            {{-- "new-password" evita que Chrome sugiera nombres de personas guardados --}}
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_operador"
                   maxlength="150" placeholder="Nombre completo del usuario asignado"
                   autocomplete="new-password">
        </div>
    </div>
    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Cargo del Operador</label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_cargo_operador"
                   maxlength="100" placeholder="Ej: Analista de Sistemas"
                   autocomplete="new-password">
        </div>
    </div>

    <div class="col-md-6">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Departamento <span class="req">*</span></label>
            <input type="text" class="pc-form-input" id="{{ $prefijo }}_departamento"
                   maxlength="150" placeholder="Ej: Recursos Humanos"
                   autocomplete="new-password">
        </div>
    </div>

    {{-- ── Sección Red ── --}}
    <div class="col-12">
        <div class="pc-section-title" style="color:#67e8f9;">
            <i class="fas fa-network-wired"></i> Red
        </div>
    </div>

    <div class="col-md-4">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Dirección IP</label>
            <div class="pc-input-group">
                <span class="pc-input-icon"><i class="fas fa-network-wired"></i></span>
                <input type="text" class="pc-form-input" id="{{ $prefijo }}_direccion_ip"
                       maxlength="45" placeholder="192.168.1.100"
                       autocomplete="off">
            </div>
            <div style="font-size:0.68rem;color:#374151;margin-top:0.25rem;">IPv4 o IPv6</div>
        </div>
    </div>

    <div class="col-md-5">
        <div style="margin-bottom:0.75rem;">
            <label class="pc-form-label">Dirección MAC</label>
            <div class="pc-input-group">
                <span class="pc-input-icon"><i class="fas fa-ethernet"></i></span>
                <input type="text" class="pc-form-input" id="{{ $prefijo }}_direccion_mac"
                       maxlength="17" placeholder="AA:BB:CC:DD:EE:FF"
                       autocomplete="off"
                       oninput="formatearMac(this)">
            </div>
            <div style="font-size:0.68rem;color:#374151;margin-top:0.25rem;">Formato: AA:BB:CC:DD:EE:FF</div>
        </div>
    </div>

    {{-- ── Observaciones ── --}}
    <div class="col-12">
        <div style="margin-bottom:0.5rem;">
            <label class="pc-form-label">Observaciones</label>
            <textarea class="pc-form-input" id="{{ $prefijo }}_observaciones"
                      rows="3" placeholder="Notas adicionales, historial de mantenimiento, etc."
                      autocomplete="off"></textarea>
        </div>
    </div>

</div>
