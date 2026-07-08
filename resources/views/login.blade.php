<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema NAS — Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login-dark.css') }}">
</head>
<body class="login-body">

    {{-- Fondo animado con partículas/grilla --}}
    <div class="login-bg">
        <div class="login-grid"></div>
        <div class="login-glow login-glow-1"></div>
        <div class="login-glow login-glow-2"></div>
    </div>

    <div class="login-wrapper">

        {{-- Panel izquierdo — Branding --}}
        <div class="login-brand-panel">
            <div class="brand-content">
                <div class="brand-logo-wrap">
                    <div class="brand-logo-icon">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
                <h1 class="brand-title">Sistema NAS</h1>
                <p class="brand-subtitle">Plataforma de almacenamiento y gestión de archivos institucional</p>

                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="feature-dot"></div>
                        <span>Gestión de archivos local y remota</span>
                    </div>
                    <div class="brand-feature">
                        <div class="feature-dot"></div>
                        <span>Control de acceso por roles</span>
                    </div>
                    <div class="brand-feature">
                        <div class="feature-dot"></div>
                        <span>Conexión FTP/SFTP a servidores externos</span>
                    </div>
                    <div class="brand-feature">
                        <div class="feature-dot"></div>
                        <span>Inventario de equipos de cómputo</span>
                    </div>
                </div>

                <div class="brand-footer-text">
                    <span>UPTTMBI &copy; {{ date('Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Panel derecho — Formulario --}}
        <div class="login-form-panel">
            <div class="login-card">

                <div class="login-card-header">
                    <div class="login-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h2 class="login-title">Bienvenido</h2>
                    <p class="login-desc">Ingresa tus credenciales para continuar</p>
                </div>

                @if(session('error'))
                    <div class="login-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="post" class="login-form" id="loginForm">
                    @csrf

                    <div class="login-field">
                        <label for="usuario" class="login-label">
                            <i class="fas fa-user"></i> Usuario
                        </label>
                        <div class="login-input-wrap">
                            <input
                                type="text"
                                id="usuario"
                                name="usuario"
                                class="login-input"
                                placeholder="Ingresa tu usuario"
                                maxlength="32"
                                autocomplete="username"
                                required
                            >
                            <div class="input-focus-bar"></div>
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="password" class="login-label">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div class="login-input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="login-input"
                                placeholder="••••••••••••"
                                maxlength="12"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="toggle-pwd" id="togglePwd" title="Mostrar/ocultar contraseña">
                                <i class="fas fa-eye" id="togglePwdIcon"></i>
                            </button>
                            <div class="input-focus-bar"></div>
                        </div>
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </span>
                        <span class="btn-loading" style="display:none;">
                            <i class="fas fa-spinner fa-spin"></i> Verificando...
                        </span>
                    </button>

                </form>

                <div class="login-card-footer">
                    <span>Acceso restringido — solo personal autorizado</span>
                </div>

            </div>
        </div>

    </div>

    <script>
        // Toggle mostrar/ocultar contraseña
        const togglePwd = document.getElementById('togglePwd');
        const pwdInput  = document.getElementById('password');
        const pwdIcon   = document.getElementById('togglePwdIcon');

        togglePwd.addEventListener('click', () => {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            pwdIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });

        // Loading state al enviar
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.querySelector('.btn-text').style.display = 'none';
            btn.querySelector('.btn-loading').style.display = 'inline-flex';
            btn.disabled = true;
        });
    </script>
</body>
</html>