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
        main {
            margin-left: 220px;
            padding: 24px;
        }

        .titulo {
            font-size: 24px;
        }
    }
</style>
</head>

<body>

    @include('components.sidebar')

    <main>
    @yield('content')
    </main>

</body>
</html>