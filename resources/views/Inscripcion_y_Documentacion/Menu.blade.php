<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inscripcion y Documentacion')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { background: #f5f7fb; display: flex; min-height: 100vh; }
        main { margin-left: 280px; flex: 1; padding: 24px; }
    </style>
</head>
<body>
@include('components.sidebar')
<main>
    @yield('content')
</main>
</body>
</html>
