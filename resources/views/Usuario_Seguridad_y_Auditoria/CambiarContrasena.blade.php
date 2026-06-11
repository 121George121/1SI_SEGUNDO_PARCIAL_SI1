<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer nueva contraseña</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6fb; margin: 0; }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #fff; width: 100%; max-width: 460px; padding: 28px; border-radius: 8px; box-shadow: 0 10px 24px rgba(0,0,0,.12); }
        input { width: 100%; height: 44px; border: 1px solid #dcdcdc; border-radius: 6px; padding: 0 12px; margin: 8px 0 14px; box-sizing: border-box; outline: none; transition: border-color 0.2s; }
        input:focus { border-color: #082f6f; }
        button, .btn { width: 100%; height: 44px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; }
        .btn-primary { background: #082f6f; color: #fff; margin-top: 12px; }
        .btn-secondary { background: #fff; color: #082f6f; border: 2px solid #082f6f; margin-top: 10px; display: block; text-align: center; line-height: 40px; text-decoration: none; box-sizing: border-box; }
        .error { background: #ffe3e3; color: #b00020; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .ok { background: #e5f8e8; color: #168038; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .hint { color: #666; font-size: 13px; margin-bottom: 16px; line-height: 1.4; }
        
        .requirements { list-style: none; padding: 0; margin: 10px 0 16px; font-size: 13px; }
        .requirements li { margin-bottom: 6px; display: flex; align-items: center; gap: 6px; transition: color 0.2s; }
        .req-invalid { color: #dc2626; }
        .req-valid { color: #16a34a; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h2>Nueva contraseña</h2>
            <p class="hint">Ingresa tu nueva contraseña cumpliendo con los requisitos de seguridad.</p>

            @if(session('success'))
                <div class="ok">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.reset.update') }}" id="formRestablecer">
                @csrf

                <label for="contrasena" style="font-weight: bold; color: #0b2d6b; font-size: 14px;">Nueva contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required autofocus placeholder="Ingresa tu contraseña">

                <!-- Lista de Requerimientos de Contraseña -->
                <ul class="requirements">
                    <li id="req-length" class="req-invalid">❌ Mínimo 8 caracteres</li>
                    <li id="req-upper" class="req-invalid">❌ Al menos una letra mayúscula</li>
                    <li id="req-lower" class="req-invalid">❌ Al menos una letra minúscula</li>
                    <li id="req-number" class="req-invalid">❌ Al menos un número</li>
                    <li id="req-special" class="req-invalid">❌ Al menos un carácter especial (ej: @, $, !, %, *, #, etc.)</li>
                </ul>

                <label for="contrasena_confirmation" style="font-weight: bold; color: #0b2d6b; font-size: 14px;">Confirmar contraseña</label>
                <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" required placeholder="Confirma tu contraseña">
                <span id="match-error" style="color: #dc2626; font-size: 12px; display: none; margin-bottom: 10px;">Las contraseñas no coinciden.</span>

                <button type="submit" class="btn btn-primary" id="btnGuardar" disabled>Guardar nueva contraseña</button>
            </form>

            <a href="{{ route('login') }}" class="btn btn-secondary" style="margin-top:15px;">Volver al login</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const password = document.getElementById('contrasena');
            const confirmPassword = document.getElementById('contrasena_confirmation');
            const btnGuardar = document.getElementById('btnGuardar');
            const matchError = document.getElementById('match-error');

            const reqLength = document.getElementById('req-length');
            const reqUpper = document.getElementById('req-upper');
            const reqLower = document.getElementById('req-lower');
            const reqNumber = document.getElementById('req-number');
            const reqSpecial = document.getElementById('req-special');

            function validador() {
                const val = password.value;
                let valid = true;

                // 1. Longitud
                if (val.length >= 8) {
                    reqLength.className = 'req-valid';
                    reqLength.innerHTML = '✔ Mínimo 8 caracteres';
                } else {
                    reqLength.className = 'req-invalid';
                    reqLength.innerHTML = '❌ Mínimo 8 caracteres';
                    valid = false;
                }

                // 2. Mayúscula
                if (/[A-Z]/.test(val)) {
                    reqUpper.className = 'req-valid';
                    reqUpper.innerHTML = '✔ Al menos una letra mayúscula';
                } else {
                    reqUpper.className = 'req-invalid';
                    reqUpper.innerHTML = '❌ Al menos una letra mayúscula';
                    valid = false;
                }

                // 3. Minúscula
                if (/[a-z]/.test(val)) {
                    reqLower.className = 'req-valid';
                    reqLower.innerHTML = '✔ Al menos una letra minúscula';
                } else {
                    reqLower.className = 'req-invalid';
                    reqLower.innerHTML = '❌ Al menos una letra minúscula';
                    valid = false;
                }

                // 4. Número
                if (/[0-9]/.test(val)) {
                    reqNumber.className = 'req-valid';
                    reqNumber.innerHTML = '✔ Al menos un número';
                } else {
                    reqNumber.className = 'req-invalid';
                    reqNumber.innerHTML = '❌ Al menos un número';
                    valid = false;
                }

                // 5. Carácter especial
                if (/[^A-Za-z0-9]/.test(val)) {
                    reqSpecial.className = 'req-valid';
                    reqSpecial.innerHTML = '✔ Al menos un carácter especial';
                } else {
                    reqSpecial.className = 'req-invalid';
                    reqSpecial.innerHTML = '❌ Al menos un carácter especial (ej: @, $, !, %, *, #, etc.)';
                    valid = false;
                }

                // 6. Confirmación coincide
                const coinciden = (val === confirmPassword.value);
                if (confirmPassword.value) {
                    matchError.style.display = coinciden ? 'none' : 'block';
                } else {
                    matchError.style.display = 'none';
                }

                btnGuardar.disabled = !(valid && coinciden && val.length > 0);
            }

            password.addEventListener('input', validador);
            confirmPassword.addEventListener('input', validador);
        });
    </script>
</body>
</html>
