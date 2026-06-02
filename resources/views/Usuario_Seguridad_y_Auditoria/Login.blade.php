<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f4f6fb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            background: #ffffff;
            overflow: hidden;
        }

        .login-left {
            width: 48%;
            background:
                linear-gradient(rgba(29, 54, 98, 0.88), rgba(4, 22, 53, 0.88)),
                url("{{ asset('images/universidad.jpg.jpeg1.jpeg') }}");
            background-size: cover;
            background-position: center;
            color: white;
            position: relative;
            display: flex;
            align-items: center;
            padding-left: 80px;
        }

        .login-left::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            border-top: 90px solid #d71920;
            border-right: 180px solid transparent;
        }

        .login-left::after {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            border-bottom: 80px solid #ffffff;
            border-left: 130px solid transparent;
        }

        .brand {
            position: relative;
            z-index: 2;
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .brand-logo {
            width: 90px;
            height: 90px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: bold;
        }

        .brand h1 {
            font-size: 30px;
            line-height: 1.2;
            letter-spacing: 1px;
        }

        .brand-line {
            width: 190px;
            height: 3px;
            background: #d71920;
            margin: 22px 0 12px 110px;
        }

        .brand p {
            margin-left: 110px;
            font-size: 16px;
            line-height: 1.4;
        }

        .login-right {
            width: 52%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(#d7d7d7 1px, transparent 1px);
            background-size: 15px 15px;
            padding: 30px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 42px 45px;
            border-radius: 6px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            text-align: center;
        }

        .card-logo {
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0b2d6b;
        font-weight: bold;
        font-size: 24px;
}

        .login-card h2 {
            color: #0b2d6b;
            font-size: 25px;
            margin-bottom: 8px;
        }

        .red-line {
            width: 48px;
            height: 2px;
            background: #d71920;
            margin: 0 auto 18px;
        }

        .login-card p {
            color: #777;
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.4;
        }

        .alert-error {
            background: #ffe3e3;
            color: #b00020;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: left;
        }

        .alert-success {
            background: #e5f8e8;
            color: #168038;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: left;
        }

        .alert-warning {
            background: #fff4e5;
            color: #8a5a00;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: left;
            border-left: 4px solid #d71920;
        }

        .alert-warning strong {
            color: #082f6f;
        }

        .tiempo-restante {
            font-size: 22px;
            font-weight: bold;
            color: #d71920;
            margin-top: 6px;
        }

        .btn-login:disabled {
            background: #9aa8c0;
            cursor: not-allowed;
        }

        .input-group {
            position: relative;
            margin-bottom: 16px;
        }

        .input-group input {
            width: 100%;
            height: 46px;
            border: 1px solid #dcdcdc;
            border-radius: 4px;
            padding: 0 45px;
            font-size: 14px;
            outline: none;
        }

        .input-group input:focus {
            border-color: #0b2d6b;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            font-size: 15px;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            font-size: 15px;
            border: none;
            background: none;
        }

        .btn-login {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 4px;
            background: #082f6f;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 6px;
        }

        .btn-login:hover {
            background: #06265a;
        }

        .forgot-link {
            display: inline-block;
            margin-top: 18px;
            color: #0b2d6b;
            font-size: 13px;
            text-decoration: none;
            font-weight: bold;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .login-left {
                display: none;
            }

            .login-right {
                width: 100%;
                min-height: 100vh;
            }

            .login-card {
                max-width: 430px;
            }
        }

        @media (max-width: 480px) {
            .login-right {
                padding: 18px;
            }

            .login-card {
                padding: 32px 24px;
            }

            .login-card h2 {
                font-size: 22px;
            }
        }
        .brand-logo, .card-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    /* Contenedor y tamaño base de cada logo */
    .brand-logo {
        width: 140px;
        height: 140px;
    }

    .card-logo {
        width: 90px;
        height: 90px;
    }

    /* Imágenes dentro de los contenedores */
    .brand-logo img, .card-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* mantiene proporción sin recortar */
    }

    /* Ajustes para pantallas medianas (tablets) */
    @media (max-width: 900px) {
        .brand-logo {
            width: 110px;
            height: 110px;
        }
        .card-logo {
            width: 70px;
            height: 70px;
        }
    }

    /* Ajustes para pantallas pequeñas (celulares) */
    @media (max-width: 480px) {
        .brand-logo {
            width: 90px;
            height: 90px;
        }
        .card-logo {
            width: 60px;
            height: 60px;
        }
    }
    </style>
</head>
<body>

    <main class="login-container">

        <section class="login-left">
            <div class="brand">
                <div class="brand-row">
                <div class="brand-logo">
                <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo" class="w-full h-full object-contain">
                </div>
                    <h1>
                        INSCRIPCION CUP<br>
                        FICCT
                    </h1>
                </div>

                <div class="brand-line"></div>

                <p>
                    Formamos líderes para<br>
                    transformar el futuro
                </p>
            </div>
        </section>

        <section class="login-right">
            <div class="login-card">

            <div class="card-logo">
            <img src="{{ asset('images/LogoUAGRM.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>

                <h2>Inicio de sesión</h2>
                <div class="red-line"></div>

                <p>
                    Ingresa con tus credenciales<br>
                    institucionales para continuar.
                </p>

                @if($errors->any())
                    <div class="alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(($intentosFallidos ?? 0) > 0)
                    <div class="alert-warning" id="panel-intentos">
                        <strong>Intentos fallidos:</strong>
                        {{ $intentosFallidos }} de {{ $intentosMaximos ?? 5 }}
                        @if($bloqueado ?? false)
                            <br><br>
                            <strong>Debes esperar para volver a intentar:</strong>
                            <div class="tiempo-restante" id="tiempo-restante">
                                {{ sprintf('%02d:%02d', intdiv($segundosRestantes, 60), $segundosRestantes % 60) }}
                            </div>
                        @elseif(($intentosFallidos ?? 0) < ($intentosMaximos ?? 5))
                            <br>
                            Te quedan {{ ($intentosMaximos ?? 5) - $intentosFallidos }}
                            {{ (($intentosMaximos ?? 5) - $intentosFallidos) === 1 ? 'intento' : 'intentos' }}
                            antes del bloqueo de 5 minutos.
                        @endif
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" id="form-login">
                    @csrf

                    <div class="input-group">
                        <span class="input-icon">👤</span>
                        <input 
                            type="email" 
                            name="correo" 
                            id="correo"
                            value="{{ old('correo') }}"
                            placeholder="Usuario"
                            required
                            @if($bloqueado ?? false) disabled @endif
                        >
                    </div>

                    <div class="input-group">
                        <span class="input-icon">🔒</span>
                        <input 
                            type="password" 
                            name="contrasena" 
                            id="contrasena"
                            placeholder="Contraseña"
                            required
                            @if($bloqueado ?? false) disabled @endif
                        >
                        <button type="button" class="toggle-password" onclick="mostrarPassword()">
                            👁
                        </button>
                    </div>

                    <button type="submit" class="btn-login" id="btn-login" @if($bloqueado ?? false) disabled @endif>
                        Iniciar sesión
                    </button>

                    <a href="{{ route('password.forgot') }}" class="forgot-link">
                        ¿Olvidaste tu contraseña?
                    </a>
                </form>

            </div>
        </section>

    </main>

    <script>
        function mostrarPassword() {
            const input = document.getElementById('contrasena');

            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        (function () {
            let segundos = {{ (int) ($segundosRestantes ?? 0) }};
            const bloqueado = {{ ($bloqueado ?? false) ? 'true' : 'false' }};
            const tiempoEl = document.getElementById('tiempo-restante');
            const btnLogin = document.getElementById('btn-login');
            const inputCorreo = document.getElementById('correo');
            const inputContrasena = document.getElementById('contrasena');

            function formatearTiempo(total) {
                const minutos = Math.floor(total / 60);
                const seg = total % 60;
                return String(minutos).padStart(2, '0') + ':' + String(seg).padStart(2, '0');
            }

            function habilitarFormulario() {
                if (btnLogin) btnLogin.disabled = false;
                if (inputCorreo) inputCorreo.disabled = false;
                if (inputContrasena) inputContrasena.disabled = false;
            }

            if (bloqueado && segundos > 0 && tiempoEl) {
                if (inputCorreo) inputCorreo.disabled = true;

                const intervalo = setInterval(function () {
                    segundos--;

                    if (segundos <= 0) {
                        clearInterval(intervalo);
                        tiempoEl.textContent = '00:00';
                        habilitarFormulario();
                        return;
                    }

                    tiempoEl.textContent = formatearTiempo(segundos);
                }, 1000);
            }
        })();
    </script>

</body>
</html>