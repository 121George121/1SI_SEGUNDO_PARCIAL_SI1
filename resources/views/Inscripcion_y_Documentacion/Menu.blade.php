<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inscripcion y Documentacion')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { background: #f5f7fb; display: flex; min-height: 100vh; }
        aside { width: 280px; background: #0b2d6b; color: white; display: flex; flex-direction: column; position: fixed; height: 100%; }
        aside .sidebar-header { display: flex; align-items: center; padding: 24px; border-bottom: 3px solid #1e3a8a; gap: 16px; }
        aside .sidebar-header img { width: 60px; height: 60px; object-fit: contain; }
        aside .sidebar-header span { font-size: 18px; font-weight: 800; line-height: 1.2; }
        .menu-modulo { margin-top: 16px; padding: 0 12px; display: flex; flex-direction: column; gap: 8px; }
        .menu-modulo a { background: rgba(255,255,255,0.15); padding: 12px; border-radius: 8px; text-align: center; color: white; text-decoration: none; font-weight: bold; }
        .menu-modulo a:hover, .menu-modulo a.activo { background: #dc2626; }
        .menu-modulo a.deshabilitado {
            background: rgba(120, 120, 120, 0.2) !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            opacity: 0.6;
        }
        .logout { margin-top: auto; padding: 16px; }
        .logout button { width: 100%; padding: 12px; background: #dc2626; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        main { margin-left: 280px; flex: 1; padding: 24px; }
    </style>
</head>
<body>
<aside>
    <div class="sidebar-header">
        <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo">
        <span>Inscripcion y Documentacion</span>
    </div>
    <nav class="menu-modulo">
        <a href="{{ route('menu') }}">Volver al Dashboard</a>
        <a href="{{ route('documentos.index') }}" class="{{ request()->routeIs('documentos.*') ? 'activo' : '' }}">CU4 - Gestionar Documentos</a>
        
        @php
            $hasGestiones = \Illuminate\Support\Facades\DB::table('gestion')->exists();
            $hasCarreras = \Illuminate\Support\Facades\DB::table('carrera')->exists();
            $hasModalidades = \Illuminate\Support\Facades\DB::table('modalidad')->exists();
            $hasTurnos = \Illuminate\Support\Facades\DB::table('turno')->exists();
            $disableInscripcion = !$hasGestiones || !$hasCarreras || !$hasModalidades || !$hasTurnos;
        @endphp

        <a href="{{ $disableInscripcion ? '#' : route('inscripcion.index') }}"
           class="{{ request()->routeIs('inscripcion.*') ? 'activo' : '' }} {{ $disableInscripcion ? 'deshabilitado' : '' }}"
           title="{{ $disableInscripcion ? 'Requiere registrar previamente: Gestiones, Carreras, Modalidades y Turnos.' : '' }}">
            CU03 - Gestionar Inscripción
        </a>
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
