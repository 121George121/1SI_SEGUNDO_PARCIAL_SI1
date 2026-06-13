<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard CUP - Preuniversitario</title>
<!-- Google Fonts Outfit -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* Reset & Typography Base */
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Outfit', sans-serif; 
    }
    body { 
        background: #f8fafc; /* Light Slate 50 */
        display: flex; 
        min-height: 100vh; 
        color: #1e293b;
    }
    
    /* Layout styling aligning with dynamic sidebar state */
    main { 
        margin-left: 280px; 
        flex: 1; 
        padding: 40px; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        min-height: 100vh;
        background: #f8fafc;
    }
    
    /* Welcome Header Banner with premium radial glow overlay */
    .welcome-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
        border-radius: 20px;
        padding: 36px 40px;
        color: #ffffff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        margin-bottom: 36px;
        position: relative;
        overflow: hidden;
    }
    
    .welcome-banner::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(239, 68, 68, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    
    .welcome-banner h1 {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
        color: #ffffff;
    }
    
    .welcome-banner p {
        font-size: 16px;
        color: #93c5fd; /* Soft Blue */
        font-weight: 500;
        margin: 0;
    }
    
    .welcome-banner .tag-system {
        display: inline-block;
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 14px;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    
    /* Statistics Cards Grid System */
    .cards-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
        gap: 24px; 
        margin-bottom: 36px; 
    }
    
    .stat-card { 
        background: #ffffff; 
        padding: 24px; 
        border-radius: 16px; 
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03); 
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease; 
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover { 
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border-color: #e2e8f0;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #ef4444; /* Crimson Accent border on hover */
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stat-card:hover::before {
        opacity: 1;
    }
    
    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .stat-card-title { 
        font-size: 13px; 
        color: #64748b; /* Slate 500 */
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3a8a;
    }
    
    .stat-card:hover .stat-card-icon {
        background: rgba(30, 58, 138, 0.08);
        color: #ef4444;
    }
    
    .stat-card-value { 
        font-size: 30px; 
        font-weight: 800; 
        color: #0f172a; /* Slate 900 */
        margin-bottom: 8px;
        line-height: 1.1;
    }
    
    .stat-card-change { 
        font-size: 13px; 
        font-weight: 650;
        color: #10b981; /* Default success green color */
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .stat-card-change.neutral {
        color: #64748b;
    }
    
    /* Charts layout block */
    .charts-grid { 
        display: grid; 
        grid-template-columns: 1fr 2fr; 
        gap: 24px; 
    }
    
    @media(max-width: 1024px) { 
        .charts-grid { 
            grid-template-columns: 1fr; 
        } 
    }
    
    .chart-container { 
        background: #ffffff; 
        padding: 28px; 
        border-radius: 16px; 
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03); 
        border: 1px solid #f1f5f9;
    }
    
    .chart-container h3 { 
        font-size: 17px; 
        font-weight: 750; 
        color: #1e3a8a;
        margin-bottom: 24px; 
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Responsive margins in coordination with collapsed menu drawer */
    @media(max-width: 768px) { 
        main { margin-left: 0; padding: 24px; } 
        .welcome-banner { padding: 28px; }
        .welcome-banner h1 { font-size: 26px; }
    }
</style>
</head>
<body>

@include('components.sidebar')

<main>
    <!-- Success and Error alerts styled cohesively -->
    @if(session('success'))
        <div style="margin-bottom: 28px; padding: 16px 20px; background: #ecfdf5; color: #065f46; border-radius: 12px; font-weight: 600; border-left: 5px solid #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08); font-size: 14.5px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom: 28px; padding: 16px 20px; background: #fef2f2; color: #991b1b; border-radius: 12px; font-weight: 600; border-left: 5px solid #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08); font-size: 14.5px;">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Welcome section -->
    <div class="welcome-banner">
        <span class="tag-system">CUP Preuniversitario</span>
        <h1>Bienvenido, {{ Auth::user()->persona ? Auth::user()->persona->nombre . ' ' . Auth::user()->persona->apellido : Auth::user()->nombre_usuario }}</h1>
        
        @php
            $rolesMap = [
                'tipo_Superadministrador' => 'Superadministrador',
                'tipo_Administrador' => 'Administrador',
                'tipo_Docente' => 'Docente',
                'tipo_Postulante' => 'Postulante',
            ];
            $userRoles = [];
            $persona = Auth::user()->persona;
            if ($persona) {
                foreach ($rolesMap as $col => $name) {
                    if ($persona->{$col}) {
                        $userRoles[] = $name;
                    }
                }
            }
            $rolesTexto = !empty($userRoles) ? implode(', ', $userRoles) : 'Sin Rol';
        @endphp
        
        <div style="margin-top: 8px; margin-bottom: 20px; display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.08); padding: 6px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12);">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span style="font-size: 12.5px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5px;">Rol: {{ $rolesTexto }}</span>
        </div>
        
        <p>Has ingresado al panel de administración del CUP FICCT. Administra inscripciones, pagos, y planificación académica.</p>
    </div>

    <!-- Statistics Grid -->
    <div class="cards-grid">
        <!-- Card 1: Postulantes -->
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Postulantes Registrados</span>
                <div class="stat-card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div>
                <div class="stat-card-value">{{ $postulantesTotal }}</div>
                <div class="stat-card-change">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    <span>{{ $postulantesCambioTexto }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Inscripciones -->
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Inscripciones Activas</span>
                <div class="stat-card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <div>
                <div class="stat-card-value">{{ $inscripcionesTotal }}</div>
                <div class="stat-card-change">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    <span>{{ $inscripcionesCambioTexto }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Pagos -->
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Pagos Completados</span>
                <div class="stat-card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
            </div>
            <div>
                <div class="stat-card-value">{{ $pagosTotal }}</div>
                <div class="stat-card-change">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    <span>{{ $pagosCambioTexto }}</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Grupos -->
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Grupos Asignados</span>
                <div class="stat-card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <div>
                <div class="stat-card-value">{{ $gruposAsignadosTotal }}</div>
                <div class="stat-card-change neutral">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $grupoCambioTexto }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <div class="chart-container">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                <span>Inscripciones por Estado</span>
            </h3>
            <div style="position: relative; height: 250px; display: flex; align-items: center; justify-content: center;">
                <canvas id="estadoChart"></canvas>
            </div>
        </div>

        <div class="chart-container">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Inscripciones por Semestre</span>
            </h3>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="mesChart"></canvas>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Doughnut Chart
    const estadoCtx = document.getElementById('estadoChart').getContext('2d');
    new Chart(estadoCtx, {
        type: 'doughnut',
        data: {
            labels: ['Inscritos', 'En Proceso', 'Docs Pendientes', 'Observados'],
            datasets: [{
                data: [
                    {{ $cantInscritos }},
                    {{ $cantEnProceso }},
                    {{ $cantPendientes }},
                    {{ $cantObservados }}
                ],
                backgroundColor: ['#1e3a8a', '#ef4444', '#3b82f6', '#94a3b8'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 16,
                        font: {
                            family: 'Outfit',
                            size: 12,
                            weight: '500'
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Line Chart
    const mesCtx = document.getElementById('mesChart').getContext('2d');
    new Chart(mesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartMesLabels) !!},
            datasets: [{
                label: 'Inscripciones',
                data: {!! json_encode($chartMesData) !!},
                borderColor: '#ef4444', /* Crimson red line */
                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#ef4444',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        font: {
                            family: 'Outfit',
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Outfit',
                            size: 11
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>
