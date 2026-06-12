<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $nombreReporte }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 11px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0b2d6b;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            color: #0b2d6b;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #555;
            font-size: 12px;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #0b2d6b;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-ok {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-error {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $nombreReporte }}</h1>
        <p>Sistema Académico CUP - Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones (FICCT)</p>
    </div>

    <div class="meta-info">
        <strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
        <strong>Generado por:</strong> {{ auth()->user()->nombre_usuario ?? 'Administrador' }}<br>
        <strong>Total Registros Reportados:</strong> {{ count($resultados) }}
    </div>

    <table>
        @if(in_array($tipo_reporte, ['general', 'aprobados', 'reprobados']))
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>CI</th>
                    <th>Nombre Postulante</th>
                    <th>Carrera</th>
                    <th>Grupo</th>
                    <th class="text-center">Promedio</th>
                    <th>Inscripción</th>
                    <th>Resultado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">{{ $index + 1 }}</td>
                        <td>{{ $item->ci }}</td>
                        <td>{{ $item->apellido }}, {{ $item->nombre }}</td>
                        <td>{{ $item->nombre_carrera ?? 'Sin carrera' }}</td>
                        <td>{{ $item->sigla_grupo ?? '-' }}</td>
                        <td class="text-center font-bold">{{ $item->promedio_final ? number_format($item->promedio_final, 2) : '-' }}</td>
                        <td>{{ $item->estado_inscripcion ?? 'Pendiente' }}</td>
                        <td>{{ $item->estado_final ?? 'Sin calificar' }}</td>
                    </tr>
                @endforeach
            </tbody>

        @elseif($tipo_reporte === 'promedios')
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Carrera</th>
                    <th class="text-center">Total Postulantes</th>
                    <th class="text-center">Promedio General</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">{{ $index + 1 }}</td>
                        <td>{{ $item->nombre_carrera }}</td>
                        <td class="text-center">{{ $item->total_postulantes }}</td>
                        <td class="text-center font-bold" style="color: #0b2d6b;">{{ $item->promedio_general ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>

        @elseif($tipo_reporte === 'grupos')
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Sigla Grupo</th>
                    <th>Aula</th>
                    <th>Modalidad</th>
                    <th>Turno</th>
                    <th class="text-center">Capacidad Máx</th>
                    <th class="text-center">Alumnos</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->sigla_grupo }}</td>
                        <td>{{ $item->nro_aula ?? '-' }}</td>
                        <td>{{ $item->nombre_modalidad ?? '-' }}</td>
                        <td>{{ $item->nombre_turno ?? '-' }}</td>
                        <td class="text-center">{{ $item->capacidad_max }}</td>
                        <td class="text-center font-bold">{{ $item->cant_estudiantes }}</td>
                        <td>{{ ucfirst($item->estado) }}</td>
                    </tr>
                @endforeach
            </tbody>

        @elseif($tipo_reporte === 'materias')
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Docente</th>
                    <th class="text-center">Alumnos</th>
                    <th class="text-center">Nota Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->nombre_materia }}</td>
                        <td>{{ $item->sigla_grupo }}</td>
                        <td>{{ $item->nombre_docente ?? 'Sin docente' }}</td>
                        <td class="text-center">{{ $item->total_estudiantes }}</td>
                        <td class="text-center font-bold" style="color: #16a34a;">{{ $item->promedio_nota ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>

        @elseif($tipo_reporte === 'docentes')
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">#</th>
                    <th>Docente</th>
                    <th>CI</th>
                    <th class="text-center">Años Serv.</th>
                    <th>Carrera(s)</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Horario</th>
                    <th>Gestión</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->nombre_docente }}</td>
                        <td>{{ $item->ci }}</td>
                        <td class="text-center">{{ $item->anio_servicio ?? '0' }}</td>
                        <td>{{ $item->nombre_carreras ?? 'Sin carrera / asignación' }}</td>
                        <td>{{ $item->nombre_materia ?? 'Sin materia asignada' }}</td>
                        <td>{{ $item->sigla_grupo ?? 'Sin grupo' }}</td>
                        <td>{{ $item->horario_clase ?? 'Sin horario' }}</td>
                        <td>{{ $item->anio_gestion ? ($item->anio_gestion . ' - ' . $item->periodo_gestion) : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>

        @elseif($tipo_reporte === 'ranking_grupos')
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">Rank</th>
                    <th>Grupo</th>
                    <th class="text-center">Postulantes</th>
                    <th class="text-center">Aprobados</th>
                    <th class="text-center">% Aprobados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">#{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->sigla_grupo }}</td>
                        <td class="text-center">{{ $item->total_postulantes }}</td>
                        <td class="text-center font-bold" style="color: #16a34a;">{{ $item->aprobados }}</td>
                        <td class="text-center font-bold" style="color: #2563eb;">{{ $item->porcentaje_aprobados }}%</td>
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>

    <div class="footer">
        CUP FICCT - Reporte Oficial del Sistema - Página 1 de 1
    </div>

</body>
</html>
