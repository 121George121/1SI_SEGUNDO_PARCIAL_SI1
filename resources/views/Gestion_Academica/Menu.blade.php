<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Gestión Académica')</title>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif;
    }

    body {
        background: #f5f7fb;
        display: flex;
        min-height: 100vh;
    }

    aside {
        width: 280px;
        background: #0b2d6b;
        color: white;
        display: flex;
        flex-direction: column;
        position: fixed;
        height: 100vh;
        padding: 0;
        z-index: 100;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px;
        border-bottom: 3px solid #1e3a8a;
    }

    .sidebar-header img {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }

    .sidebar-header span {
        font-size: 20px;
        font-weight: 800;
        line-height: 1.2;
    }

    .menu-modulo {
        margin-top: 16px;
        padding: 0 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .menu-modulo a {
        background: rgba(255,255,255,0.15);
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        color: white;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.2s;
    }

    .menu-modulo a:hover,
    .menu-modulo a.activo {
        background: #dc2626;
    }

    .menu-modulo a.deshabilitado {
        background: rgba(120, 120, 120, 0.2) !important;
        color: #9ca3af !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        opacity: 0.6;
    }

    .logout {
        margin-top: auto;
        padding: 16px;
    }

    .logout button {
        width: 100%;
        padding: 12px;
        background: #dc2626;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
    }

    .logout button:hover {
        background: #b91c1c;
    }

    main {
        margin-left: 280px;
        flex: 1;
        padding: 24px;
    }

    .titulo {
        font-size: 30px;
        color: #0b2d6b;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .subtitulo {
        font-size: 16px;
        color: #555;
        margin-bottom: 24px;
    }

    /* Common UI components aligning with other modules */
    .btn {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: background 0.2s, opacity 0.2s;
        text-align: center;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .btn-primary { background: #082f6f; color: #fff; }
    .btn-secondary { background: #e5e7eb; color: #111; }
    .btn-danger { background: #dc2626; color: #fff; }
    .btn-success { background: #16a34a; color: #fff; }
    .btn-warning { background: #f59e0b; color: #fff; }

    .alert {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 14px;
    }
    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error { background: #ffe3e3; color: #b00020; }

    /* Modern clean table matching security/documentation */
    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
        margin-top: 16px;
        margin-bottom: 24px;
    }

    th, td {
        border: 1px solid #e5e7eb;
        padding: 12px;
        text-align: left;
        font-size: 14px;
    }

    th {
        background: #e5e7eb;
        color: #1e3a8a;
        font-weight: bold;
    }

    tr:hover {
        background-color: #f9fafb;
    }

    /* Modal dialog styling matching documentos.blade.php */
    .modal-fondo {
        display: none;
        position: fixed;
        z-index: 9999;
        inset: 0;
        background: rgba(0,0,0,0.45);
        justify-content: center;
        align-items: center;
    }

    .modal-fondo.activo {
        display: flex;
    }

    .modal-contenido {
        background: white;
        width: 600px;
        max-width: 95%;
        padding: 24px;
        border-radius: 14px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 10px;
    }

    .modal-header h2 {
        color: #0b2d6b;
        margin: 0;
        font-size: 22px;
    }

    .btn-cerrar {
        background: #dc2626;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 18px;
    }

    .card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 22px rgba(0,0,0,0.12);
    }

    .card h3 {
        color: #0b2d6b;
        font-size: 20px;
        margin-bottom: 10px;
    }

    .card p {
        color: #555;
        font-size: 14px;
        line-height: 1.4;
    }

    @media (max-width: 768px) {
        aside {
            width: 220px;
        }

        main {
            margin-left: 220px;
            padding: 24px;
        }

        .sidebar-header {
            padding: 18px;
            gap: 10px;
        }

        .sidebar-header img {
            width: 50px;
            height: 50px;
        }

        .sidebar-header span {
            font-size: 16px;
        }

        .titulo {
            font-size: 24px;
        }
    }
</style>
</head>

<body>

    <aside>
        <div class="sidebar-header">
            <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo">
            <span>Gestión Académica</span>
        </div>

        <nav class="menu-modulo">
            <a href="{{ route('menu') }}">Volver al Dashboard</a>

            @php
                $hasCarreras = \Illuminate\Support\Facades\DB::table('carrera')->exists();
                $hasGestiones = \Illuminate\Support\Facades\DB::table('gestion')->exists();
                $hasTurnos = \Illuminate\Support\Facades\DB::table('turno')->exists();
                $hasMaterias = \Illuminate\Support\Facades\DB::table('materia')->exists();
                $hasHorarios = \Illuminate\Support\Facades\DB::table('horario')->exists();

                // Check if the 5 required base catalogs are registered
                $disableRest = !$hasCarreras || !$hasGestiones || !$hasTurnos || !$hasMaterias || !$hasHorarios;

                $disableGrupos = $disableRest;
                $disableAsignarPostulantes = $disableRest;
                $disableAsignarDocentes = $disableRest;
                $disableEvaluaciones = $disableRest;
                $disableAdmision = $disableRest;
            @endphp

            <a href="{{ route('carreras-cupos.index') }}"
                class="{{ request()->routeIs('carreras-cupos.*') ? 'activo' : '' }}">
                CU06 - Carreras y Cupos
            </a>

            <a href="{{ $disableGrupos ? '#' : route('grupos.index') }}" 
               class="{{ request()->routeIs('grupos.*') ? 'activo' : '' }} {{ $disableGrupos ? 'deshabilitado' : '' }}"
               title="{{ $disableGrupos ? 'Requiere registrar previamente: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                CU11 - Gestionar Grupos
            </a>

            <a href="{{ $disableAsignarPostulantes ? '#' : route('postulantes-grupos.index') }}" 
               class="{{ request()->routeIs('postulantes-grupos.*') ? 'activo' : '' }} {{ $disableAsignarPostulantes ? 'deshabilitado' : '' }}"
               title="{{ $disableAsignarPostulantes ? 'Requiere registrar previamente: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                CU12 - Asignar Postulantes a Grupos
            </a>

            <a href="{{ route('gestiones.index') }}" class="{{ request()->routeIs('gestiones.*') ? 'activo' : '' }}">
                Gestionar Gestiones
            </a>

            <a href="{{ route('modalidades.index') }}" class="{{ request()->routeIs('modalidades.*') ? 'activo' : '' }}">
                Gestionar Modalidades
            </a>

            <a href="{{ route('turnos.index') }}" class="{{ request()->routeIs('turnos.*') ? 'activo' : '' }}">
                Gestionar Turnos
            </a>

            <a href="{{ route('materias.index') }}" class="{{ request()->routeIs('materias.*') ? 'activo' : '' }}">
                CU14 - Gestionar Materias
            </a>

            <a href="{{ route('horarios.index') }}" class="{{ request()->routeIs('horarios.*') ? 'activo' : '' }}">
                CU14 - Gestionar Horarios
            </a>

            <a href="{{ $disableAsignarDocentes ? '#' : route('asignaciones-docentes.index') }}" 
               class="{{ request()->routeIs('asignaciones-docentes.*') ? 'activo' : '' }} {{ $disableAsignarDocentes ? 'deshabilitado' : '' }}"
               title="{{ $disableAsignarDocentes ? 'Requiere registrar previamente: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                CU15 - Asignar Docentes a Grupos y Materias
            </a>

            <a href="{{ $disableEvaluaciones ? '#' : route('evaluaciones-notas.index') }}" 
               class="{{ request()->routeIs('evaluaciones-notas.*') || request()->routeIs('evaluaciones.*') || request()->routeIs('notas.*') ? 'activo' : '' }} {{ $disableEvaluaciones ? 'deshabilitado' : '' }}"
               title="{{ $disableEvaluaciones ? 'Requiere registrar previamente: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                CU16 - Gestionar Evaluaciones y Notas
            </a>

            <a href="{{ $disableAdmision ? '#' : route('admision.index') }}" 
               class="{{ request()->routeIs('admision.*') ? 'activo' : '' }} {{ $disableAdmision ? 'deshabilitado' : '' }}"
               title="{{ $disableAdmision ? 'Requiere registrar previamente: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                CU17 - Gestionar Admisión Final
            </a>
        </nav>

        <div class="logout">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <main>
    @yield('content')
    </main>

</body>
</html>