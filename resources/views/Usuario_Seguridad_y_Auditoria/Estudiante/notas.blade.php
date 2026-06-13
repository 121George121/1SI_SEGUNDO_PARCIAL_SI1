@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Mis Notas - CUP FICCT')

@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding: 20px 0;">
    <!-- Header Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 30px; border-radius: 16px; color: #fff; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <span style="display: inline-block; background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; border: 1px solid rgba(59, 130, 246, 0.3);">Historial Académico</span>
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Mis Calificaciones</h1>
        <p style="font-size: 14px; color: #93c5fd; margin: 0;">Consulta tus evaluaciones y notas registradas durante el Curso Preuniversitario. (Solo Lectura)</p>
    </div>

    <!-- Notes Card Table -->
    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px; overflow: hidden;">
        <h3 style="font-size: 18px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span>Planilla de Calificaciones</span>
        </h3>

        @if($notasMapeadas->isNotEmpty())
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; border: none; background: #fff;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0;">
                            <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: left;">Evaluación</th>
                            <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: right;">Nota</th>
                            <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: center;">Estado</th>
                            <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: left;">Fecha Registro</th>
                            <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: left;">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notasMapeadas as $nota)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="border: none; padding: 16px 14px; color: #0f172a; font-weight: 600; font-size: 14.5px;">{{ $nota['evaluacion'] }}</td>
                                <td style="border: none; padding: 16px 14px; color: #0f172a; font-weight: 800; font-size: 15px; text-align: right;">{{ number_format($nota['nota'], 2) }}</td>
                                <td style="border: none; padding: 16px 14px; text-align: center;">
                                    @if(strtolower(trim($nota['estado'])) === 'aprobado')
                                        <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                                            Aprobado
                                        </span>
                                    @else
                                        <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                                            {{ $nota['estado'] ?: 'Reprobado' }}
                                        </span>
                                    @endif
                                </td>
                                <td style="border: none; padding: 16px 14px; color: #64748b; font-size: 14px;">{{ \Carbon\Carbon::parse($nota['fecha'])->format('d/m/Y') }}</td>
                                <td style="border: none; padding: 16px 14px; color: #64748b; font-size: 14px; font-style: italic;">{{ $nota['observacion'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px 20px; color: #64748b;">
                <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 12px; color: #94a3b8;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 4px; color: #475569;">Aún no tienes notas registradas.</p>
                <p style="font-size: 13.5px; color: #94a3b8;">Las notas estarán visibles a medida que los docentes registren las calificaciones en el sistema.</p>
            </div>
        @endif
    </div>
</div>
@endsection
