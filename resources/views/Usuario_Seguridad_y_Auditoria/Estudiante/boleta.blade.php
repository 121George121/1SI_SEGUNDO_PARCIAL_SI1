@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Boleta de Inscripción - CUP FICCT')

@section('content')
<style>
    /* Printing styles */
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-boleta, #printable-boleta * {
            visibility: visible;
        }
        #printable-boleta {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div style="max-width: 900px; margin: 0 auto; padding: 20px 0;">
    <!-- Actions Bar -->
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <a href="{{ route('estudiante.estado-admision') }}" class="btn btn-secondary" style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Volver</span>
        </a>
        <button onclick="window.print()" class="btn" style="background: #ef4444; color: #fff; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Imprimir Boleta</span>
        </button>
    </div>

    <!-- Certificate / Boleta Document Card -->
    <div id="printable-boleta" style="background: #fff; border-radius: 16px; border: 2px solid #e2e8f0; box-shadow: 0 4px 30px rgba(15, 23, 42, 0.04); overflow: hidden; padding: 40px; font-family: 'Outfit', sans-serif;">
        <!-- Header Sheet -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px double #e2e8f0; padding-bottom: 24px; margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo UAGRM FICCT" style="width: 70px; height: 70px; object-fit: contain;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 850; color: #1e3a8a; margin: 0;">U.A.G.R.M.</h2>
                    <h3 style="font-size: 13px; font-weight: 750; color: #ef4444; margin: 2px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones</h3>
                </div>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Boleta de Inscripción</span>
                <p style="font-size: 18px; color: #1e3a8a; font-weight: 850; margin-top: 2px;">No. INS-{{ str_pad($inscripcion->Codigo_inscripcion, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>

        <!-- Title of Document -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Constancia de Inscripción y Registro</h1>
            <p style="font-size: 13px; color: #64748b; margin-top: 4px;">Curso Preuniversitario (CUP)</p>
        </div>

        <!-- Student Information Details -->
        <div style="margin-bottom: 30px;">
            <h4 style="font-size: 13px; font-weight: 750; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 16px;">Datos del Postulante</h4>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Nombre Completo</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $persona->nombre }} {{ $persona->apellido }}</p>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Documento de Identidad (CI)</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $persona->ci }}</p>
                </div>
            </div>
        </div>

        <!-- Academic Information Details -->
        <div style="margin-bottom: 30px;">
            <h4 style="font-size: 13px; font-weight: 750; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 16px;">Detalles Académicos e Inscripción</h4>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Carrera Asignada</span>
                    <p style="font-size: 15px; color: #1e3a8a; font-weight: 700; margin-top: 2px;">{{ $carreraAceptada }}</p>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Fecha de Inscripción</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 600; margin-top: 2px;">{{ \Carbon\Carbon::parse($inscripcion->fecha_inscripcion)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Grupo Académico</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 600; margin-top: 2px;">{{ $grupo_sigla }}</p>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Turno Asignado</span>
                    <p style="font-size: 15px; color: #0f172a; font-weight: 600; margin-top: 2px;">{{ $turno_nombre }}</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Estado de Admisión</span>
                    <p style="font-size: 14px; color: #059669; font-weight: 700; margin-top: 2px; text-transform: uppercase;">Admitido / Aprobado</p>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Detalle de Pago Matrícula</span>
                    <p style="font-size: 14px; color: #059669; font-weight: 700; margin-top: 2px; text-transform: uppercase;">Liquidado (Comprobante: {{ $nroComprobante }})</p>
                </div>
            </div>
        </div>

        <!-- Schedules Section -->
        <div style="margin-bottom: 40px;">
            <h4 style="font-size: 13px; font-weight: 750; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 16px;">Horarios de Clases Asignados</h4>
            @if($horarios->isNotEmpty())
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: none;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 10px 14px; text-align: left; font-weight: 700; color: #475569; font-size: 12.5px; text-transform: uppercase; border: 1px solid #e2e8f0;">Materia</th>
                            <th style="padding: 10px 14px; text-align: left; font-weight: 700; color: #475569; font-size: 12.5px; text-transform: uppercase; border: 1px solid #e2e8f0;">Día</th>
                            <th style="padding: 10px 14px; text-align: center; font-weight: 700; color: #475569; font-size: 12.5px; text-transform: uppercase; border: 1px solid #e2e8f0;">Hora Inicio</th>
                            <th style="padding: 10px 14px; text-align: center; font-weight: 700; color: #475569; font-size: 12.5px; text-transform: uppercase; border: 1px solid #e2e8f0;">Hora Fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($horarios as $h)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; color: #0f172a; font-weight: 600; font-size: 13.5px; border: 1px solid #e2e8f0;">{{ $h->materia }}</td>
                                <td style="padding: 12px 14px; color: #475569; font-size: 13px; border: 1px solid #e2e8f0;">{{ $h->dia }}</td>
                                <td style="padding: 12px 14px; color: #475569; font-size: 13px; text-align: center; border: 1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($h->hora_inicio)->format('H:i') }}</td>
                                <td style="padding: 12px 14px; color: #475569; font-size: 13px; text-align: center; border: 1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($h->hora_fin)->format('H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 20px; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b; font-size: 13.5px;">
                    Horario pendiente de asignación por la administración académica.
                </div>
            @endif
        </div>

        <!-- Footer Seal and Signatures -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 60px;">
            <div style="text-align: center; width: 200px;">
                <div style="border-top: 1px solid #94a3b8; padding-top: 8px;">
                    <p style="font-size: 12px; color: #64748b; font-weight: 600;">Firma del Postulante</p>
                </div>
            </div>
            <div style="text-align: center;">
                <p style="font-size: 11px; color: #94a3b8; font-style: italic;">Santa Cruz de la Sierra, {{ now()->format('d/m/Y') }}</p>
            </div>
            <div style="text-align: center; width: 220px;">
                <div style="border-top: 1px solid #94a3b8; padding-top: 8px;">
                    <p style="font-size: 12px; color: #64748b; font-weight: 600;">Dirección de Admisiones</p>
                    <p style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">U.A.G.R.M. FICCT</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
