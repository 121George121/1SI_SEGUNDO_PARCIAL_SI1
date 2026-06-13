@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Estado de Admisión - CUP FICCT')

@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding: 20px 0;">
    <!-- Header Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 30px; border-radius: 16px; color: #fff; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <span style="display: inline-block; background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; border: 1px solid rgba(239, 68, 68, 0.3);">Admisión CUP</span>
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Estado de Admisión</h1>
        <p style="font-size: 14px; color: #93c5fd; margin: 0;">Consulta si has sido admitido a la carrera postulada y los detalles de tu asignación.</p>
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

    <!-- Admission Status Box -->
    @php
        $boxStyle = 'background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;';
        $statusIcon = '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        
        if ($estado_admision === 'Aceptado') {
            $boxStyle = 'background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;';
            $statusIcon = '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        } elseif ($estado_admision === 'Rechazado') {
            $boxStyle = 'background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;';
            $statusIcon = '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        } elseif ($estado_admision === 'En revisión') {
            $boxStyle = 'background: #fffbeb; border: 1px solid #fde68a; color: #78350f;';
            $statusIcon = '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        }
    @endphp

    <div style="{{ $boxStyle }} padding: 24px; border-radius: 16px; margin-bottom: 30px; display: flex; align-items: flex-start; gap: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
        <div style="flex-shrink: 0; padding-top: 2px;">
            {!! $statusIcon !!}
        </div>
        <div>
            <h2 style="font-size: 19px; font-weight: 800; margin-bottom: 6px;">Estado: {{ $estado_admision }}</h2>
            <p style="font-size: 15px; font-weight: 600; line-height: 1.5; margin: 0;">{{ $mensaje_admision }}</p>
        </div>
    </div>

    <!-- Details Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 30px;">
        <!-- Card 1: Carrera Postulada -->
        <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px;">
            <h3 style="font-size: 17px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span>Carrera de Postulación</span>
            </h3>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Carrera Principal</span>
                    <p style="font-size: 16px; color: #1e3a8a; font-weight: 700; margin-top: 2px;">{{ $carreraPostulada ?: 'No registrada' }}</p>
                </div>
            </div>
        </div>

        <!-- Card 2: Detalles de Asignación Académica si fue Aceptado -->
        @if($estado_admision === 'Aceptado')
            <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px;">
                <h3 style="font-size: 17px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span>Asignación Universitaria</span>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Carrera Admitida</span>
                        <p style="font-size: 16px; color: #10b981; font-weight: 700; margin-top: 2px;">{{ $carrera_aceptada }}</p>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Grupo Asignado</span>
                        <p style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $grupo_asignado ?: 'Pendiente de asignación' }}</p>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Turno Académico</span>
                        <p style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $turno_asignado ?: 'Pendiente de asignación' }}</p>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Fecha de Admisión</span>
                        <p style="font-size: 15px; color: #0f172a; font-weight: 500; margin-top: 2px;">{{ \Carbon\Carbon::parse($fecha_admision)->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Estado de Matrícula</span>
                        <div style="margin-top: 4px;">
                            @if($matriculaPagada)
                                <span style="background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Pagado</span>
                            @else
                                <span style="background: #fffbeb; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Pendiente de Pago</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Boleta de Inscripción Section -->
    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px; text-align: center;">
        <h3 style="font-size: 18px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <span>Boleta Oficial de Inscripción</span>
        </h3>

        @if($estado_admision === 'Aceptado' && $matriculaPagada)
            <p style="font-size: 14.5px; color: #475569; margin-bottom: 20px; max-width: 600px; margin-left: auto; margin-right: auto;">
                Tu inscripción y matrícula han sido procesadas correctamente. Puedes visualizar y descargar tu boleta oficial haciendo clic en el botón de abajo.
            </p>
            <a href="{{ route('estudiante.boleta-inscripcion') }}" class="btn" style="background: #1e3a8a; color: #fff; font-weight: 700; font-size: 15px; padding: 12px 28px; border-radius: 8px; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2); transition: all 0.2s;">
                Ver boleta de inscripción
            </a>
        @elseif($estado_admision === 'Aceptado' && !$matriculaPagada)
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; color: #78350f; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; max-width: 600px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Para ver tu boleta de inscripción, primero debes pagar tu matrícula.</span>
            </div>
        @else
            <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; color: #475569; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; max-width: 600px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>La boleta de inscripción estará disponible cuando seas aceptado a una carrera.</span>
            </div>
        @endif
    </div>
</div>
@endsection
