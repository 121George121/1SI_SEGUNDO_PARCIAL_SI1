@extends('Usuario_Seguridad_y_Auditoria.Vista_Docente.layout')

@section('title', 'Notas - Portal Docente')

@section('content')

<div style="background:linear-gradient(135deg,#0f172a,#164e63); padding:28px 32px; border-radius:16px;
            color:#fff; margin-bottom:28px; box-shadow:0 8px 24px rgba(0,0,0,0.1);">
    <h1 style="font-size:24px; font-weight:800; margin-bottom:6px;">Notas</h1>
    <p style="font-size:14px; color:#7dd3fc; margin:0;">
        Notas de los estudiantes de tus grupos. 🔒 Solo lectura — las notas son registradas únicamente por personal administrativo.
    </p>
</div>

{{-- Filtro por grupo --}}
@if($gruposDisponibles->isNotEmpty())
    <form method="GET" action="{{ route('docente.notas') }}"
          style="background:#fff; border-radius:12px; padding:18px 20px; margin-bottom:20px;
                 box-shadow:0 4px 12px rgba(0,0,0,0.04); border:1px solid #e2e8f0;
                 display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <label style="font-weight:700; color:#334155; font-size:14px;">Filtrar por grupo:</label>
        <select name="grupo_id"
                style="padding:8px 12px; border:1px solid #cbd5e1; border-radius:7px;
                       font-size:14px; color:#334155; outline:none; background:#f8fafc;"
                onchange="this.form.submit()">
            <option value="">Todos los grupos</option>
            @foreach($gruposDisponibles as $g)
                <option value="{{ $g->Id_grupo }}" {{ $grupoFiltro == $g->Id_grupo ? 'selected' : '' }}>
                    {{ $g->sigla_grupo }}
                </option>
            @endforeach
        </select>
        @if($grupoFiltro)
            <a href="{{ route('docente.notas') }}"
               style="color:#64748b; font-size:13px; font-weight:600; text-decoration:none;">
                Limpiar filtro ✕
            </a>
        @endif
    </form>
@endif

@if($notas->isEmpty())
    <div style="background:#fff; border-radius:14px; padding:56px 24px; text-align:center;
                box-shadow:0 4px 16px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
        <svg style="width:48px;height:48px;color:#94a3b8;margin:0 auto 12px;display:block;"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        <p style="font-size:16px; font-weight:700; color:#475569; margin:0;">
            Aún no existen notas registradas para tus grupos.
        </p>
        <p style="font-size:13px; color:#94a3b8; margin-top:8px;">
            Las notas serán registradas por el personal administrativo del sistema.
        </p>
    </div>
@else
    <div style="background:#fff; border-radius:14px; overflow:hidden;
                box-shadow:0 4px 16px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">

        <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex;
                    justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <span style="font-weight:700; color:#0f172a; font-size:15px;">
                {{ $notas->count() }} nota(s) encontradas
            </span>
            <span style="font-size:12px; color:#94a3b8; font-style:italic;">
                🔒 No puedes modificar estas notas
            </span>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:800px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Estudiante</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">CI</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Materia</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Grupo</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Evaluación</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Nota</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Estado</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Observación</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notas as $nota)
                        <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background=''">
                            <td style="padding:13px 16px; font-size:14px; font-weight:700; color:#0f172a;">
                                {{ $nota->nombre_estudiante }} {{ $nota->apellido_estudiante }}
                            </td>
                            <td style="padding:13px 16px; font-size:13px; color:#64748b; font-weight:600;">
                                {{ $nota->ci }}
                            </td>
                            <td style="padding:13px 16px; font-size:14px; color:#334155; font-weight:600;">
                                {{ $nota->nombre_materia }}
                            </td>
                            <td style="padding:13px 16px;">
                                <span style="background:#f0f9ff; color:#0369a1; font-weight:700;
                                             padding:4px 10px; border-radius:20px; font-size:12px;">
                                    {{ $nota->sigla_grupo }}
                                </span>
                            </td>
                            <td style="padding:13px 16px; text-align:center; font-size:13px; color:#475569; font-weight:600;">
                                Evaluación {{ $nota->numero_evaluacion }}
                            </td>
                            <td style="padding:13px 16px; text-align:center;">
                                @php
                                    $notaVal = floatval($nota->nota);
                                    $colorNota = $notaVal >= 51 ? '#059669' : '#dc2626';
                                @endphp
                                <span style="font-size:18px; font-weight:800; color:{{ $colorNota }};">
                                    {{ number_format($notaVal, 1) }}
                                </span>
                            </td>
                            <td style="padding:13px 16px; text-align:center;">
                                @php
                                    $estadoAc = strtolower($nota->estado_academico);
                                    $bgEstado = match($estadoAc) {
                                        'aprobado' => '#ecfdf5',
                                        'reprobado' => '#fef2f2',
                                        default => '#f1f5f9',
                                    };
                                    $txEstado = match($estadoAc) {
                                        'aprobado' => '#059669',
                                        'reprobado' => '#dc2626',
                                        default => '#64748b',
                                    };
                                @endphp
                                <span style="background:{{ $bgEstado }}; color:{{ $txEstado }};
                                             padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase;">
                                    {{ ucfirst($nota->estado_academico) }}
                                </span>
                            </td>
                            <td style="padding:13px 16px; font-size:13px; color:#64748b;">
                                Sin observaciones
                            </td>
                            <td style="padding:13px 16px; font-size:13px; color:#64748b;">
                                {{ $nota->fecha ? \Carbon\Carbon::parse($nota->fecha)->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
