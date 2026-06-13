@extends('Usuario_Seguridad_y_Auditoria.Vista_Docente.layout')

@section('title', 'Mis Grupos - Portal Docente')

@section('content')

<div style="background:linear-gradient(135deg,#0f172a,#164e63); padding:28px 32px; border-radius:16px;
            color:#fff; margin-bottom:28px; box-shadow:0 8px 24px rgba(0,0,0,0.1);">
    <h1 style="font-size:24px; font-weight:800; margin-bottom:6px;">Mis Grupos</h1>
    <p style="font-size:14px; color:#7dd3fc; margin:0;">
        Grupos asignados a ti en el sistema académico. Solo lectura.
    </p>
</div>

@if($grupos->isEmpty())
    <div style="background:#fff; border-radius:14px; padding:48px 24px; text-align:center;
                box-shadow:0 4px 16px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
        <svg style="width:48px;height:48px;color:#94a3b8;margin:0 auto 12px;display:block;"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p style="font-size:16px; font-weight:700; color:#475569; margin:0;">
            Aún no tienes grupos asignados.
        </p>
    </div>
@else
    <div style="background:#fff; border-radius:14px; overflow:hidden;
                box-shadow:0 4px 16px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:750px;">
                <thead>
                    <tr style="background:#0f172a; color:#fff;">
                        <th style="padding:14px 16px; text-align:left; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Grupo</th>
                        <th style="padding:14px 16px; text-align:left; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Materia</th>
                        <th style="padding:14px 16px; text-align:left; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Turno</th>
                        <th style="padding:14px 16px; text-align:left; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Modalidad</th>
                        <th style="padding:14px 16px; text-align:left; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Gestión</th>
                        <th style="padding:14px 16px; text-align:left; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Horarios</th>
                        <th style="padding:14px 16px; text-align:center; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Estudiantes</th>
                        <th style="padding:14px 16px; text-align:center; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Estado</th>
                        <th style="padding:14px 16px; text-align:center; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupos as $g)
                        <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background=''">
                            <td style="padding:14px 16px;">
                                <span style="font-weight:800; color:#0f172a; font-size:15px;">
                                    {{ $g->sigla_grupo }}
                                </span>
                            </td>
                            <td style="padding:14px 16px; font-size:14px; color:#334155; font-weight:600;">
                                {{ $g->nombre_materia }}
                            </td>
                            <td style="padding:14px 16px; font-size:14px; color:#334155;">
                                {{ $g->turno }}
                            </td>
                            <td style="padding:14px 16px; font-size:14px; color:#334155;">
                                {{ $g->modalidad }}
                            </td>
                            <td style="padding:14px 16px; font-size:13px; color:#64748b;">
                                {{ $g->anio }} — {{ $g->periodo }}
                            </td>
                            <td style="padding:14px 16px; font-size:12px; color:#475569; max-width:200px;">
                                {{ $g->horarios ?? 'Sin horario' }}
                            </td>
                            <td style="padding:14px 16px; text-align:center;">
                                <span style="background:#f0f9ff; color:#0369a1; font-weight:700;
                                             padding:4px 10px; border-radius:20px; font-size:13px;">
                                    {{ $g->cant_estudiantes }} / {{ $g->capacidad_max }}
                                </span>
                            </td>
                            <td style="padding:14px 16px; text-align:center;">
                                <span style="background:{{ strtolower($g->estado_grupo) === 'activo' ? '#ecfdf5' : '#fef2f2' }};
                                             color:{{ strtolower($g->estado_grupo) === 'activo' ? '#059669' : '#dc2626' }};
                                             padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase;">
                                    {{ $g->estado_grupo }}
                                </span>
                            </td>
                            <td style="padding:14px 16px; text-align:center;">
                                <a href="{{ route('docente.grupos.estudiantes', $g->Id_grupo) }}"
                                   style="display:inline-flex; align-items:center; gap:6px;
                                          background:#0f172a; color:#22d3ee; text-decoration:none;
                                          font-size:13px; font-weight:700; padding:8px 14px;
                                          border-radius:7px; white-space:nowrap; transition:background 0.2s;"
                                   onmouseover="this.style.background='#1e293b'"
                                   onmouseout="this.style.background='#0f172a'">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Ver estudiantes
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
