<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard CUP</title>
<style>
/* Reset básico */
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial, Helvetica, sans-serif; }
body { background:#f5f7fb; display:flex; min-height:100vh; }

/* Contenido principal */
main { margin-left:280px; flex:1; padding:24px; }
main h1 { font-size:36px; font-weight:800; color:#1e3a8a; margin-bottom:12px; }
main p { font-size:18px; color:#333; margin-bottom:24px; }

/* Cards dashboard */
.cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:24px; }
.card { background:white; padding:16px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center; }
.card h2 { font-size:16px; color:#777; margin-bottom:8px; }
.card p { font-size:24px; font-weight:bold; margin-bottom:4px; }
.card span { font-size:14px; color:green; }

/* Charts placeholders */
.charts { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:16px; }
.chart { background:white; padding:16px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.chart h3 { font-weight:bold; margin-bottom:12px; }

/* Responsivo */
@media(max-width:768px){ main{margin-left:220px;} main h1{font-size:28px;} }
@media(max-width:480px){ main{margin-left:200px;} main h1{font-size:24px;} }
</style>
</head>
<body>

@include('components.sidebar')

<main>
    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 14px; background: #d1fae5; color: #065f46; border-radius: 8px; font-weight: bold; border-left: 5px solid #10b981;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom: 20px; padding: 14px; background: #fee2fee2; background-color: #fee2e2; color: #991b1b; border-radius: 8px; font-weight: bold; border-left: 5px solid #ef4444;">
            {{ $errors->first() }}
        </div>
    @endif

    <h1>Bienvenido {{ Auth::user()->persona ? Auth::user()->persona->nombre . ' ' . Auth::user()->persona->apellido : Auth::user()->nombre_usuario }}</h1>
    <p>Sistema de Inscripción al CUP Preuniversitario</p>

    <!-- Cards de estadísticas -->
    <div class="cards">
        <div class="card">
            <h2>Postulantes Registrados</h2>
            <p>{{ $postulantesTotal }}</p>
            <span>{{ $postulantesCambioTexto }}</span>
        </div>
        <div class="card">
            <h2>Inscripciones Activas</h2>
            <p>{{ $inscripcionesTotal }}</p>
            <span>{{ $inscripcionesCambioTexto }}</span>
        </div>
        <div class="card">
            <h2>Pagos Completados</h2>
            <p>{{ $pagosTotal }}</p>
            <span>{{ $pagosCambioTexto }}</span>
        </div>
        <div class="card">
            <h2>Grupos Asignados</h2>
            <p>{{ $gruposAsignadosTotal }}</p>
            <span>{{ $grupoCambioTexto }}</span>
        </div>
    </div>

    <!-- Gráficos placeholders -->
    <div class="charts">
        <div class="chart">
            <h3>Inscripciones por Estado</h3>
            <canvas id="estadoChart" width="100%" height="200"></canvas>
        </div>
        <div class="chart">
            <h3>Inscripciones por Semestre</h3>
            <canvas id="mesChart" width="100%" height="100"></canvas>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const estadoCtx = document.getElementById('estadoChart').getContext('2d');
    new Chart(estadoCtx, {
        type: 'doughnut',
        data: {
            labels:['Inscritos','En Proceso','Documentos Pendientes','Observados'],
            datasets:[{
                data:[
                    {{ $cantInscritos }},
                    {{ $cantEnProceso }},
                    {{ $cantPendientes }},
                    {{ $cantObservados }}
                ],
                backgroundColor:['#1e3a8a','#dc2626','#2563eb','#9ca3af']
            }]
        },
        options:{responsive:true}
    });

    const mesCtx = document.getElementById('mesChart').getContext('2d');
    new Chart(mesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartMesLabels) !!},
            datasets:[{
                label:'Inscripciones',
                data: {!! json_encode($chartMesData) !!},
                borderColor:'#1e3a8a',
                backgroundColor:'rgba(30,58,138,0.2)',
                fill:true,
                tension:0.3
            }]
        },
        options:{responsive:true}
    });
</script>

</body>
</html>
