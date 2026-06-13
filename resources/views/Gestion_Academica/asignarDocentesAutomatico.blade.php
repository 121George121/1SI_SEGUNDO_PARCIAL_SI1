@extends('Gestion_Academica.Menu')

@section('content')

<h1 class="titulo">Asignación Automática de Docentes</h1>
<p class="subtitulo">Asignar docentes de forma inteligente y distribuir la carga horaria cumpliendo con especialidades, límites de grupos y periodos de descanso.</p>

<style>
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .btn-back {
        background: #0b2d6b;
        color: white;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: bold;
        transition: background 0.2s;
    }

    .btn-back:hover {
        background: #1e3a8a;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        border-left: 6px solid #0b2d6b;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-card.teachers {
        border-left-color: #10b981;
    }

    .stat-card.pending {
        border-left-color: #f59e0b;
    }

    .stat-card h3 {
        color: #555;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .stat-card .value {
        font-size: 36px;
        font-weight: 800;
        color: #0b2d6b;
        margin-bottom: 8px;
    }

    .stat-card .desc {
        font-size: 13px;
        color: #666;
    }

    .rules-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
    }

    .rules-card h3 {
        color: #0b2d6b;
        margin-bottom: 12px;
        font-size: 16px;
    }

    .rules-list {
        margin-left: 20px;
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
    }

    .rules-list li {
        margin-bottom: 8px;
    }

    .form-card {
        background: white;
        padding: 28px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        margin-bottom: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 18px;
    }

    .form-group label {
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 8px;
    }

    .form-group select {
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        transition: border 0.2s;
        background-color: white;
    }

    .form-group select:focus {
        border-color: #0b2d6b;
        outline: none;
    }

    .btn-submit {
        background: #10b981;
        color: white;
        border: none;
        padding: 14px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
        text-align: center;
        display: block;
        width: 100%;
    }

    .btn-submit:hover {
        background: #059669;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        padding: 14px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-weight: 500;
    }
</style>

<div class="header-actions">
    <a href="{{ route('asignaciones-docentes.index') }}" class="btn-back">
        &larr; Volver a Asignaciones de Docentes
    </a>
</div>

@if($errors->any())
    <div class="alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<!-- PANEL SUPERIOR DE ESTADÍSTICAS -->
<div class="stats-container">
    <div class="stat-card">
        <h3>Grupos en esta Gestión</h3>
        <div class="value">{{ $totalGrupos }}</div>
        <p class="desc">Cantidad total de grupos académicos.</p>
    </div>

    <div class="stat-card">
        <h3>Materias Requeridas</h3>
        <div class="value">{{ $totalRequeridos }}</div>
        <p class="desc">Parejas grupo-materia con horario registrado.</p>
    </div>

    <div class="stat-card teachers">
        <h3>Docentes Activos</h3>
        <div class="value">{{ $totalDocentesActivos }}</div>
        <p class="desc">Docentes disponibles en el sistema.</p>
    </div>

    <div class="stat-card pending">
        <h3>Asignaciones Pendientes</h3>
        <div class="value">{{ $totalPendientes }}</div>
        <p class="desc">De un total de {{ $totalRequeridos }} requeridas ({{ $totalAsignados }} ya asignadas).</p>
    </div>
</div>

<!-- DETALLE DE REGLAS DE NEGOCIO -->
<div class="rules-card">
    <h3>Reglas de Asignación Automática de Docentes:</h3>
    <ul class="rules-list">
        <li><strong>Especialidad Académica:</strong> Un docente solo puede ser asignado a materias asociadas directamente con sus especialidades registradas.</li>
        <li><strong>Límite de Carga Académica:</strong> Cada docente puede ser asignado a un máximo de <strong>4 grupos</strong> distintos.</li>
        <li><strong>Compatibilidad de Horarios:</strong> El algoritmo garantiza que no existan cruces u superposiciones de horarios para el docente asignado.</li>
        <li><strong>Descansos Obligatorios:</strong> Un docente puede impartir hasta un máximo de <strong>2 periodos continuos</strong> de clase el mismo día. Luego, se exige obligatoriamente un periodo libre/descanso antes de poder volver a dictar clase.</li>
    </ul>
</div>

<!-- FORMULARIO -->
<div class="form-card">
    <h2 style="color:#0b2d6b; margin-bottom:20px; font-size:20px;">Criterios de Asignación Automática</h2>

    <form action="{{ route('asignaciones-docentes.autogenerar.store') }}" method="POST" id="formAutogenerar">
        @csrf

        <div class="form-group">
            <label for="Id_gestion">Gestión Académica</label>
            <select name="Id_gestion" id="Id_gestion" onchange="recargarGestion(this.value)">
                @foreach($gestiones as $gestion)
                    <option value="{{ $gestion->id_gestion }}" {{ $gestion->id_gestion == $idGestion ? 'selected' : '' }}>
                        {{ $gestion->anio }} - {{ $gestion->periodo }} {{ strtolower(trim($gestion->estado)) === 'activo' ? '(Activo)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-submit" onclick="return confirmarEjecucion();">
            Iniciar Asignación Automática
        </button>
    </form>
</div>

<script>
function recargarGestion(val) {
    window.location.href = "{{ route('asignaciones-docentes.autogenerar.view') }}?Id_gestion=" + val;
}

function confirmarEjecucion() {
    return confirm("¿Está seguro que desea iniciar la asignación automática de docentes para la gestión seleccionada? Se reescribirán las asignaciones existentes de esta gestión.");
}
</script>

@endsection
