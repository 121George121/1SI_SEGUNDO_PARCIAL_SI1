<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Logística y Recursos</title>

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
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px;
        border-bottom: 3px solid #1e3a8a;
    }

    .sidebar-header img {
        width: 70px;
        height: 70px;
        object-fit: contain;
    }

    .sidebar-header span {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
    }

    .menu-modulo {
        margin-top: 22px;
        padding: 0 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .menu-modulo div {
        background: rgba(255,255,255,0.15);
        padding: 13px;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: background 0.2s;
        font-weight: bold;
    }

    .menu-modulo div:hover {
        background: rgba(255,255,255,0.30);
    }

    .menu-modulo .activo {
        background: #dc2626;
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
    }

    .logout button:hover {
        background: #b91c1c;
    }

    main {
        margin-left: 280px;
        flex: 1;
        padding: 34px;
    }

    .titulo {
        font-size: 36px;
        color: #0b2d6b;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .subtitulo {
        font-size: 18px;
        color: #333;
        margin-bottom: 24px;
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
        font-size: 22px;
        margin-bottom: 10px;
    }

    .card p {
        color: #555;
        font-size: 15px;
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

        .sidebar-header img {
            width: 55px;
            height: 55px;
        }

        .sidebar-header span {
            font-size: 19px;
        }

        .titulo {
            font-size: 28px;
        }
    }

    @media (max-width: 480px) {
        aside {
            width: 190px;
        }

        main {
            margin-left: 190px;
            padding: 18px;
        }

        .sidebar-header {
            flex-direction: column;
            text-align: center;
        }

        .menu-modulo div {
            font-size: 13px;
            padding: 10px;
        }

        .titulo {
            font-size: 23px;
        }
    }
</style>
</head>

<body>

    <aside>
        <div class="sidebar-header">
            <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo">
            <span>Logística<br>y Recursos</span>
        </div>

        <div class="menu-modulo">

            <div onclick="window.location='{{ route('menu') }}'">
                Volver al Dashboard
            </div>

            <div onclick="window.location='{{ route('aulas.index') }}'"
                class="{{ request()->routeIs('aulas.*') ? 'activo' : '' }}" style="cursor:pointer;">
                CU08 - Gestionar Aulas
            </div>
            <div onclick="window.location='{{ route('docentes.index') }}'"
                class="{{ request()->routeIs('docentes.*') ? 'activo' : '' }}"
                style="cursor:pointer;">
                CU09 - Gestionar Docentes
            </div>
            <div onclick="window.location='{{ route('reportes.index') }}'"
                class="{{ request()->routeIs('reportes.*') ? 'activo' : '' }}"
                style="cursor:pointer;">
                CU18 - Generar Reportes
            </div>

        </div>

        <div class="logout">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <main>
        @hasSection('content')
            @yield('content')
        @else
            <h1 class="titulo">Logística y Recursos</h1>
            <p class="subtitulo">Seleccione un módulo para administrar los recursos disponibles del CUP FICCT.</p>

            <div class="cards">
                <div class="card" onclick="window.location='{{ route('aulas.index') }}'">
                    <h3>Gestionar Aulas</h3>
                    <p>
                        Registrar aulas, editar datos, asignar capacidad y consultar disponibilidad.
                    </p>
                </div>
                <div class="card" onclick="window.location='{{ route('reportes.index') }}'">
                    <h3>Generar Reportes</h3>
                    <p>
                        Crear reportes estadísticos y ranking de postulantes, grupos, cupos y resultados.
                    </p>
                </div>
            </div>
        @endif
    </main>

</body>
</html>