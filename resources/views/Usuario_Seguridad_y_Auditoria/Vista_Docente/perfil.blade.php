@extends('Usuario_Seguridad_y_Auditoria.Vista_Docente.layout')

@section('title', 'Mi Perfil - Portal Docente')

@section('content')

{{-- Hero Header --}}
<div style="background: linear-gradient(135deg, #0f172a 0%, #164e63 100%); padding: 32px; border-radius: 16px;
            color: #fff; margin-bottom: 28px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); position: relative; overflow: hidden;">
    <div style="position:absolute; top:-40px; right:-40px; width:180px; height:180px;
                background: rgba(34,211,238,0.06); border-radius: 50%;"></div>
    <span style="display:inline-block; background:rgba(34,211,238,0.15); color:#22d3ee;
                 padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700;
                 text-transform:uppercase; letter-spacing:1.5px; margin-bottom:12px; border:1px solid rgba(34,211,238,0.3);">
        Portal Docente
    </span>
    <h1 style="font-size:26px; font-weight:800; margin-bottom:8px;">Mi Perfil</h1>
    <p style="font-size:14px; color:#7dd3fc; margin:0;">
        Consulta tu información personal y académica registrada en el sistema.
    </p>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- Grid de tarjetas --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">

    {{-- CARD: Datos Personales --}}
    <div style="background:#fff; border-radius:14px; padding:24px;
                box-shadow:0 4px 16px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
        <h3 style="font-size:16px; font-weight:700; color:#0f172a; display:flex; align-items:center;
                   gap:8px; border-bottom:1px solid #f1f5f9; padding-bottom:12px; margin-bottom:20px;">
            <svg width="18" height="18" fill="none" stroke="#22d3ee" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Datos Personales
        </h3>

        @php
            $campos = [
                'CI'                   => $persona->ci,
                'Nombre completo'      => $persona->nombre . ' ' . $persona->apellido,
                'Sexo'                 => $persona->sexo === 'M' ? 'Masculino' : ($persona->sexo === 'F' ? 'Femenino' : 'No especificado'),
                'Fecha de nacimiento'  => \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y'),
                'Teléfono'             => $persona->telefono ?: 'No registrado',
                'Correo electrónico'   => $persona->correo   ?: 'No registrado',
                'Dirección'            => $persona->direccion ?: 'No registrada',
            ];
        @endphp

        <div style="display:flex; flex-direction:column; gap:14px;">
            @foreach($campos as $etiqueta => $valor)
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                        {{ $etiqueta }}
                    </span>
                    <p style="font-size:15px; color:#0f172a; font-weight:600; margin-top:2px;">
                        {{ $valor }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- CARD: Cuenta de Acceso --}}
    <div style="display:flex; flex-direction:column; gap:24px;">
        <div style="background:#fff; border-radius:14px; padding:24px;
                    box-shadow:0 4px 16px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
            <h3 style="font-size:16px; font-weight:700; color:#0f172a; display:flex; align-items:center;
                       gap:8px; border-bottom:1px solid #f1f5f9; padding-bottom:12px; margin-bottom:20px;">
                <svg width="18" height="18" fill="none" stroke="#22d3ee" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Cuenta de Acceso
            </h3>
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Nombre de usuario</span>
                    <p style="font-size:15px; color:#0f172a; font-weight:700; margin-top:2px;">
                        {{ $usuario->nombre_usuario }}
                    </p>
                </div>
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Estado de cuenta</span>
                    <div style="margin-top:4px;">
                        <span style="background:#ecfdf5; color:#059669; padding:4px 10px;
                                     border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase;">
                            {{ $usuario->estado }}
                        </span>
                    </div>
                </div>
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Miembro desde</span>
                    <p style="font-size:15px; color:#0f172a; font-weight:600; margin-top:2px;">
                        {{ \Carbon\Carbon::parse($usuario->fecha_creacion)->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- CARD: Datos Docente --}}
        <div style="background:#fff; border-radius:14px; padding:24px;
                    box-shadow:0 4px 16px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
            <h3 style="font-size:16px; font-weight:700; color:#0f172a; display:flex; align-items:center;
                       gap:8px; border-bottom:1px solid #f1f5f9; padding-bottom:12px; margin-bottom:20px;">
                <svg width="18" height="18" fill="none" stroke="#22d3ee" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                Datos Docente
            </h3>
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Años de servicio</span>
                    <p style="font-size:15px; color:#0f172a; font-weight:600; margin-top:2px;">
                        {{ $docente->anio_servicio ?? 'No registrado' }}
                        @if($docente->anio_servicio) años @endif
                    </p>
                </div>
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Estado</span>
                    <div style="margin-top:4px;">
                        <span style="background:#eff6ff; color:#1d4ed8; padding:4px 10px;
                                     border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase;">
                            {{ $docente->estado ?? 'activo' }}
                        </span>
                    </div>
                </div>

                {{-- Especialidades --}}
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">
                        Especialidades
                    </span>
                    @if($especialidades->isNotEmpty())
                        <div style="margin-top:8px; display:flex; flex-direction:column; gap:6px;">
                            @foreach($especialidades as $esp)
                                <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px;
                                            padding:8px 12px; font-size:13px; font-weight:600; color:#0369a1;">
                                    {{ $esp->nombre_especialidad }}
                                    <span style="color:#64748b; font-weight:400;">
                                        — {{ $esp->nombre_materia }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color:#94a3b8; font-style:italic; margin-top:6px; font-size:14px;">
                            Sin especialidades registradas.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
