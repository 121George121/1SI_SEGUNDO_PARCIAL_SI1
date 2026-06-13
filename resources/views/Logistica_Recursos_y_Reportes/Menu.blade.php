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
        main {
            margin-left: 220px;
            padding: 24px;
        }

        .titulo {
            font-size: 28px;
        }
    }

    @media (max-width: 480px) {
        main {
            margin-left: 190px;
            padding: 18px;
        }

        .titulo {
            font-size: 23px;
        }
    }
</style>
</head>

<body>

    @include('components.sidebar')

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