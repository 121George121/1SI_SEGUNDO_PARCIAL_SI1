@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Pagar Matrícula - CUP FICCT')

@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding: 20px 0;">
    <!-- Header Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 30px; border-radius: 16px; color: #fff; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <span style="display: inline-block; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; border: 1px solid rgba(16, 185, 129, 0.3);">Pagos y Aranceles</span>
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Pagar Matrícula</h1>
        <p style="font-size: 14px; color: #93c5fd; margin: 0;">Realiza el pago de tu matrícula mediante nuestra pasarela integrada de PayPal de forma segura.</p>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 28px; padding: 16px 20px; background: #ecfdf5; color: #065f46; border-radius: 12px; font-weight: 600; border-left: 5px solid #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08); font-size: 14.5px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom: 28px; padding: 16px 20px; background: #fef2f2; color: #991b1b; border-radius: 12px; font-weight: 600; border-left: 5px solid #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08); font-size: 14.5px;">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Payment Detail Card -->
    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; padding: 30px; max-width: 600px; margin: 0 auto;">
        <h3 style="font-size: 19px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            <span>Detalle de Matrícula</span>
        </h3>

        <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #f8fafc;">
                <span style="font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase;">Concepto del pago</span>
                <span style="font-size: 16px; color: #0f172a; font-weight: 700;">{{ $pagoMatricula->concepto_pago }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #f8fafc;">
                <span style="font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase;">Monto Arancel</span>
                <span style="font-size: 20px; color: #1e3a8a; font-weight: 850;">Bs. {{ number_format($pagoMatricula->monto, 2) }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #f8fafc;">
                <span style="font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase;">Estado actual</span>
                @php
                    $isPaid = strtolower(trim($pagoInscripcion->estado_pago_inscripcion)) === 'liquidado';
                @endphp
                @if($isPaid)
                    <span style="background: #ecfdf5; color: #059669; padding: 4px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 700; text-transform: uppercase; border: 1px solid #a7f3d0;">Pagado</span>
                @else
                    <span style="background: #fffbeb; color: #d97706; padding: 4px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 700; text-transform: uppercase; border: 1px solid #fde68a;">{{ $pagoInscripcion->estado_pago_inscripcion ?: 'Pendiente' }}</span>
                @endif
            </div>

            @if($pagoInscripcion->fecha_pago)
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px;">
                <span style="font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase;">Fecha de Pago</span>
                <span style="font-size: 15px; color: #0f172a; font-weight: 600;">{{ \Carbon\Carbon::parse($pagoInscripcion->fecha_pago)->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>

        <div style="text-align: center; border-top: 1px solid #f1f5f9; padding-top: 24px;">
            @if($isPaid)
                <div style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Tu matrícula ya fue pagada correctamente.</span>
                </div>
            @else
                <p style="font-size: 14px; color: #64748b; margin-bottom: 20px; line-height: 1.5;">
                    Haz clic en el botón de abajo para ser redirigido a la pasarela de PayPal y completar el pago electrónico. El arancel será cobrado en dólares equivalentes (Bs. {{ number_format($pagoMatricula->monto, 2) }} / 7).
                </p>
                <form action="{{ route('pagos.paypal.pagar', [$pagoMatricula->Id_pago, $inscripcion->Codigo_inscripcion]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn" style="background: #0070ba; color: #fff; font-weight: 800; font-size: 15px; padding: 12px 32px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 112, 186, 0.3); transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer;">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: #fff;">
                            <path d="M20.007 8.002c-.156-1.576-1.077-3.003-2.607-3.565-1.614-.59-4.218-.59-5.591-.59H7.031c-.687 0-1.282.493-1.402 1.17L3.064 19.895a.754.754 0 00.742.885h3.987l.794-5.06a1.455 1.455 0 011.432-1.23h1.86c2.812 0 5.06-1.442 5.688-4.256.402-1.79.034-3.327-.56-4.232z"></path>
                        </svg>
                        <span>Pagar con PayPal</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
