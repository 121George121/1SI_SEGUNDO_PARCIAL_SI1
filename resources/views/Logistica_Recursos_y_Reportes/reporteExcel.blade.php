<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        th {
            background-color: #0b2d6b;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
        }
        td {
            border: 1px solid #dddddd;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h3>{{ $nombreReporte }}</h3>
    <p>Generado en: {{ now()->format('d/m/Y H:i:s') }}</p>
    
    <table>
        @if(in_array($tipo_reporte, ['general', 'aprobados', 'reprobados']))
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>CI</th>
                    <th>Nombre Postulante</th>
                    <th>Carrera</th>
                    <th>Grupo</th>
                    <th>Promedio Final</th>
                    <th>Estado Inscripción</th>
                    <th>Resultado Final</th>
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
                    <th>#</th>
                    <th>Carrera</th>
                    <th>Total Postulantes</th>
                    <th>Promedio General</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">{{ $index + 1 }}</td>
                        <td>{{ $item->nombre_carrera }}</td>
                        <td class="text-center">{{ $item->total_postulantes }}</td>
                        <td class="text-center font-bold">{{ $item->promedio_general ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>

        @elseif($tipo_reporte === 'grupos')
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sigla Grupo</th>
                    <th>Aula</th>
                    <th>Modalidad</th>
                    <th>Turno</th>
                    <th>Capacidad Máxima</th>
                    <th>Estudiantes Inscritos</th>
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
                    <th>#</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Docente</th>
                    <th>Total Estudiantes Evaluados</th>
                    <th>Promedio General de Nota</th>
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
                        <td class="text-center font-bold">{{ $item->promedio_nota ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>

        @elseif($tipo_reporte === 'docentes')
            <thead>
                <tr>
                    <th>#</th>
                    <th>Docente</th>
                    <th>CI</th>
                    <th>Años de Servicio</th>
                    <th>Carrera(s)</th>
                    <th>Materia Asignada</th>
                    <th>Grupo Asignado</th>
                    <th>Horario Clase</th>
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
                    <th>Rank</th>
                    <th>Grupo</th>
                    <th>Total Postulantes</th>
                    <th>Cantidad Aprobados</th>
                    <th>Porcentaje de Aprobación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $index => $item)
                    <tr>
                        <td class="text-center font-bold">#{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->sigla_grupo }}</td>
                        <td class="text-center">{{ $item->total_postulantes }}</td>
                        <td class="text-center font-bold">{{ $item->aprobados }}</td>
                        <td class="text-center font-bold">{{ $item->porcentaje_aprobados }}%</td>
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>
</body>
</html>
