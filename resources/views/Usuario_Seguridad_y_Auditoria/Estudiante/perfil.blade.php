@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Perfil del Estudiante - CUP FICCT')

@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding: 20px 0;">
    <!-- Welcome Header -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 30px; border-radius: 16px; color: #fff; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
        <span style="display: inline-block; background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; border: 1px solid rgba(59, 130, 246, 0.3);">Portal del Estudiante</span>
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Mi Perfil</h1>
        <p style="font-size: 14px; color: #93c5fd; margin: 0;">Consulta y verifica tu información registrada en el sistema de admisión preuniversitario.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 24px; border-left: 5px solid #10b981; font-weight: 600;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom: 24px; border-left: 5px solid #ef4444; font-weight: 600; background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 6px;">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Profile Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 30px;">
        <!-- Card 1: Datos Personales -->
        <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; overflow: hidden; padding: 24px;">
            <h3 style="font-size: 18px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Datos Personales</span>
            </h3>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Cédula de Identidad (CI)</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $persona->ci }}</p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Nombre Completo</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ $persona->nombre }} {{ $persona->apellido }}</p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Sexo</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ $persona->sexo === 'M' ? 'Masculino' : ($persona->sexo === 'F' ? 'Femenino' : 'No especificado') }}</p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Fecha de Nacimiento</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Teléfono / Celular</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ $persona->telefono ?: 'No registrado' }}</p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Correo Electrónico</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ $persona->correo ?: 'No registrado' }}</p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Dirección de Domicilio</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ $persona->direccion ?: 'No registrada' }}</p>
                </div>
            </div>
        </div>

        <!-- Card 2: Cuenta & Admisión -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Cuenta de Usuario -->
            <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px; flex: 1;">
                <h3 style="font-size: 18px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11.5V10c0-3.313-2.687-6-6-6M9 11v1a1.5 1.5 0 001.5 1.5h1.534m4.103-6.223a3.5 3.5 0 11-4.828 4.828m6.414-2.828l4.243-4.243m-4.242 4.242L22 12m-2.828-2.828l-4.243 4.243"></path>
                    </svg>
                    <span>Cuenta de Acceso</span>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Nombre de Usuario</span>
                        <p style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $usuario->nombre_usuario }}</p>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Estado de Cuenta</span>
                        <div style="margin-top: 4px;">
                            <span style="background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">{{ $usuario->estado }}</span>
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Miembro Desde</span>
                        <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ \Carbon\Carbon::parse($usuario->fecha_creacion)->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Datos de Inscripción -->
            <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px; flex: 1;">
                <h3 style="font-size: 18px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span>Detalles Académicos</span>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @if($inscripcion)
                        <div>
                            <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Código de Inscripción</span>
                            <p style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">#{{ $inscripcion->Codigo_inscripcion }}</p>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Carrera Postulada</span>
                            <p style="font-size: 15px; color: #1e3a8a; font-weight: 700; margin-top: 2px;">{{ $carreraPostulada ?: 'No registrada' }}</p>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Estado de Inscripción</span>
                            <div style="margin-top: 4px;">
                                @if(strtolower($inscripcion->estado) === 'inscrito')
                                    <span style="background: #e6fffa; color: #047481; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Inscrito</span>
                                @else
                                    <span style="background: #fffbeb; color: #b45309; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">{{ str_replace('_', ' ', $inscripcion->estado) }}</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div style="text-align: center; padding: 20px 0; color: #64748b;">
                            <p style="font-size: 14px; font-weight: 500;">No cuentas con una inscripción registrada en esta gestión.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
