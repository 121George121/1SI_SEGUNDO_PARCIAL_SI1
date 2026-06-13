@extends('Usuario_Seguridad_y_Auditoria.Vista_Docente.layout')

@section('title', 'Estudiantes del Grupo {{ $grupo->sigla_grupo }} - Portal Docente')

@section('content')

{{-- Breadcrumb --}}
<div style="margin-bottom:20px; font-size:13px; color:#64748b; display:flex; align-items:center; gap:8px;">
    <a href="{{ route('docente.grupos') }}"
       style="color:#22d3ee; text-decoration:none; font-weight:600;">
        ← Mis grupos
    </a>
    <span>/</span>
    <span style="font-weight:600; color:#334155;">{{ $grupo->sigla_grupo }}</span>
</div>

{{-- Header con info del grupo --}}
<div style="background:linear-gradient(135deg,#0f172a,#164e63); padding:28px 32px; border-radius:16px;
            color:#fff; margin-bottom:28px; box-shadow:0 8px 24px rgba(0,0,0,0.1);">
    <h1 style="font-size:24px; font-weight:800; margin-bottom:14px;">
        Grupo: {{ $grupo->sigla_grupo }}
    </h1>
    <div style="display:flex; flex-wrap:wrap; gap:20px;">
        <div>
            <span style="font-size:11px; color:#7dd3fc; font-weight:700; text-transform:uppercase;">Materia(s)</span>
            <p style="font-size:15px; font-weight:700; margin-top:4px;">{{ $materias ?: 'Sin materia asignada' }}</p>
        </div>
        <div>
            <span style="font-size:11px; color:#7dd3fc; font-weight:700; text-transform:uppercase;">Turno</span>
            <p style="font-size:15px; font-weight:700; margin-top:4px;">{{ $grupo->turno }}</p>
        </div>
        <div>
            <span style="font-size:11px; color:#7dd3fc; font-weight:700; text-transform:uppercase;">Modalidad</span>
            <p style="font-size:15px; font-weight:700; margin-top:4px;">{{ $grupo->modalidad }}</p>
        </div>
        <div>
            <span style="font-size:11px; color:#7dd3fc; font-weight:700; text-transform:uppercase;">Gestión</span>
            <p style="font-size:15px; font-weight:700; margin-top:4px;">{{ $grupo->anio }} — {{ $grupo->periodo }}</p>
        </div>
        <div>
            <span style="font-size:11px; color:#7dd3fc; font-weight:700; text-transform:uppercase;">Estudiantes</span>
            <p style="font-size:15px; font-weight:700; margin-top:4px;">{{ $grupo->cant_estudiantes }}</p>
        </div>
    </div>

    {{-- Horarios --}}
    @if($horarios->isNotEmpty())
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.1);">
            <span style="font-size:11px; color:#7dd3fc; font-weight:700; text-transform:uppercase; display:block; margin-bottom:10px;">
                Horarios
            </span>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                @foreach($horarios as $h)
                    <span style="background:rgba(34,211,238,0.15); color:#22d3ee; padding:5px 12px;
                                 border-radius:20px; font-size:12px; font-weight:700; border:1px solid rgba(34,211,238,0.3);">
                        {{ $h->dia }} {{ substr($h->hora_inicio, 0, 5) }} – {{ substr($h->hora_fin, 0, 5) }}
                        <span style="opacity:0.7;">({{ $h->materia }})</span>
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Lista de Estudiantes --}}
<div style="background:#fff; border-radius:14px; overflow:hidden;
            box-shadow:0 4px 16px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">

    <div style="padding:20px 24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;">
            Lista de Estudiantes
            <span style="background:#f0f9ff; color:#0369a1; font-size:13px; font-weight:700;
                         padding:3px 10px; border-radius:20px; margin-left:8px;">
                {{ $estudiantes->count() }}
            </span>
        </h2>
        <span style="font-size:13px; color:#94a3b8; font-style:italic; font-weight:500;">
            🔒 Vista de solo lectura
        </span>
    </div>

    @if($estudiantes->isEmpty())
        <div style="padding:48px 24px; text-align:center;">
            <svg style="width:40px;height:40px;color:#94a3b8;margin:0 auto 12px;display:block;"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/>
            </svg>
            <p style="font-size:16px; font-weight:700; color:#475569; margin:0;">
                Este grupo aún no tiene estudiantes inscritos.
            </p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:650px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700;
                                   text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">#</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700;
                                   text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">CI</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700;
                                   text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Nombre Completo</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700;
                                   text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Correo</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; font-weight:700;
                                   text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Estado Inscripción</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; font-weight:700;
                                   text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Estado Admisión</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estudiantes as $idx => $est)
                        <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background=''">
                            <td style="padding:13px 16px; font-size:13px; color:#94a3b8; font-weight:600;">
                                {{ $idx + 1 }}
                            </td>
                            <td style="padding:13px 16px; font-size:14px; font-weight:700; color:#0f172a;">
                                {{ $est->ci }}
                            </td>
                            <td style="padding:13px 16px;">
                                <span style="font-size:14px; font-weight:700; color:#0f172a;">
                                     {{ $est->nombre }} {{ $est->apellido }}
                                </span>
                            </td>
                            <td style="padding:13px 16px; font-size:13px; color:#475569;">
                                {{ $est->correo ?: '—' }}
                            </td>
                            <td style="padding:13px 16px; text-align:center;">
                                @php
                                    $estado = $est->estado_inscripcion_detalle ?? $est->estado_inscripcion;
                                    $colorBg = match(strtolower(str_replace('_', '', $estado))) {
                                        'inscrito' => '#ecfdf5',
                                        'enrevision' => '#fffbeb',
                                        default => '#f1f5f9',
                                    };
                                    $colorTx = match(strtolower(str_replace('_', '', $estado))) {
                                        'inscrito' => '#059669',
                                        'enrevision' => '#b45309',
                                        default => '#475569',
                                    };
                                @endphp
                                <span style="background:{{ $colorBg }}; color:{{ $colorTx }};
                                             padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase;">
                                    {{ str_replace('_', ' ', $estado) }}
                                </span>
                            </td>
                            <td style="padding:13px 16px; text-align:center;">
                                @php
                                    $estadoAdm = 'Pendiente';
                                    if ($est->estado_asignacion && strtolower(trim($est->estado_asignacion)) === 'admitido') {
                                        $estadoAdm = 'Aceptado';
                                    } elseif ($est->estado_final && strtolower(trim($est->estado_final)) === 'reprobado') {
                                        $estadoAdm = 'Rechazado';
                                    } else {
                                        $estadoAdm = 'En revisión';
                                    }

                                    $colorBgAdm = match($estadoAdm) {
                                        'Aceptado' => '#ecfdf5',
                                        'Rechazado' => '#fef2f2',
                                        'En revisión' => '#fffbeb',
                                        default => '#f1f5f9',
                                    };
                                    $colorTxAdm = match($estadoAdm) {
                                        'Aceptado' => '#059669',
                                        'Rechazado' => '#dc2626',
                                        'En revisión' => '#b45309',
                                        default => '#475569',
                                    };
                                @endphp
                                <span style="background:{{ $colorBgAdm }}; color:{{ $colorTxAdm }};
                                             padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase;">
                                    {{ $estadoAdm }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
