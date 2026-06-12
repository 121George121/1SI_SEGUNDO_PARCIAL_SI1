<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Horario - Grupo {{ $grupo->sigla_grupo }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f5f9;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .no-print-zone {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }

        .btn-print {
            padding: 10px 20px;
            background: #0b2d6b;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
            text-decoration: none;
        }

        .btn-print:hover {
            background: #1e3a8a;
        }

        .btn-back {
            background: #64748b;
        }

        .btn-back:hover {
            background: #475569;
        }

        /* Thermal ticket mockup */
        .ticket {
            background: #fff;
            width: 100%;
            max-width: 850px;
            padding: 30px;
            box-sizing: border-box;
            border: 2px dashed #94a3b8;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1px dashed #000;
            padding-bottom: 15px;
        }

        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 13px;
        }

        .student-callout {
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .schedule-header {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            font-weight: bold;
            display: flex;
        }

        .row-day {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px dotted #ccc;
            align-items: center;
        }

        .day-name {
            width: 130px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .day-slots {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
        }

        .slot-item {
            white-space: nowrap;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 15px;
            font-size: 12px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                display: block;
            }
            .no-print-zone {
                display: none;
            }
            .ticket {
                border: none;
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-zone">
        <button class="btn-print" onclick="window.print()">Imprimir Comprobante</button>
        <a href="{{ route('grupos.horario', $grupo->id_grupo) }}" class="btn-print btn-back">Volver a Asignación</a>
    </div>

    <div class="ticket">
        <div class="header">
            <h2>UNIVERSIDAD AUTÓNOMA GABRIEL RENÉ MORENO</h2>
            <p>FACULTAD DE INGENIERÍA EN CIENCIAS DE LA COMPUTACIÓN Y TELECOMUNICACIONES</p>
            <p>PROGRAMA DE ADMISIÓN BÁSICA (CUP) - GESTIÓN {{ $grupo->anio }} - PERIODO {{ $grupo->periodo }}</p>
        </div>

        <div class="student-callout">
            Senor alumno Ud. debera presentarse a clases de PROGRAMA DE ADMISION BASICA en el Grupo :<strong>{{ $grupo->sigla_grupo }}</strong>
        </div>

        <div class="schedule-header">
            <div style="width: 130px;">DIA</div>
            <div style="flex: 1;">MATERIA HORARIO AULA</div>
        </div>

        @foreach($scheduleByDay as $dia => $slots)
            <div class="row-day">
                <div class="day-name">
                    {{ $dia }}
                </div>
                <div class="day-slots">
                    @if(count($slots) > 0)
                        @foreach($slots as $s)
                            <span class="slot-item">:{{ $s['materia'] }} {{ $s['rango'] }} {{ $s['aula'] }}|</span>
                        @endforeach
                    @else
                        <span style="color: #888; font-style: italic;">-- SIN CLASES --</span>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="footer">
            <p>Fecha y Hora de Impresión: {{ date('d-m-Y H:i:s') }}</p>
            <p>Presente este comprobante al ingresar al aula.</p>
        </div>
    </div>

</body>
</html>
