<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Usuario, Seguridad y Auditoria')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { background: #f5f7fb; display: flex; min-height: 100vh; }
        aside { width: 280px; background: #0b2d6b; color: white; display: flex; flex-direction: column; position: fixed; height: 100%; }
        aside .sidebar-header { display: flex; align-items: center; padding: 24px; border-bottom: 3px solid #1e3a8a; gap: 16px; }
        aside .sidebar-header img { width: 60px; height: 60px; object-fit: contain; }
        aside .sidebar-header span { font-size: 20px; font-weight: 800; }
        .menu-modulo { margin-top: 16px; padding: 0 12px; display: flex; flex-direction: column; gap: 8px; }
        .menu-modulo a { background: rgba(255,255,255,0.15); padding: 12px; border-radius: 8px; text-align: center; color: white; text-decoration: none; font-weight: bold; }
        .menu-modulo a:hover, .menu-modulo a.activo { background: #dc2626; }
        .logout { margin-top: auto; padding: 16px; }
        .logout button { width: 100%; padding: 12px; background: #dc2626; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
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
<aside>
    <div class="sidebar-header">
        <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo">
        <span>Seguridad y Auditoria</span>
    </div>
    <nav class="menu-modulo">
        <a href="{{ route('menu') }}">Volver al Dashboard</a>
        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">CU2 - Usuarios y Roles</a>
    </nav>
    <div class="logout">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Cerrar sesion</button>
        </form>
    </div>
</aside>
<main>
    @yield('content')
</main>
</body>
</html>
