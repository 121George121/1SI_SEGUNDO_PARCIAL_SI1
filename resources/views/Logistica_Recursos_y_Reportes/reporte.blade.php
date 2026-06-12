@extends('Logistica_Recursos_y_Reportes.Menu')

@section('content')

<h1 class="titulo">CU18 - Generar Reportes</h1>
<p class="subtitulo">Generar reportes estadísticos y de mérito sobre postulantes, grupos, cupos y resultados académicos.</p>

<style>
    .card-box {
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group select,
    .form-group input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        background-color: #f9fafb;
    }

    .btn-container {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-primary,
    .btn-success,
    .btn-warning,
    .btn-info {
        border: none;
        padding: 11px 18px;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: background 0.2s;
    }

    .btn-primary { background: #0b2d6b; }
    .btn-primary:hover { background: #092456; }
    
    .btn-success { background: #16a34a; }
    .btn-success:hover { background: #15803d; }

    .btn-warning { background: #d97706; }
    .btn-warning:hover { background: #b45309; }

    .btn-info { background: #2563eb; }
    .btn-info:hover { background: #1d4ed8; }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 14px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: bold;
    }

    .table-responsive {
        overflow-x: auto;
        margin-top: 16px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    th {
        background: #0b2d6b;
        color: white;
        padding: 12px 14px;
        text-align: left;
        font-size: 14px;
    }

    td {
        padding: 12px 14px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
        color: #374151;
        vertical-align: middle;
    }

    tr:hover {
        background-color: #f9fafb;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }

    .badge-ok {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-error {
        background: #fee2e2;
        color: #991b1b;
    }

    @media print {
        aside {
            display: none !important;
        }
        main {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .card-box:first-of-type {
            display: none !important;
        }
        .btn-container {
            display: none !important;
        }
        .titulo, .subtitulo {
            text-align: center;
        }
    }
</style>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Filtros de Reporte</h2>

    <form method="POST" action="{{ route('reportes.generar') }}">
        @csrf
        
        <div class="form-grid">
            <div class="form-group" style="grid-column: span 2;">
                <label for="tipo_reporte">Tipo de Reporte Obligatorio</label>
                <select name="tipo_reporte" id="tipo_reporte" required style="font-weight: bold; color: #0b2d6b;">
                    <option value="general" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'general' ? 'selected' : '' }}>1. Lista general de postulantes</option>
                    <option value="aprobados" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'aprobados' ? 'selected' : '' }}>2. Postulantes aprobados</option>
                    <option value="reprobados" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'reprobados' ? 'selected' : '' }}>3. Postulantes reprobados</option>
                    <option value="promedios" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'promedios' ? 'selected' : '' }}>4. Promedios generales por carrera</option>
                    <option value="grupos" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'grupos' ? 'selected' : '' }}>5. Cantidad de grupos habilitados</option>
                    <option value="materias" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'materias' ? 'selected' : '' }}>6. Estadísticas por materia</option>
                    <option value="docentes" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'docentes' ? 'selected' : '' }}>7. Docentes por grupos</option>
                    <option value="ranking_grupos" {{ old('tipo_reporte', $tipo_reporte ?? '') == 'ranking_grupos' ? 'selected' : '' }}>8. Grupos con mayor cantidad de aprobados</option>
                </select>
            </div>

            <div class="form-group">
                <label for="Id_gestion">Gestión Académica</label>
                <select name="Id_gestion" id="Id_gestion">
                    <option value="">Todas las gestiones...</option>
                    @foreach($gestiones as $gestion)
                        <option value="{{ $gestion->Id_gestion }}" {{ old('Id_gestion') == $gestion->Id_gestion ? 'selected' : '' }}>
                            {{ $gestion->anio }} - {{ $gestion->periodo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="Id_carrera">Carrera</label>
                <select name="Id_carrera" id="Id_carrera">
                    <option value="">Todas las carreras...</option>
                    @foreach($carreras as $carrera)
                        <option value="{{ $carrera->Id_carrera }}" {{ old('Id_carrera') == $carrera->Id_carrera ? 'selected' : '' }}>
                            {{ $carrera->nombre_carrera }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="Id_grupo">Grupo</label>
                <select name="Id_grupo" id="Id_grupo">
                    <option value="">Todos los grupos...</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->Id_grupo }}" {{ old('Id_grupo') == $grupo->Id_grupo ? 'selected' : '' }}>
                            {{ $grupo->sigla_grupo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="meritos">Criterio de Ordenamiento</label>
                <select name="meritos" id="meritos">
                    <option value="0" {{ old('meritos') == '0' ? 'selected' : '' }}>Alfabético (Apellido, Nombre)</option>
                    <option value="1" {{ old('meritos') == '1' ? 'selected' : '' }}>Mérito Académico (Mayor Promedio)</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 24px;" class="btn-container">
            <button type="submit" formaction="{{ route('reportes.generar') }}" class="btn-primary">
                Generar Reporte
            </button>
            @if(isset($resultados) && $resultados->isNotEmpty())
                <button type="submit" formaction="{{ route('reportes.exportar', ['format' => 'pdf']) }}" class="btn-success">
                    Exportar PDF
                </button>
                <button type="submit" formaction="{{ route('reportes.exportar', ['format' => 'excel']) }}" class="btn-warning">
                    Exportar Excel
                </button>
                <button type="button" onclick="window.print()" class="btn-info">
                    Imprimir Reporte
                </button>
            @endif
        </div>
    </form>
</div>

@if(isset($resultados) && $resultados->isNotEmpty())
    @php
        $nombresReporte = [
            'general' => 'Lista General de Postulantes',
            'aprobados' => 'Postulantes Aprobados',
            'reprobados' => 'Postulantes Reprobados',
            'promedios' => 'Promedios Generales por Carrera',
            'grupos' => 'Cantidad de Grupos Habilitados',
            'materias' => 'Estadísticas por Materia',
            'docentes' => 'Docentes por Grupos',
            'ranking_grupos' => 'Grupos con Mayor Cantidad de Aprobados'
        ];
        $nombreActual = $nombresReporte[$tipo_reporte ?? 'general'] ?? 'Reporte';
    @endphp

    <div class="card-box">
        <h2 style="color:#0b2d6b; margin-bottom:16px;">Resultados: {{ $nombreActual }}</h2>
        
        <div class="table-responsive">
            <table>
                @if(in_array($tipo_reporte, ['general', 'aprobados', 'reprobados']))
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">Rank</th>
                            <th>CI</th>
                            <th>Nombre Postulante</th>
                            <th>Carrera</th>
                            <th>Grupo</th>
                            <th style="text-align: center;">Promedio Final</th>
                            <th>Estado Inscripción</th>
                            <th>Resultado Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultados as $index => $item)
                            <tr>
                                <td style="font-weight: bold; text-align: center;">
                                    @if(old('meritos') == '1')
                                        #{{ $index + 1 }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->ci }}</td>
                                <td>{{ $item->apellido }}, {{ $item->nombre }}</td>
                                <td>{{ $item->nombre_carrera ?? 'Sin carrera asignada' }}</td>
                                <td>{{ $item->sigla_grupo ?? 'Sin grupo' }}</td>
                                <td style="text-align: center; font-weight: bold;">
                                    {{ $item->promedio_final ? number_format($item->promedio_final, 2) : '-' }}
                                </td>
                                <td>
                                    <span class="badge {{ $item->estado_inscripcion == 'Aceptado' ? 'badge-ok' : ($item->estado_inscripcion == 'Observado' ? 'badge-error' : 'badge-warning') }}">
                                        {{ $item->estado_inscripcion ?? 'Pendiente' }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->estado_final)
                                        <span class="badge {{ $item->estado_final == 'Aprobado' ? 'badge-ok' : 'badge-error' }}">
                                            {{ $item->estado_final }}
                                        </span>
                                    @else
                                        <span style="color: #6b7280; font-size: 13px;">Sin calificar</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                @elseif($tipo_reporte === 'promedios')
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th>Carrera</th>
                            <th style="text-align: center;">Total Postulantes</th>
                            <th style="text-align: center;">Promedio General de la Carrera</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultados as $index => $item)
                            <tr>
                                <td style="font-weight: bold; text-align: center;">{{ $index + 1 }}</td>
                                <td>{{ $item->nombre_carrera }}</td>
                                <td style="text-align: center;">{{ $item->total_postulantes }}</td>
                                <td style="text-align: center; font-weight: bold; color: #0b2d6b;">
                                    {{ $item->promedio_general ? number_format($item->promedio_general, 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                @elseif($tipo_reporte === 'grupos')
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th>Sigla Grupo</th>
                            <th>Aula</th>
                            <th>Modalidad</th>
                            <th>Turno</th>
                            <th style="text-align: center;">Capacidad Máxima</th>
                            <th style="text-align: center;">Estudiantes Inscritos</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultados as $index => $item)
                            <tr>
                                <td style="font-weight: bold; text-align: center;">{{ $index + 1 }}</td>
                                <td style="font-weight: bold; color: #0b2d6b;">{{ $item->sigla_grupo }}</td>
                                <td>{{ $item->nro_aula ?? 'Sin aula' }}</td>
                                <td>{{ $item->nombre_modalidad ?? 'Sin modalidad' }}</td>
                                <td>{{ $item->nombre_turno ?? 'Sin turno' }}</td>
                                <td style="text-align: center;">{{ $item->capacidad_max }}</td>
                                <td style="text-align: center; font-weight: bold;">{{ $item->cant_estudiantes }}</td>
                                <td>
                                    <span class="badge {{ $item->estado == 'activo' ? 'badge-ok' : 'badge-error' }}">
                                        {{ ucfirst($item->estado) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                @elseif($tipo_reporte === 'materias')
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th>Materia</th>
                            <th>Grupo</th>
                            <th>Docente Asignado</th>
                            <th style="text-align: center;">Total Estudiantes Evaluados</th>
                            <th style="text-align: center;">Promedio General de Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultados as $index => $item)
                            <tr>
                                <td style="font-weight: bold; text-align: center;">{{ $index + 1 }}</td>
                                <td style="font-weight: bold; color: #0b2d6b;">{{ $item->nombre_materia }}</td>
                                <td>{{ $item->sigla_grupo }}</td>
                                <td>{{ $item->nombre_docente ?? 'Sin docente' }}</td>
                                <td style="text-align: center;">{{ $item->total_estudiantes }}</td>
                                <td style="text-align: center; font-weight: bold; color: #16a34a;">
                                    {{ $item->promedio_nota ? number_format($item->promedio_nota, 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                @elseif($tipo_reporte === 'docentes')
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th>Docente</th>
                            <th>CI</th>
                            <th style="text-align: center;">Años de Servicio</th>
                            <th>Grupo Asignado</th>
                            <th>Materia Asignada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultados as $index => $item)
                            <tr>
                                <td style="font-weight: bold; text-align: center;">{{ $index + 1 }}</td>
                                <td style="font-weight: bold; color: #0b2d6b;">{{ $item->nombre_docente }}</td>
                                <td>{{ $item->ci }}</td>
                                <td style="text-align: center;">{{ $item->anio_servicio ?? '0' }}</td>
                                <td>{{ $item->sigla_grupo }}</td>
                                <td>{{ $item->nombre_materia }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                @elseif($tipo_reporte === 'ranking_grupos')
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">Rank</th>
                            <th>Grupo</th>
                            <th style="text-align: center;">Total Postulantes</th>
                            <th style="text-align: center;">Cantidad Aprobados</th>
                            <th style="text-align: center;">Porcentaje de Aprobación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultados as $index => $item)
                            <tr>
                                <td style="font-weight: bold; text-align: center;">#{{ $index + 1 }}</td>
                                <td style="font-weight: bold; color: #0b2d6b;">{{ $item->sigla_grupo }}</td>
                                <td style="text-align: center;">{{ $item->total_postulantes }}</td>
                                <td style="text-align: center; font-weight: bold; color: #16a34a;">{{ $item->aprobados }}</td>
                                <td style="text-align: center; font-weight: bold; color: #2563eb;">
                                    {{ $item->porcentaje_aprobados }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>
    </div>
@endif

@endsection
