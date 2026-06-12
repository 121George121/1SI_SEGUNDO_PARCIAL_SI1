@extends('Gestion_Academica.Menu')

@section('content')

<h1 class="titulo">CU11 - Creación Automática de Grupos</h1>
<p class="subtitulo">Generar grupos de forma inteligente y distribuir postulantes validados según sus preferencias de modalidad y turno.</p>

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
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

    .stat-card.virtual {
        border-left-color: #10b981;
    }

    .stat-card.total {
        border-left-color: #f59e0b;
        background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
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

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 14px;
        border-top: 1px solid #eee;
        padding-top: 14px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-item .label {
        font-size: 12px;
        color: #888;
    }

    .detail-item .val {
        font-size: 16px;
        font-weight: bold;
        color: #333;
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

    .form-group select,
    .form-group input {
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        transition: border 0.2s;
    }

    .form-group select:focus,
    .form-group input:focus {
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

    .preference-summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 14px;
    }

    .preference-summary-table th {
        background: #f3f4f6;
        color: #4b5563;
        font-weight: 600;
        font-size: 12px;
        padding: 8px 12px;
        text-align: left;
    }

    .preference-summary-table td {
        padding: 8px 12px;
        font-size: 13px;
        border-bottom: 1px solid #f3f4f6;
    }

    .badge-pending {
        background: #fef3c7;
        color: #d97706;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
    }
</style>

<div class="header-actions">
    <a href="{{ route('grupos.index') }}" class="btn-back">
        &larr; Volver a Gestión de Grupos
    </a>
</div>

@if($errors->any())
    <div class="alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<!-- PANEL SUPERIOR DE ESTADÍSTICAS -->
<div class="stats-container">
    
    <!-- CARD GLOBAL -->
    <div class="stat-card total">
        <h3>Postulantes Validados</h3>
        <div class="value">{{ $totalValidados }}</div>
        <p class="desc">Postulantes en esta gestión con documentos y pagos validados.</p>
    </div>

    <!-- TARJETAS POR MODALIDAD -->
    @foreach($modalidadesValidas as $modalidad)
        @php
            $isVirtual = str_contains(strtolower($modalidad->nombre_modalidad), 'virtual');
            $totalModalidad = 0;
            $sinGrupoModalidad = 0;
            foreach($stats[$modalidad->nombre_modalidad] ?? [] as $tName => $values) {
                $totalModalidad += $values['total'];
                $sinGrupoModalidad += $values['sin_grupo'];
            }
        @endphp
        <div class="stat-card {{ $isVirtual ? 'virtual' : '' }}">
            <h3>Preferencia: {{ $modalidad->nombre_modalidad }}</h3>
            <div class="value">{{ $totalModalidad }}</div>
            <p class="desc">{{ $sinGrupoModalidad }} alumnos pendientes de grupo</p>

            <table class="preference-summary-table">
                <thead>
                    <tr>
                        <th>Turno</th>
                        <th>Total Validados</th>
                        <th>Pendientes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($turnosValidos as $turno)
                        @php
                            $tStats = $stats[$modalidad->nombre_modalidad][$turno->nombre] ?? ['total' => 0, 'sin_grupo' => 0];
                        @endphp
                        <tr>
                            <td><strong>{{ $turno->nombre }}</strong></td>
                            <td>{{ $tStats['total'] }}</td>
                            <td>
                                @if($tStats['sin_grupo'] > 0)
                                    <span class="badge-pending">{{ $tStats['sin_grupo'] }} sin grupo</span>
                                @else
                                    <span style="color:#10b981; font-weight:bold;">Completo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>

<!-- FORMULARIO DE CRITERIOS DE GENERACIÓN -->
<div class="form-card">
    <h2 style="color:#0b2d6b; margin-bottom:20px; font-size:20px;">Criterios de Creación de Grupos</h2>

    <form action="{{ route('grupos.autogenerar.store') }}" method="POST" id="formAutogenerar">
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

        <div class="form-group">
            <label for="estudiantes_por_aula">Cantidad de Estudiantes por Aula / Grupo</label>
            <input type="number" name="estudiantes_por_aula" id="estudiantes_por_aula" min="1" max="200" value="40" required placeholder="Ej: 40">
            <span style="font-size:12px; color:#666; margin-top:6px;">
                El sistema creará los grupos necesarios para cada modalidad y turno dividiendo los postulantes validados sin grupo entre esta cantidad. Las aulas activas se asignarán de manera cíclica.
            </span>
        </div>

        <button type="submit" class="btn-submit" onclick="return confirmarEjecucion();">
            Iniciar Generación Automática
        </button>
    </form>
</div>

<script>
function recargarGestion(val) {
    window.location.href = "{{ route('grupos.autogenerar.view') }}?Id_gestion=" + val;
}

function confirmarEjecucion() {
    const cap = document.getElementById('estudiantes_por_aula').value;
    if(!cap || cap <= 0) {
        alert("Por favor ingrese una cantidad válida de estudiantes por aula.");
        return false;
    }
    return confirm("¿Está seguro que desea iniciar la autogeneración de grupos con un límite de " + cap + " estudiantes por aula? Se asignarán automáticamente las aulas activas disponibles.");
}
</script>

@endsection
