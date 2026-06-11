<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar código</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6fb; margin: 0; }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #fff; width: 100%; max-width: 430px; padding: 28px; border-radius: 8px; box-shadow: 0 10px 24px rgba(0,0,0,.12); }
        input { width: 100%; height: 44px; border: 1px solid #dcdcdc; border-radius: 6px; padding: 0 12px; margin: 10px 0 16px; box-sizing: border-box; }
        button, .btn { width: 100%; height: 44px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; }
        .btn-primary { background: #082f6f; color: #fff; }
        .btn-secondary { background: #fff; color: #082f6f; border: 2px solid #082f6f; margin-top: 10px; display: block; text-align: center; line-height: 40px; text-decoration: none; box-sizing: border-box; }
        .error { background: #ffe3e3; color: #b00020; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .ok { background: #e5f8e8; color: #168038; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .hint { color: #666; font-size: 13px; margin-bottom: 16px; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h2>Verificar código</h2>
            <p class="hint">Hemos enviado un código de seguridad de 6 dígitos a su correo electrónico. Por favor ingréselo a continuación para continuar.</p>

            @if(session('success'))
                <div class="ok">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.verify.submit') }}">
                @csrf
                <label for="codigo">Código de 6 dígitos</label>
                <input type="text" id="codigo" name="codigo" value="{{ old('codigo') }}" maxlength="6" pattern="[0-9]{6}" required placeholder="Ej: 123456" autofocus>
                <button type="submit" class="btn btn-primary">Verificar código</button>
            </form>

            <form method="POST" action="{{ route('password.resend') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Volver a enviar código</button>
            </form>

            <a class="btn btn-secondary" href="{{ route('login') }}" style="margin-top:10px;">Volver al login</a>
        </div>
    </div>
</body>
</html>
