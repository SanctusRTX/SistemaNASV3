<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema NAS</title>
    <script>
        // Aplicar dark mode por defecto antes de renderizar para evitar parpadeo
        if (localStorage.getItem('darkMode') !== 'disabled') {
            document.documentElement.classList.add('dark-mode-loading');
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('dark-mode');
            });
        }
    </script>
    <link href="{{ asset('Css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/custom-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/poppins.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/iconos.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/estilos.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/botones.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/vista-explorador.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/busqueda.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/tooltips-animados.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/sidebar-fix.css') }}" rel="stylesheet">
    <link href="{{ asset('Css/subcarpetas.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar-tree.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <style>
    /* Anti-flash: fondo oscuro desde el momento cero */
    html.dark-mode-loading { background-color: #0f172a; }

    /* ========== DARK MODE ========== */
    body.dark-mode { background-color: #07090e !important; color: #cbd5e1; display:flex; flex-direction:column; min-height:100vh; }
    body.dark-mode main { background-color: #07090e !important; flex:1 1 auto; }
    body.dark-mode footer, body.dark-mode #main-footer { background-color: #07090e !important; border-color: rgba(255,255,255,0.04) !important; color: #374151; }

    /* Cards */
    body.dark-mode .card { background-color: #1e293b; border-color: #334155; color: #cbd5e1; }
    body.dark-mode .card-header { background-color: #0f3460 !important; border-color: #334155 !important; color: #e2e8f0 !important; }
    body.dark-mode .card-body { background-color: #1e293b; color: #cbd5e1; }
    body.dark-mode .card.shadow-sm { box-shadow: 0 2px 10px rgba(0,0,0,0.4) !important; }

    /* Sidebar */
    body.dark-mode #sidebar { background-color: #1e293b; border-color: #334155 !important; }
    body.dark-mode #sidebar aside { background-color: #1e293b; }
    body.dark-mode .nav-link { color: #94a3b8 !important; }
    body.dark-mode .nav-link:hover { color: #3AB397 !important; background-color: #0f3460; border-radius: 6px; }

    /* Formularios */
    body.dark-mode .form-control,
    body.dark-mode select.form-control,
    body.dark-mode textarea.form-control { background-color: #0f172a; border-color: #334155; color: #cbd5e1; }
    body.dark-mode .form-control:focus,
    body.dark-mode select.form-control:focus,
    body.dark-mode textarea.form-control:focus { background-color: #0f172a; color: #e2e8f0; border-color: #3AB397; box-shadow: 0 0 0 0.2rem rgba(58,179,151,0.2); }
    body.dark-mode .form-control::placeholder { color: #64748b; }
    body.dark-mode .input-group-text { background-color: #1e293b; border-color: #334155; color: #94a3b8; }
    body.dark-mode .custom-file-label { background-color: #0f172a; border-color: #334155; color: #94a3b8; }
    body.dark-mode .custom-file-label::after { background-color: #3AB397; color: #fff; border-color: #3AB397; }
    body.dark-mode label, body.dark-mode .form-label { color: #cbd5e1; }
    body.dark-mode small.form-text { color: #64748b !important; }

    /* Tablas */
    body.dark-mode .table { color: #cbd5e1; background-color: transparent; }
    body.dark-mode .table td, body.dark-mode .table th { border-color: #334155; }
    body.dark-mode .table-hover tbody tr:hover { background-color: #0f3460; color: #e2e8f0; }
    body.dark-mode .thead-light th { background-color: #0f3460 !important; color: #e2e8f0; border-color: #334155; }

    /* Modales */
    body.dark-mode .modal-content { background-color: #1e293b; border-color: #334155; color: #cbd5e1; }
    body.dark-mode .modal-header { border-color: #334155; }
    body.dark-mode .modal-footer { border-color: #334155; background-color: #1e293b; }
    body.dark-mode .modal-body { background-color: #1e293b; }
    body.dark-mode .close { color: #94a3b8 !important; text-shadow: none; }

    /* Botones */
    body.dark-mode .btn-secondary { background-color: #334155; border-color: #475569; color: #e2e8f0; }
    body.dark-mode .btn-secondary:hover { background-color: #475569; }
    body.dark-mode .btn-outline-secondary { border-color: #475569; color: #94a3b8; }
    body.dark-mode .btn-outline-secondary:hover { background-color: #334155; color: #e2e8f0; }
    body.dark-mode .btn-outline-light { border-color: #475569; color: #94a3b8; }
    body.dark-mode .btn-outline-light:hover { background-color: #334155; }

    /* Alertas */
    body.dark-mode .alert-warning { background-color: #3d2c00; border-color: #78450a; color: #fde68a; }
    body.dark-mode .alert-success { background-color: #052e16; border-color: #166534; color: #86efac; }
    body.dark-mode .alert-danger  { background-color: #450a0a; border-color: #991b1b; color: #fca5a5; }
    body.dark-mode .alert-info    { background-color: #0c1a2e; border-color: #1e40af; color: #93c5fd; }

    /* Badges */
    body.dark-mode .badge-secondary { background-color: #334155; color: #cbd5e1; }
    body.dark-mode .badge-warning   { background-color: #78350f; color: #fde68a; }
    body.dark-mode .badge-primary   { background-color: #1e3a8a; color: #bfdbfe; }

    /* Explorador items */
    body.dark-mode .item { background-color: #1e293b !important; border-color: #334155 !important; color: #cbd5e1 !important; }
    body.dark-mode .item:hover { background-color: #0f3460 !important; }
    body.dark-mode .item-name { color: #e2e8f0 !important; }
    body.dark-mode .item-info { color: #94a3b8 !important; }
    body.dark-mode .item-size, body.dark-mode .item-date, body.dark-mode .item-count { color: #64748b !important; }

    /* Breadcrumb */
    body.dark-mode .explorer-path { background-color: #1e293b !important; }
    body.dark-mode .path-segment { color: #94a3b8 !important; }
    body.dark-mode .path-segment:hover { color: #3AB397 !important; }
    body.dark-mode .path-segment.current { color: #3AB397 !important; font-weight: 600; }
    body.dark-mode .path-separator i { color: #475569 !important; }

    /* Misc */
    body.dark-mode .bg-light { background-color: #0f172a !important; color: #cbd5e1 !important; }
    body.dark-mode .border-bottom, body.dark-mode .border-top, body.dark-mode .border-right, body.dark-mode .border { border-color: #334155 !important; }
    body.dark-mode .text-muted { color: #64748b !important; }
    body.dark-mode h4, body.dark-mode h5, body.dark-mode h6, body.dark-mode p, body.dark-mode strong { color: #e2e8f0; }
    body.dark-mode .progress { background-color: #0f172a; border: 1px solid #334155; }
    body.dark-mode .container.border { border-color: #334155 !important; }
    body.dark-mode hr { border-color: #334155; }
    body.dark-mode .search-container input { background-color: #0f172a; color: #cbd5e1; border-color: #334155; }
    body.dark-mode .vista-btn { background-color: #1e293b; color: #94a3b8; border-color: #334155; }
    body.dark-mode .vista-btn.active, body.dark-mode .vista-btn:hover { background-color: #0f3460; color: #e2e8f0; }
    body.dark-mode #btnVaciarPapelera { background-color: #1e293b !important; color: #f87171 !important; border-color: #334155; }
    body.dark-mode .font-weight-bold { color: #e2e8f0; }
    body.dark-mode .sidebar-overlay.show { background: rgba(0,0,0,0.6); }

    /* ========== SESSION WARNING MODAL ========== */
    #session-warning-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        align-items: center;
        justify-content: center;
        animation: none;
    }
    #session-warning-overlay.visible {
        display: flex;
        animation: swOverlayIn 0.3s ease forwards;
    }
    @keyframes swOverlayIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .session-warning-card {
        background: #1e293b;
        border: 1px solid rgba(251, 191, 36, 0.35);
        border-radius: 20px;
        padding: 2.5rem 2.25rem 2rem;
        max-width: 400px;
        width: 92%;
        text-align: center;
        box-shadow: 0 30px 70px rgba(0,0,0,0.55), 0 0 0 1px rgba(251,191,36,0.1);
        animation: swCardIn 0.4s cubic-bezier(0.34,1.56,0.64,1) forwards;
    }
    @keyframes swCardIn {
        from { transform: scale(0.85) translateY(-20px); opacity: 0; }
        to   { transform: scale(1)    translateY(0);     opacity: 1; }
    }
    .session-icon {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: rgba(251, 191, 36, 0.12);
        border: 2px solid rgba(251, 191, 36, 0.4);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem;
        animation: swPulse 1.8s ease-in-out infinite;
    }
    @keyframes swPulse {
        0%,100% { box-shadow: 0 0 0 0   rgba(251,191,36,0.4); }
        50%      { box-shadow: 0 0 0 14px rgba(251,191,36,0);   }
    }
    .session-icon i { font-size: 1.9rem; color: #fbbf24; }
    .session-warning-card h4 {
        color: #f1f5f9; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;
    }
    .session-warning-card p {
        color: #94a3b8; font-size: 0.9rem; margin-bottom: 1.25rem;
    }
    #session-countdown-display {
        font-size: 3rem; font-weight: 800; color: #fbbf24;
        letter-spacing: 2px; margin-bottom: 0.5rem;
        font-variant-numeric: tabular-nums;
        transition: color 0.4s ease;
    }
    #session-countdown-display.urgent   { color: #f97316; }
    #session-countdown-display.critical { color: #ef4444; animation: swBlink 0.5s ease-in-out infinite alternate; }
    @keyframes swBlink { from { opacity: 1; } to { opacity: 0.4; } }
    .session-warning-sub {
        font-size: 0.78rem !important; color: #64748b !important; margin-bottom: 1.5rem !important;
    }
    .session-btn-group { display: flex; flex-direction: column; gap: 0.65rem; }
    #session-btn-continuar {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        background: linear-gradient(135deg, #3AB397, #2d9a80);
        color: #fff; border: none; border-radius: 10px;
        padding: 0.75rem 1.25rem; font-size: 0.92rem; font-weight: 600;
        cursor: pointer; transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(58,179,151,0.3);
    }
    #session-btn-continuar:hover {
        background: linear-gradient(135deg, #2d9a80, #228068);
        transform: translateY(-2px); box-shadow: 0 6px 20px rgba(58,179,151,0.4);
    }
    #session-btn-salir {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        background: transparent; color: #64748b;
        border: 1px solid rgba(100,116,139,0.3); border-radius: 10px;
        padding: 0.65rem 1.25rem; font-size: 0.85rem; font-weight: 500;
        cursor: pointer; transition: all 0.2s ease;
    }
    #session-btn-salir:hover {
        background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.4); color: #fca5a5;
    }
    .session-spinner { color: #94a3b8; font-size: 0.9rem; margin-top: 1rem; }
    </style>

    <script src="{{ asset('vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('vendor/popper/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('Js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('Js/ionicons.js') }}"></script>
    <script src="{{ asset('Js/tooltips-animados.js') }}"></script>
    <script src="{{ asset('Js/renombrar.js') }}"></script>
    <script src="{{ asset('Js/eliminar.js') }}"></script>
    <script src="{{ asset('Js/mobile-responsive.js') }}"></script>
    <script src="{{ asset('Js/vista-explorador.js') }}"></script>
    <link href="{{ asset('css/aesthetic-dark.css') }}?v={{ time() }}" rel="stylesheet">
</head>
<body>

<div class="sidebar-overlay" aria-hidden="true"></div>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark border-bottom py-2">
        <div class="container-fluid">
            <button id="toggleSidebar" class="btn btn-outline-light d-lg-none mr-2">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}" style="font-weight: 700; font-size: 1.1rem; letter-spacing: 0.05em;">
                <span class="brand-logo-icon mr-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3AB397, #22d3ee); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 0 10px rgba(58,179,151,0.4);">
                    <i class="fas fa-server" style="font-size: 0.9rem; color: #07090e;"></i>
                </span>
                Sistema NAS
            </a>
            
            {{-- Buscador centralizado --}}
            <div class="d-none d-lg-flex align-items-center mx-auto" style="max-width: 380px; width: 100%;">
                <form action="{{ route('dashboard') }}" method="get" class="w-100 m-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.06); border-right: none; color: #64748b; border-radius: 8px 0 0 8px;">
                                <i class="fas fa-search" style="font-size: 0.8rem;"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" name="buscar"
                               placeholder="Buscar en el almacenamiento..."
                               value="{{ request('buscar') }}"
                               style="background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(255,255,255,0.06) !important; border-left: none !important; color: #f1f5f9 !important; border-radius: 0 8px 8px 0 !important; font-size: 0.8rem; height: 34px;"
                               autocomplete="off">
                    </div>
                </form>
            </div>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMobile">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarMobile">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item d-lg-none w-100 text-center mb-2">
                        <span class="nav-link text-white">
                            <i class="fas fa-user"></i> Usuario: {{ ucfirst(session('username', 'Invitado')) }}
                        </span>
                    </li>
                    <li class="nav-item ml-lg-2">
                        <button type="button"
                            id="btnAbrirPerfil"
                            data-toggle="modal"
                            data-target="#modalEditarPerfil"
                            title="Editar mi perfil"
                            style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); cursor:pointer; padding:6px 12px; border-radius:10px; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.05)'"
                            onmouseout="this.style.background='rgba(255,255,255,0.02)'">
                            <span class="d-flex align-items-center gap-2" style="color:#fff; gap:6px;">
                                <span style="
                                    width:24px; height:24px; border-radius:50%;
                                    background:rgba(58,179,151,0.15);
                                    border:1px solid rgba(58,179,151,0.4);
                                    display:flex; align-items:center; justify-content:center;
                                    margin-right: 6px;
                                ">
                                    <i class="fas fa-user" style="font-size:.7rem; color: #3AB397;"></i>
                                </span>
                                <span style="line-height:1; text-align: left;">
                                    <span style="font-weight:600; font-size:.8rem; display: block;">
                                        {{ ucfirst(session('username', 'Invitado')) }}
                                    </span>
                                    <small style="opacity:.6; font-size:.65rem; display: block; margin-top: 1px;">
                                        {{ ucfirst(session('rol', 'Usuario')) }}
                                    </small>
                                </span>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item ml-lg-3">
                        <form method="post" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm" type="submit" title="Cerrar Sesión" style="border-radius: 8px; font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main style="flex:1 1 auto; display:flex; flex-direction:column;">
    <div class="row position-relative" style="flex:1;">

        <div id="sidebar" class="col-md-3 border-right">
            <aside class="p-3" id="sidebar-nav" data-carpeta-actual="{{ $carpeta ?? '' }}">
                <div class="sidebar-tree-header">
                    <div class="d-flex align-items-center mb-2 mt-2" style="color: #cbd5e1;">
                        <i class="fas fa-hdd mr-2" style="font-size: 1.1rem; color: #3AB397;"></i>
                        <h6 class="mb-0" style="font-weight: 600; font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase;">Almacenamiento</h6>
                    </div>

                    {{-- Pestañas de ruta (breadcrumb compacto) --}}
                    <div id="sidebar-path-tabs" class="sidebar-path-tabs"></div>

                    {{-- Acceso rápido a carpetas raíz --}}
                    @if(count($carpetas) > 0)
                    <div class="sidebar-quick-tabs">
                        @foreach($carpetas as $carpetaItem)
                        <button type="button" class="sidebar-quick-tab" data-ruta="{{ $carpetaItem }}" title="{{ $carpetaItem }}">
                            <i class="fas fa-folder"></i>
                            <span>{{ $carpetaItem }}</span>
                        </button>
                        @endforeach
                    </div>
                    @endif

                    {{-- Filtro --}}
                    <div class="sidebar-filter">
                        <div class="sidebar-filter-wrap">
                            <i class="fas fa-search"></i>
                            <input type="search" id="sidebar-filter" placeholder="Filtrar carpetas y archivos…" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="sidebar-tree-scroll">
                    <ul class="tree-root">
                        @foreach($carpetas as $carpetaItem)
                        @php
                            $activa = isset($carpeta) && ($carpeta === $carpetaItem || str_starts_with($carpeta ?? '', $carpetaItem . '/'));
                            $esActual = isset($carpeta) && $carpeta === $carpetaItem;
                        @endphp
                        <li class="tree-node{{ $activa ? ' expanded' : '' }}" data-ruta="{{ $carpetaItem }}" data-tipo="carpeta">
                            <div class="tree-row{{ $esActual ? ' active' : '' }}" data-ruta="{{ $carpetaItem }}">
                                <button type="button" class="tree-toggle{{ $activa ? ' expanded' : '' }}" aria-label="Expandir" title="Expandir">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <span class="tree-icon icon-folder{{ $activa ? ' icon-folder-open' : '' }}">
                                    <i class="fas fa-folder{{ $activa ? '-open' : '' }}"></i>
                                </span>
                                <span class="tree-label" role="button" tabindex="0" data-ruta="{{ $carpetaItem }}" title="{{ $carpetaItem }} (clic: expandir · doble clic: abrir)">{{ $carpetaItem }}</span>
                            </div>
                            <ul class="tree-children" id="tree-children-{{ md5($carpetaItem) }}">
                                @if($activa)
                                <li class="tree-loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</li>
                                @endif
                            </ul>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>

        <div id="mainContent" class="col-md-9 py-3 pr-4 @if(isset($modulo) && $modulo !== 'explorador_windows') modulo-layout @endif">

            {{-- Barra de herramientas superior estilo Linear --}}
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" id="main-toolbar" style="gap: 0.5rem;">
                
                {{-- Breadcrumb dinámico --}}
                <div class="d-flex align-items-center flex-wrap" id="breadcrumb-path" style="gap: 0.25rem; font-size: 0.8rem;">
                    <span style="color: #64748b;"><i class="fas fa-layer-group" style="font-size: 0.8rem;"></i></span>
                    <a href="{{ route('dashboard') }}" class="path-segment" style="font-size: 0.8rem;">
                        <i class="fas fa-home" style="font-size: 0.75rem; color: #3AB397;"></i> Almacenamiento
                    </a>
                </div>

                {{-- Botones de acción --}}
                <div class="d-flex align-items-center" style="gap: 0.5rem;">
                    @if(!isset($servidor))
                        {{-- Botón Actualizar --}}
                        <a href="{{ route('dashboard') }}" class="action-btn" title="Actualizar" data-tooltip="Actualizar">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                        @if(session('rol') === 'administrador' || session('rol') === 'supervisor')
                        <a href="{{ route('dashboard') }}?modulo=crear_carpeta" class="action-btn" title="Crear Carpeta">
                            <i class="fas fa-folder-plus" style="color: #60a5fa;"></i>
                        </a>
                        <a href="{{ route('dashboard') }}?modulo=crear_archivo" class="action-btn" title="Crear Archivo">
                            <i class="fas fa-file-alt" style="color: #34d399;"></i>
                        </a>
                        <a href="{{ route('dashboard') }}?modulo=copiarmover" class="action-btn" title="Copiar/Mover">
                            <i class="fas fa-copy" style="color: #94a3b8;"></i>
                        </a>
                        @endif
                        <a href="{{ route('subir') }}" class="action-btn" title="Subir Archivo">
                            <i class="fas fa-upload" style="color: #34d399;"></i>
                        </a>
                        <a href="{{ route('dashboard') }}?modulo=servidores" class="action-btn" title="Servidores FTP">
                            <i class="fas fa-network-wired" style="color: #67e8f9;"></i>
                        </a>
                        <a href="{{ route('dashboard') }}?modulo=computadoras" class="action-btn" title="Computadoras">
                            <i class="fas fa-desktop" style="color: #94a3b8;"></i>
                        </a>
                        @if(session('rol') === 'administrador')
                        <a href="{{ route('dashboard') }}?modulo=papelera" class="action-btn" title="Papelera">
                            <i class="fas fa-trash-alt" style="color: #fca5a5;"></i>
                        </a>
                        <a href="{{ route('dashboard') }}?modulo=usuarios" class="action-btn" title="Usuarios">
                            <i class="fas fa-users" style="color: #c084fc;"></i>
                        </a>
                        @endif
                    @else
                        {{-- Modo Remoto --}}
                        <span class="badge badge-warning text-dark mr-2" style="font-size: 11px; border-radius: 6px; padding: 0.35em 0.65em;">
                            <i class="fas fa-globe"></i> REMOTO: {{ $servidor->nombre }}
                        </span>
                        @if(session('rol') === 'administrador' || session('rol') === 'supervisor')
                        <button type="button" class="action-btn" title="Crear Carpeta" onclick="crearCarpetaRemota()">
                            <i class="fas fa-folder-plus" style="color: #60a5fa;"></i>
                        </button>
                        <button type="button" class="action-btn" title="Subir Archivo" onclick="abrirModalSubirRemoto()">
                            <i class="fas fa-upload" style="color: #34d399;"></i>
                        </button>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Contenido del módulo --}}
            @yield('content')

        </div>
    </div>
</main>

<footer id="main-footer">
    <span>&copy; 2025 UPTTMBI &mdash; Sistema NAS</span>
    <span style="opacity:0.4;">|</span>
    <span>Hecho por Josué.S. &middot; José.U. &middot; Daniel.M.</span>
</footer>

<script src="{{ asset('Js/sidebar-controller.js') }}"></script>
<script src="{{ asset('Js/script2.js') }}"></script>
<script src="{{ asset('Js/sidebar-tree.js') }}?v={{ filemtime(public_path('Js/sidebar-tree.js')) }}"></script>
<script>
$(document).ready(function() {
    if (localStorage.getItem('darkMode') !== 'disabled') {
        $('body').addClass('dark-mode');
        $('#darkModeIcon').removeClass('fa-moon').addClass('fa-sun');
        $('#darkModeBtn').removeClass('btn-outline-secondary').addClass('btn-warning');
    }
    $('#darkModeBtn').on('click', function() {
        const isDark = $('body').hasClass('dark-mode');
        $('body').toggleClass('dark-mode', !isDark);
        localStorage.setItem('darkMode', isDark ? 'disabled' : 'enabled');
        $('#darkModeIcon').toggleClass('fa-moon fa-sun');
        $('#darkModeBtn').toggleClass('btn-outline-secondary btn-warning');
    });
});
</script>

@if(session('loggedin'))
{{-- ===================== MODAL ADVERTENCIA DE SESIÓN ===================== --}}
<div id="session-warning-overlay" role="dialog" aria-modal="true" aria-label="Advertencia de sesión">
    <div class="session-warning-card">
        <div class="session-icon">
            <i class="fas fa-clock"></i>
        </div>
        <h4>¿Sigues ahí?</h4>
        <p>Tu sesión se cerrará automáticamente en:</p>
        <div id="session-countdown-display">01:00</div>
        <p class="session-warning-sub">
            <i class="fas fa-mouse-pointer"></i>
            Mueve el cursor o presiona una tecla para continuar
        </p>
        <div class="session-btn-group">
            <button id="session-btn-continuar" type="button">
                <i class="fas fa-check-circle"></i> Continuar Sesión
            </button>
            <button id="session-btn-salir" type="button">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión Ahora
            </button>
        </div>
    </div>
</div>

{{-- Configuración para session-manager.js --}}
<script>
window.SessionManagerConfig = {
    pingUrl  : '{{ route("session.ping") }}',
    closeUrl : '{{ route("session.close") }}',
    loginUrl : '{{ route("login") }}',
};
</script>
<script src="{{ asset('Js/session-manager.js') }}"></script>

{{-- ===================== MODAL EDITAR PERFIL ===================== --}}
<div class="modal fade" id="modalEditarPerfil" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg,#3AB397,#2d9a80);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-edit"></i> Editar Mi Perfil
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <span style="
                        display:inline-flex; align-items:center; justify-content:center;
                        width:64px; height:64px; border-radius:50%;
                        background:linear-gradient(135deg,#3AB397,#2d9a80);
                        color:#fff; font-size:1.8rem;
                    ">
                        <i class="fas fa-user"></i>
                    </span>
                    <p class="mt-2 mb-0 font-weight-bold">{{ ucfirst(session('username','')) }}</p>
                    <small class="text-muted">{{ ucfirst(session('rol','')) }}</small>
                </div>
                <hr>
                <div class="form-group">
                    <label><i class="fas fa-user text-primary"></i> Nombre de usuario</label>
                    <input type="text" class="form-control" id="perfil_username"
                           value="{{ session('username','') }}" maxlength="32">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock text-warning"></i> Nueva contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="perfil_password"
                               maxlength="12" placeholder="Dejar en blanco para no cambiarla">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnTogglePwd"
                                    title="Mostrar/Ocultar">
                                <i class="fas fa-eye" id="iconoPwd"></i>
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Máx. 12 caracteres</small>
                </div>
                <div id="perfil-alert" class="alert d-none mt-2" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarPerfil">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL VISTA PREVIA (móvil/tablet) ===================== --}}
<div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header preview-panel-header" style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--border-glass);">
                <div class="preview-header-info" style="min-width:0; flex:1;">
                    <div class="preview-header-icon"><i class="fas fa-file"></i></div>
                    <h5 class="preview-header-name modal-title mb-0"></h5>
                    <small class="preview-header-meta text-muted"></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body preview-body p-3" id="preview-modal-body"></div>
            <div class="modal-footer preview-footer d-flex flex-wrap gap-2" style="border-top: 1px solid var(--border-glass);">
                <a href="#" class="btn btn-sm btn-outline-success preview-btn-download" target="_blank" rel="noopener">
                    <i class="fas fa-download"></i> Descargar
                </a>
                <a href="#" class="btn btn-sm btn-outline-info preview-btn-editar d-none">
                    <i class="fas fa-file-alt"></i> Editar
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Toggle mostrar/ocultar contraseña
    $('#btnTogglePwd').on('click', function () {
        const inp = document.getElementById('perfil_password');
        const ico = document.getElementById('iconoPwd');
        if (inp.type === 'password') {
            inp.type = 'text';
            ico.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inp.type = 'password';
            ico.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Guardar perfil
    $('#btnGuardarPerfil').on('click', function () {
        const username = $('#perfil_username').val().trim();
        const password = $('#perfil_password').val();
        const alertBox = $('#perfil-alert');

        if (!username) {
            alertBox.removeClass('d-none alert-success').addClass('alert-danger')
                    .text('El nombre de usuario no puede estar vacío.');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        alertBox.addClass('d-none');

        fetch('/perfil', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ username: username, password: password })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alertBox.removeClass('d-none alert-danger').addClass('alert-success')
                        .text('✔ Perfil actualizado. Recargando...');
                setTimeout(() => location.reload(), 1200);
            } else {
                alertBox.removeClass('d-none alert-success').addClass('alert-danger')
                        .text('Error: ' + (data.message || 'No se pudo guardar.'));
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar cambios');
            }
        })
        .catch(() => {
            alertBox.removeClass('d-none alert-success').addClass('alert-danger')
                    .text('Error de conexión.');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar cambios');
        });
    });

    // Limpiar al cerrar el modal
    $('#modalEditarPerfil').on('hidden.bs.modal', function () {
        $('#perfil_password').val('');
        $('#perfil-alert').addClass('d-none').text('');
        $('#btnGuardarPerfil').prop('disabled', false)
                              .html('<i class="fas fa-save"></i> Guardar cambios');
    });
});
</script>
@endif

@stack('scripts')

</body>
</html>