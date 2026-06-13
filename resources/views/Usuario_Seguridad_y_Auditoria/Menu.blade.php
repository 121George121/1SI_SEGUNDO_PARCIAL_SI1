<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Usuario, Seguridad y Auditoria')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { background: #f5f7fb; display: flex; min-height: 100vh; }
        main { margin-left: 280px; flex: 1; padding: 24px; }
        .btn { display: inline-block; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 14px; border: none; cursor: pointer; }
        .btn-primary { background: #082f6f; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #111; }
        .btn-danger { background: #dc2626; color: #fff; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 14px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #ffe3e3; color: #b00020; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; font-size: 14px; }
        th { background: #e5e7eb; }
    </style>
</head>
<body>
@include('components.sidebar')
<main>
    @yield('content')
</main>
</body>
</html>
