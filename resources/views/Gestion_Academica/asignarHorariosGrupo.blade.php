@extends('Gestion_Academica.Menu')

@section('title', 'Asignar Horario - Grupo ' . $grupo->sigla_grupo)

@section('content')

<style>
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .card-box {
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        margin-bottom: 24px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 14px;
    }

    .info-item {
        background: #f8fafc;
        padding: 12px 16px;
        border-radius: 8px;
        border-left: 4px solid #0b2d6b;
    }

    .info-label {
        font-size: 12px;
        color: #64748b;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 16px;
        color: #0b2d6b;
        font-weight: bold;
    }

    /* Grid Schedule Styles */
    .schedule-wrapper {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 900px;
    }

    .schedule-table th {
        background: #0b2d6b;
        color: white;
        font-weight: 600;
        padding: 16px;
        text-align: center;
        border: 1px solid #1e3a8a;
    }

    .schedule-table td {
        padding: 12px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .time-col {
        background: #f1f5f9;
        font-weight: bold;
        color: #0b2d6b;
        text-align: center;
        width: 150px;
        font-size: 14px;
    }

    .cell-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background-color: white;
        font-size: 13px;
        color: #334155;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
    }

    .cell-select:focus {
        border-color: #0b2d6b;
        box-shadow: 0 0 0 3px rgba(11, 45, 107, 0.15);
    }

    .cell-select.has-value {
        background-color: #f0fdf4;
        border-color: #86efac;
        color: #166534;
        font-weight: 600;
    }

    /* Premium Alert Styles */
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 14px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.1);
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 14px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.1);
    }

    .btn-group {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .btn-action {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: bold;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-save {
        background: #10b981;
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .btn-save:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    .btn-print {
        background: #3b82f6;
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    }

    .btn-print:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .btn-back {
        background: #64748b;
        color: white;
    }

    .btn-back:hover {
        background: #475569;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #64748b;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 12px;
        color: #cbd5e1;
    }
</style>

<div class="header-container">
    <div>
        <h1 class="titulo" style="margin:0;">Asignar Horario</h1>
        <p class="subtitulo" style="margin:4px 0 0 0;">Asignar materias a los bloques del turno correspondiente del grupo.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<!-- Group details card -->
<div class="card-box">
    <h3 style="color:#0b2d6b; margin:0 0 14px 0; font-size:18px;">Información del Grupo</h3>
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Grupo</div>
            <div class="info-value">{{ $grupo->sigla_grupo }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Turno</div>
            <div class="info-value">{{ $grupo->nombre_turno }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Aula / Ubicación</div>
            <div class="info-value">{{ $grupo->nro_aula }} ({{ $grupo->ubicacion ?? 'Sin Ubicación' }})</div>
        </div>
        <div class="info-item">
            <div class="info-label">Gestión Académica</div>
            <div class="info-value">{{ $grupo->anio }} - {{ $grupo->periodo }}</div>
        </div>
    </div>
</div>

<!-- Grid Schedule Selector -->
<div class="card-box">
    <h3 style="color:#0b2d6b; margin:0 0 16px 0; font-size:18px;">Distribución Semanal de Materias</h3>

    @if(count($bloques) > 0)
        <form action="{{ route('grupos.horario.store', $grupo->id_grupo) }}" method="POST">
            @csrf

            <div class="schedule-wrapper">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Bloque / Horario</th>
                            <th>Lunes</th>
                            <th>Martes</th>
                            <th>Miércoles</th>
                            <th>Jueves</th>
                            <th>Viernes</th>
                            <th>Sábado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bloques as $timeRange => $bloqueInfo)
                            <tr>
                                <td class="time-col">
                                    {{ $timeRange }}
                                </td>
                                @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $dia)
                                    <td>
                                        @if(isset($bloqueInfo['dias'][$dia]))
                                            @php
                                                $horarioId = $bloqueInfo['dias'][$dia];
                                                $materiaAsignada = isset($asignaciones[$horarioId]) ? $asignaciones[$horarioId]->Id_materia : null;
                                            @endphp
                                            <select name="horario_materia[{{ $horarioId }}]" class="cell-select {{ $materiaAsignada ? 'has-value' : '' }}" onchange="updateSelectStyle(this)">
                                                <option value="">-- Vacío --</option>
                                                @foreach($materias as $m)
                                                    <option value="{{ $m->id_materia }}" {{ $materiaAsignada == $m->id_materia ? 'selected' : '' }}>
                                                        {{ $m->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div style="text-align: center; color: #cbd5e1; font-style: italic; font-size: 12px;">
                                                No habilitado
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-action btn-save">
                    Guardar Distribución de Horario
                </button>
                
                @if(count($asignaciones) > 0)
                    <a href="{{ route('grupos.horario.imprimir', $grupo->id_grupo) }}" class="btn-action btn-print" target="_blank">
                        Ver Comprobante de Horario (Imprimir)
                    </a>
                @endif

                <a href="{{ route('grupos.index') }}" class="btn-action btn-back">
                    Volver a Grupos
                </a>
            </div>
        </form>
    @else
        <div class="empty-state">
            <div style="font-size: 36px; margin-bottom: 10px;">📅</div>
            <p style="font-weight: bold; margin-bottom: 6px;">No hay horarios definidos para el Turno: {{ $grupo->nombre_turno }}</p>
            <p style="font-size: 13px; color: #64748b;">Debe registrar los bloques correspondientes a este turno en "Gestionar Horarios" antes de asignarlos al grupo.</p>
            <div style="margin-top: 20px;">
                <a href="{{ route('horarios.index') }}" class="btn-action btn-print">
                    Registrar Bloques de Horario
                </a>
                <a href="{{ route('grupos.index') }}" class="btn-action btn-back" style="margin-left: 10px;">
                    Volver a Grupos
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    function updateSelectStyle(selectElement) {
        if (selectElement.value) {
            selectElement.classList.add('has-value');
        } else {
            selectElement.classList.remove('has-value');
        }
    }
</script>

@endsection
