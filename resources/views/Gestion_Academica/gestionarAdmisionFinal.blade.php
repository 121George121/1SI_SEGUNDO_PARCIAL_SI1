@extends('Gestion_Academica.Menu')

@section('title', 'Gestionar Admisión Final')

@section('content')

<h1 class="titulo">CU17 - Gestionar Admisión Final</h1>
<p class="subtitulo">Determinar la aprobación o rechazo de los postulantes según resultados y cupos disponibles.</p>

<style>
    .card-box {
        background: #fff;
        padding: 22px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 6px;
    }

    .form-group input,
    .form-group select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
    }

    .btn {
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-primary { background: #0b2d6b; color: white; }
    .btn-primary:hover { background: #1e3a8a; }

    .btn-success { background: #16a34a; color: white; }
    .btn-success:hover { background: #15803d; }

    .btn-danger { background: #dc2626; color: white; }
    .btn-danger:hover { background: #b91c1c; }

    .btn-secondary { background: #e5e7eb; color: #374151; }
    .btn-secondary:hover { background: #d1d5db; }

    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }

    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1e40af; }

    .search-select {
        position: relative;
    }

    .search-input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        width: 100%;
    }

    .search-options {
        display: none;
        position: absolute;
        top: 100%;
        margin-top: 6px;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-radius: 8px;
        max-height: 180px;
        overflow-y: auto;
        z-index: 999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .search-options.activo {
        display: block;
    }

    .search-option {
        padding: 10px;
        cursor: pointer;
        color: #333;
    }

    .search-option:hover {
        background: #e5e7eb;
    }

    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-card h3 {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 6px;
    }

    .stat-card p {
        font-size: 24px;
        font-weight: bold;
        color: #0b2d6b;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th {
        background-color: #0b2d6b;
        color: white;
        padding: 10px;
        text-align: left;
        font-size: 14px;
    }

    td {
        padding: 10px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }

    tr:hover {
        background-color: #f8fafc;
    }

    .tabs {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 16px;
    }

    .tab-btn {
        padding: 10px 16px;
        cursor: pointer;
        font-weight: bold;
        color: #64748b;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
    }

    .tab-btn.active {
        color: #0b2d6b;
        border-bottom: 2px solid #0b2d6b;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-weight: bold;
    }

    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error { background: #fee2e2; color: #991b1b; }
</style>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Selección de Gestión Académica</h2>
    
    <form method="GET" action="{{ route('admision.index') }}" id="form-gestion-select" class="form-grid" style="grid-template-columns: 1fr auto; align-items: end;">
        <div class="form-group search-select" data-search-select>
            <label>Gestión Académica</label>
            
            <input type="text" class="search-input" placeholder="Buscar gestión..." autocomplete="off" value="{{ $idGestion ? $gestiones->firstWhere('Id_gestion', $idGestion)->anio . ' - ' . $gestiones->firstWhere('Id_gestion', $idGestion)->periodo : '' }}" required>
            <input type="hidden" name="Id_gestion" value="{{ $idGestion ?? '' }}">
            
            <div class="search-options">
                @foreach($gestiones as $gestion)
                    <div class="search-option" data-value="{{ $gestion->Id_gestion }}">
                        {{ $gestion->anio }} - {{ $gestion->periodo }}
                    </div>
                @endforeach
            </div>
        </div>
        
        <div>
            <button type="submit" class="btn btn-primary" style="height: 40px;">Cargar Información</button>
        </div>
    </form>
</div>

@if($idGestion)
    <div class="card-box">
        <h2 style="color:#0b2d6b; margin-bottom:16px;">Consolidación de la Admisión</h2>
        
        <div class="grid-stats">
            <div class="stat-card">
                <h3>Postulantes Totales</h3>
                <p>{{ $totalPostulantes }}</p>
            </div>
            <div class="stat-card">
                <h3>Postulantes con Notas Listas</h3>
                <p>{{ $totalPostulantesConNotas }}</p>
            </div>
            <div class="stat-card">
                <h3>Postulantes Admitidos</h3>
                <p style="color: #16a34a;">{{ count($admitidos) }}</p>
            </div>
            <div class="stat-card">
                <h3>Postulantes Rechazados</h3>
                <p style="color: #dc2626;">{{ count($rechazados) }}</p>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
            @if($totalPostulantesConNotas > 0)
                <form method="POST" action="{{ route('admision.consolidar') }}" onsubmit="return confirm('¿Seguro que deseas consolidar los resultados finales? Esto ordenará a los postulantes por promedio académico y asignará cupos disponibles en orden de mérito.');">
                    @csrf
                    <input type="hidden" name="Id_gestion" value="{{ $idGestion }}">
                    <button type="submit" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        {{ count($admitidos) > 0 || count($rechazados) > 0 ? 'Volver a Ejecutar Admisión' : 'Ejecutar Admisión por Mérito' }}
                    </button>
                </form>
            @else
                <button class="btn btn-secondary" disabled title="Debe haber postulantes con notas finales en resultadoacademico para consolidar.">
                    Ejecutar Admisión por Mérito (Notas pendientes)
                </button>
            @endif

            @if(count($admitidos) > 0 || count($rechazados) > 0)
                <form method="POST" action="{{ route('admision.notificar') }}" onsubmit="return confirm('¿Seguro que deseas notificar los resultados por correo a todos los postulantes de esta gestión?');">
                    @csrf
                    <input type="hidden" name="Id_gestion" value="{{ $idGestion }}">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        Notificar Resultados Finales
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card-box">
        <h2 style="color:#0b2d6b; margin-bottom:16px;">Ocupación de Cupos por Carrera</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Cupos Totales</th>
                        <th>Cupos Ocupados</th>
                        <th>Disponibles</th>
                        <th>Estado de Vacantes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carrerasCupos as $cc)
                        @php
                            $disponibles = $cc->cantidad_cupos - $cc->cupos_ocupados;
                        @endphp
                        <tr>
                            <td><strong>{{ $cc->nombre_carrera }}</strong></td>
                            <td>{{ $cc->cantidad_cupos }}</td>
                            <td>{{ $cc->cupos_ocupados }}</td>
                            <td><strong>{{ $disponibles }}</strong></td>
                            <td>
                                @if($disponibles <= 0)
                                    <span class="badge badge-danger">Lleno</span>
                                @else
                                    <span class="badge badge-success">Vacantes disponibles</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #666; padding: 20px;">
                                No se encontraron cupos definidos por carrera para esta gestión.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(count($admitidos) > 0 || count($rechazados) > 0)
        <div class="card-box">
            <div class="tabs">
                <div class="tab-btn active" data-target="tab-admitidos">Postulantes Admitidos ({{ count($admitidos) }})</div>
                <div class="tab-btn" data-target="tab-rechazados">Postulantes Rechazados ({{ count($rechazados) }})</div>
            </div>

            <div id="tab-admitidos" class="tab-content active">
                <div style="margin-bottom:12px;">
                    <input type="text" id="buscarAdmitidos" placeholder="Buscar admitido..." style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;">
                </div>
                <div style="overflow-x: auto;">
                    <table id="tablaAdmitidos">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">Merito</th>
                                <th>CI</th>
                                <th>Postulante</th>
                                <th>Carrera Asignada</th>
                                <th style="text-align: center;">Promedio Final</th>
                                <th style="text-align: center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admitidos as $item)
                                <tr>
                                    <td style="font-weight: bold; text-align: center;">#{{ $item->puesto_merito }}</td>
                                    <td>{{ $item->ci }}</td>
                                    <td>{{ $item->nombre_completo }}</td>
                                    <td><strong>{{ $item->nombre_carrera }}</strong></td>
                                    <td style="text-align: center; font-weight: bold; color: #16a34a;">{{ number_format($item->promedio_final, 2) }}</td>
                                    <td style="text-align: center;"><span class="badge badge-success">Admitido</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="tab-rechazados" class="tab-content">
                <div style="margin-bottom:12px;">
                    <input type="text" id="buscarRechazados" placeholder="Buscar rechazado..." style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;">
                </div>
                <div style="overflow-x: auto;">
                    <table id="tablaRechazados">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th>CI</th>
                                <th>Postulante</th>
                                <th style="text-align: center;">Promedio Final</th>
                                <th>Motivo</th>
                                <th style="text-align: center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rechazados as $index => $item)
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>{{ $item->ci }}</td>
                                    <td>{{ $item->nombre_completo }}</td>
                                    <td style="text-align: center; font-weight: bold; color: #dc2626;">{{ number_format($item->promedio_final, 2) }}</td>
                                    <td><span style="color: #666; font-style: italic;">Sin cupo disponible en sus opciones preferidas</span></td>
                                    <td style="text-align: center;"><span class="badge badge-danger">Rechazado</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endif

<script>
document.querySelectorAll('[data-search-select]').forEach(function (contenedor) {
    const input = contenedor.querySelector('.search-input');
    const hidden = contenedor.querySelector('input[type="hidden"]');
    const opciones = contenedor.querySelector('.search-options');
    const items = contenedor.querySelectorAll('.search-option');

    input.addEventListener('focus', function () {
        opciones.classList.add('activo');
    });

    input.addEventListener('input', function () {
        const texto = input.value.toLowerCase();
        hidden.value = '';
        items.forEach(function (item) {
            const coincide = item.textContent.toLowerCase().includes(texto);
            item.style.display = coincide ? 'block' : 'none';
        });
        opciones.classList.add('activo');
    });

    items.forEach(function (item) {
        item.addEventListener('click', function () {
            input.value = item.textContent.trim();
            hidden.value = item.dataset.value;
            opciones.classList.remove('activo');
            // Auto submit to load the page
            input.closest('form').submit();
        });
    });

    document.addEventListener('click', function (e) {
        if (!contenedor.contains(e.target)) {
            opciones.classList.remove('activo');
        }
    });
});

// Tab switching logic
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        this.classList.add('active');
        document.getElementById(this.dataset.target).classList.add('active');
    });
});

// Search filter logic
function setupSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('keyup', function() {
        const text = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(text) ? '' : 'none';
        });
    });
}
setupSearch('buscarAdmitidos', 'tablaAdmitidos');
setupSearch('buscarRechazados', 'tablaRechazados');
</script>

@endsection
