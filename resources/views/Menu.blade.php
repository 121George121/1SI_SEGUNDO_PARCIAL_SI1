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

/* Sidebar */
aside { width:280px; background:#0b2d6b; color:white; display:flex; flex-direction:column; position:fixed; height:100%; padding:0; }
aside .sidebar-header { display:flex; align-items:center; padding:24px; border-bottom:3px solid #1e3a8a; gap:16px; }
aside .sidebar-header img { width:80px; height:80px; object-fit:contain; }
aside .sidebar-header span { font-size:32px; font-weight:800; background:linear-gradient(to right,rgb(141, 17, 17), #dc2626); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

.menu-modulo { 
    margin-top:16px; 
    padding:0 12px; 
    display:flex; 
    flex-direction:column; 
    gap:8px; 
}

.menu-modulo div { 
    background: rgba(255,255,255,0.15); 
    padding:12px; 
    border-radius:8px; 
    text-align:center; 
    cursor:pointer; 
    transition: background 0.2s; 
    font-weight:bold; 
}

.menu-modulo div:hover { 
    background: rgba(255,255,255,0.3); 
}

.menu-modulo .activo { 
    background:#dc2626; 
}
/* Cerrar sesión */
.logout { margin-top:auto; padding:16px; }
.logout button { width:100%; padding:12px; background:#dc2626; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer; transition: background 0.2s; }
.logout button:hover { background:#b91c1c; }

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
@media(max-width:768px){ aside{width:220px;} main{margin-left:220px;} aside .sidebar-header img{width:60px;height:60px;} aside .sidebar-header span{font-size:24px;} main h1{font-size:28px;} }
@media(max-width:480px){ aside{width:200px;} main{margin-left:200px;} aside .sidebar-header img{width:50px;height:50px;} aside .sidebar-header span{font-size:20px;} main h1{font-size:24px;} }
</style>
</head>
<body>

<aside>
    <div class="sidebar-header">
        <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo">
        <span>CUP FICCT</span>
    </div>

    <div class="menu-modulo">

    <div onclick="window.location='{{ route('menu') }}'"
         class="{{ request()->routeIs('menu') ? 'activo' : '' }}"
         style="cursor:pointer;">
        Dashboard
    </div>

    <div onclick="window.location='{{ route('carreras-cupos.index') }}'"
         class="{{ request()->routeIs('carreras-cupos.*') ? 'activo' : '' }}"
         style="cursor:pointer;">
        Gestión Académica
    </div>

    <div onclick="window.location='{{ route('documentos.index') }}'"
         class="{{ request()->routeIs('documentos.*') ? 'activo' : '' }}"
         style="cursor:pointer;">
        Inscripción y Documentación
    </div>

    <div onclick="window.location='{{ route('usuarios.index') }}'"
         class="{{ request()->routeIs('usuarios.*') ? 'activo' : '' }}"
         style="cursor:pointer;">
        Usuario, Seguridad y Auditoría
    </div>

    <div onclick="window.location='{{ route('aulas.index') }}'"
        class="{{ request()->routeIs('aulas.*') ? 'activo' : '' }}"
        style="cursor:pointer;">
        Logística y Reportes
    </div>

    <div onclick="window.location='{{ route('pagos.index') }}'"
        class="{{ request()->routeIs('pagos.*') ? 'activo' : '' }}"
        style="cursor:pointer;">
        Gestión Financiera
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
    <h1>Bienvenido  {{ Auth::user()->nombre_usuario }}</h1>
    <p>Sistema de Inscripción al CUP Preuniversitario</p>

    <!-- Cards de estadísticas -->
    <div class="cards">
        <div class="card">
            <h2>Postulantes Registrados</h2>
            <p>1,248</p>
            <span>+12% respecto al mes anterior</span>
        </div>
        <div class="card">
            <h2>Inscripciones Activas</h2>
            <p>932</p>
            <span>+8% respecto al mes anterior</span>
        </div>
        <div class="card">
            <h2>Pagos Completados</h2>
            <p>785</p>
            <span>+15% respecto al mes anterior</span>
        </div>
        <div class="card">
            <h2>Grupos Asignados</h2>
            <p>56</p>
            <span>+4% respecto al mes anterior</span>
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
                data:[932,518,414,198],
                backgroundColor:['#1e3a8a','#dc2626','#2563eb','#9ca3af']
            }]
        },
        options:{responsive:true}
    });

    const mesCtx = document.getElementById('mesChart').getContext('2d');
    new Chart(mesCtx, {
        type: 'line',
        data: {
            labels:['Dic','Ene','Feb','Mar','Abr','May'],
            datasets:[{
                label:'Inscripciones',
                data:[180,320,540,760,640,900],
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
