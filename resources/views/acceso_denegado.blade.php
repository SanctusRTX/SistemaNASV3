<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado – Sistema NAS</title>
    <link href="{{ asset('Css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f3460 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            overflow: hidden;
        }

        /* Fondo animado con partículas */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(239,68,68,0.07) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(99,102,241,0.07) 0%, transparent 50%),
                radial-gradient(circle at 50% 80%, rgba(58,179,151,0.05) 0%, transparent 50%);
            animation: bgPulse 8s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes bgPulse {
            0%   { opacity: 0.6; }
            100% { opacity: 1; }
        }

        .acceso-denegado-card {
            position: relative;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 24px;
            padding: 3rem 3.5rem;
            max-width: 520px;
            width: 90%;
            text-align: center;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(239, 68, 68, 0.1),
                inset 0 1px 0 rgba(255,255,255,0.05);
            animation: slideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes slideIn {
            0%  { opacity: 0; transform: translateY(-40px) scale(0.9); }
            100%{ opacity: 1; transform: translateY(0)    scale(1); }
        }

        .icono-bloqueo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(239,68,68,0.05));
            border: 2px solid rgba(239,68,68,0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse 2.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            50%       { box-shadow: 0 0 0 16px rgba(239,68,68,0); }
        }

        .icono-bloqueo i {
            font-size: 2.2rem;
            color: #ef4444;
        }

        .codigo-error {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 3px;
            color: #ef4444;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        h1 {
            font-size: 1.7rem;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
            line-height: 1.2;
        }

        .descripcion {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }

        .info-rol {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(99,102,241,0.3);
            border-radius: 50px;
            padding: 0.5rem 1.25rem;
            margin-bottom: 1.75rem;
            font-size: 0.85rem;
            color: #a5b4fc;
        }

        .info-rol i { color: #818cf8; }

        .divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin: 0 0 1.75rem;
        }

        .btn-group-acceso {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-volver {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, #3AB397, #2d9a80);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(58,179,151,0.3);
        }

        .btn-volver:hover {
            background: linear-gradient(135deg, #2d9a80, #228068);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(58,179,151,0.4);
            text-decoration: none;
        }

        .btn-cerrar-sesion {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            background: transparent;
            color: #64748b;
            border: 1px solid rgba(100,116,139,0.3);
            border-radius: 12px;
            padding: 0.7rem 1.5rem;
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-cerrar-sesion:hover {
            background: rgba(239,68,68,0.1);
            border-color: rgba(239,68,68,0.4);
            color: #fca5a5;
            text-decoration: none;
        }

        .badge-modulo {
            display: inline-block;
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            font-size: 0.8rem;
            font-family: monospace;
            margin: 0 2px;
        }

        @media (max-width: 480px) {
            .acceso-denegado-card { padding: 2rem 1.75rem; }
            h1 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<div class="acceso-denegado-card">

    <!-- Ícono animado -->
    <div class="icono-bloqueo">
        <i class="fas fa-shield-alt"></i>
    </div>

    <!-- Código de error -->
    <p class="codigo-error">Error 403 – Acceso Denegado</p>

    <!-- Título -->
    <h1>No tienes permiso para acceder aquí</h1>

    <!-- Descripción -->
    <p class="descripcion">
        Tu cuenta
        @if(session('username'))
            <strong style="color:#e2e8f0;">{{ ucfirst(session('username')) }}</strong>
        @endif
        no tiene los permisos necesarios para acceder a este módulo.
        @if(session('modulo_bloqueado'))
            El módulo <span class="badge-modulo">{{ session('modulo_bloqueado') }}</span>
            solo está disponible para usuarios con permisos superiores.
        @endif
    </p>

    <!-- Rol actual -->
    <div class="info-rol">
        <i class="fas fa-user-tag"></i>
        Tu rol actual:
        <strong>{{ ucfirst(session('rol', 'Usuario')) }}</strong>
    </div>

    <hr class="divider">

    <!-- Botones de acción -->
    <div class="btn-group-acceso">
        <a href="{{ route('dashboard') }}" class="btn-volver">
            <i class="fas fa-home"></i>
            Volver al Inicio
        </a>

        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn-cerrar-sesion w-100">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </button>
        </form>
    </div>

</div>

</body>
</html>
