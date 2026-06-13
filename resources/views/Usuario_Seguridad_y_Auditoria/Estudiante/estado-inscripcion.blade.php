@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Estado de Inscripción - CUP FICCT')

@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding: 20px 0;">
    <!-- Header Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 30px; border-radius: 16px; color: #fff; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <span style="display: inline-block; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; border: 1px solid rgba(16, 185, 129, 0.3);">Requisitos de Admisión</span>
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Estado de Inscripción</h1>
        <p style="font-size: 14px; color: #93c5fd; margin: 0;">Revisa el estado de la validación de tus documentos presentados para concretar tu registro.</p>
    </div>

    <!-- General Status Overview -->
    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px; margin-bottom: 30px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
        <div>
            <span style="font-size: 13px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Estado General</span>
            <div style="margin-top: 6px; display: flex; align-items: center; gap: 10px;">
                @if($inscripcion)
                    @if(strtolower($inscripcion->estado) === 'inscrito')
                        <span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 6px 14px; border-radius: 30px; font-size: 14px; font-weight: 800; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                            Inscrito / Aceptado
                        </span>
                    @else
                        <span style="background: #fffbeb; color: #78350f; border: 1px solid #fde68a; padding: 6px 14px; border-radius: 30px; font-size: 14px; font-weight: 800; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                            En Revisión
                        </span>
                    @endif
                @else
                    <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; padding: 6px 14px; border-radius: 30px; font-size: 14px; font-weight: 800; text-transform: uppercase;">Sin Registro</span>
                @endif
            </div>
        </div>

        @if($inscripcion)
        <div>
            <span style="font-size: 13px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Fecha de Registro</span>
            <p style="font-size: 16px; color: #0f172a; font-weight: 700; margin-top: 6px;">
                {{ \Carbon\Carbon::parse($inscripcion->fecha_inscripcion)->format('d/m/Y') }}
            </p>
        </div>
        @endif
    </div>

    <!-- Documents Presentation Card -->
    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 24px; overflow: hidden;">
        <h3 style="font-size: 18px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Documentación Presentada</span>
        </h3>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; border: none; background: #fff;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0;">
                        <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: left;">Requisito Documental</th>
                        <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: left;">Tipo</th>
                        <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: center;">Estado</th>
                        <th style="background: transparent; border: none; padding: 14px; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; text-align: left;">Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentosLista as $doc)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="border: none; padding: 16px 14px; color: #0f172a; font-weight: 600; font-size: 14.5px;">{{ $doc['nombre'] }}</td>
                            <td style="border: none; padding: 16px 14px; color: #64748b; font-size: 14px;">{{ $doc['tipo'] }}</td>
                            <td style="border: none; padding: 16px 14px; text-align: center;">
                                @php
                                    $estadoClass = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                                    $est = strtolower(trim($doc['estado']));
                                    if ($est === 'validado' || $est === 'aprobado') {
                                        $estadoClass = 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;';
                                    } elseif ($est === 'observado') {
                                        $estadoClass = 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;';
                                    } elseif ($est === 'rechazado') {
                                        $estadoClass = 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;';
                                    } elseif ($est === 'en revision' || $est === 'en_revision') {
                                        $estadoClass = 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;';
                                    }
                                @endphp
                                <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 700; text-transform: uppercase; {{ $estadoClass }}">
                                    {{ str_replace('_', ' ', $doc['estado']) }}
                                </span>
                            </td>
                            <td style="border: none; padding: 16px 14px; font-size: 14px;">
                                @if(strtolower($doc['estado']) === 'observado' || strtolower($doc['estado']) === 'rechazado')
                                    <span style="color: #d97706; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        {{ $doc['observacion'] }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">{{ $doc['observacion'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="border: none; padding: 30px; text-align: center; color: #64748b; font-size: 14px;">
                                No se encontraron requisitos configurados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
