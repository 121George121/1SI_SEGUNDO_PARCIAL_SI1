<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contrasena</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6fb; margin: 0; }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #fff; width: 100%; max-width: 430px; padding: 28px; border-radius: 8px; box-shadow: 0 10px 24px rgba(0,0,0,.12); }
        input { width: 100%; height: 44px; border: 1px solid #dcdcdc; border-radius: 6px; padding: 0 12px; margin: 10px 0 16px; }
        button { width: 100%; height: 44px; border: none; border-radius: 6px; background: #082f6f; color: #fff; cursor: pointer; }
        .error { background: #ffe3e3; color: #b00020; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .back { display: inline-block; margin-top: 16px; color: #082f6f; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h2>Recuperar contraseña</h2>
            <p>Te enviaremos un codigo de 6 digitos a tu correo.</p>

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.forgot.send') }}">
                @csrf
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" value="{{ old('correo') }}" required>
                <button type="submit">Enviar codigo</button>
            </form>

            <a class="back" href="{{ route('login') }}">Volver al login</a>
        </div>
    </div>
</body>
</html>
