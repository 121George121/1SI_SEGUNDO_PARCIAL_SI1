@extends('Usuario_Seguridad_y_Auditoria.Vista_Docente.layout')

@section('title', 'Mis Materias - Portal Docente')

@section('content')

<div style="background:linear-gradient(135deg,#0f172a,#164e63); padding:28px 32px; border-radius:16px;
            color:#fff; margin-bottom:28px; box-shadow:0 8px 24px rgba(0,0,0,0.1);">
    <h1 style="font-size:24px; font-weight:800; margin-bottom:6px;">Mis Materias</h1>
    <p style="font-size:14px; color:#7dd3fc; margin:0;">
        Materias asignadas a ti en los grupos activos del sistema. Solo lectura.
    </p>
</div>

@if($materias->isEmpty())
    <div style="background:#fff; border-radius:14px; padding:48px 24px; text-align:center;
                box-shadow:0 4px 16px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
        <svg style="width:48px;height:48px;color:#94a3b8;margin:0 auto 12px;display:block;"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p style="font-size:16px; font-weight:700; color:#475569; margin:0;">
            Aún no tienes materias asignadas.
        </p>
        <p style="font-size:14px; color:#94a3b8; margin-top:6px;">
            Cuando un administrador te asigne a un grupo, aparecerán aquí tus materias.
        </p>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
        @foreach($materias as $m)
            <div style="background:#fff; border-radius:14px; padding:20px 22px;
                        box-shadow:0 4px 16px rgba(0,0,0,0.05); border:1px solid #e2e8f0;
                        border-top:4px solid #22d3ee; transition:transform 0.15s;"
                 onmouseover="this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.transform='none'">

                {{-- Nombre materia --}}
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
                    <h3 style="font-size:16px; font-weight:800; color:#0f172a; line-height:1.3;">
                        {{ $m->nombre_materia }}
                    </h3>
                    <span style="background:{{ strtolower($m->estado_materia) === 'activo' ? '#ecfdf5' : '#fef2f2' }};
                                 color:{{ strtolower($m->estado_materia) === 'activo' ? '#059669' : '#dc2626' }};
                                 padding:3px 8px; border-radius:20px; font-size:11px; font-weight:700;
                                 text-transform:uppercase; white-space:nowrap; margin-left:8px;">
                        {{ $m->estado_materia }}
                    </span>
                </div>

                @if($m->descripcion_materia)
                    <p style="font-size:13px; color:#64748b; margin-bottom:14px; line-height:1.5;">
                        {{ Str::limit($m->descripcion_materia, 90) }}
                    </p>
                @endif

                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span><strong>Grupo:</strong> {{ $m->sigla_grupo }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span><strong>Gestión:</strong> {{ $m->anio }} — {{ $m->periodo }}</span>
                    </div>
                    @if($m->horarios)
                        <div style="display:flex; align-items:flex-start; gap:8px; font-size:13px; color:#475569;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-top:2px;flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Horarios:</strong> {{ $m->horarios }}</span>
                        </div>
                    @endif
                </div>

                {{-- Badge grupo --}}
                <div style="margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9;">
                    <a href="{{ route('docente.grupos.estudiantes', $m->Id_grupo) }}"
                       style="display:inline-flex; align-items:center; gap:6px; background:#0f172a;
                              color:#22d3ee; font-size:13px; font-weight:700; text-decoration:none;
                              padding:8px 14px; border-radius:7px; transition:background 0.2s;"
                       onmouseover="this.style.background='#1e293b'"
                       onmouseout="this.style.background='#0f172a'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/>
                        </svg>
                        Ver estudiantes
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
