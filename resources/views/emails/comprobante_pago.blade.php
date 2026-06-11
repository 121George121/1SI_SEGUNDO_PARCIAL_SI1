<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Pago - CUP FICCT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 0 auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid #e1e4e8;
        }
        .header {
            background-color: #0b2d6b;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .content h2 {
            color: #0b2d6b;
            font-size: 20px;
            margin-top: 0;
        }
        .details-box {
            background-color: #f7f9fc;
            border-left: 4px solid #0b2d6b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .details-box p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer {
            background-color: #f1f3f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777777;
            border-top: 1px solid #e1e4e8;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>CUP FICCT - UAGRM</h1>
    </div>
    
    <div class="content">
        <h2>¡Hola, {{ $pago->nombre }}!</h2>
        <p>Le informamos que el pago de su arancel de inscripción para el CUP Preuniversitario ha sido procesado de manera exitosa.</p>
        
        <p>A continuación se detallan los datos correspondientes a su transacción:</p>
        
        <div class="details-box">
            <p><strong>Código de Inscripción:</strong> {{ $pago->codigo_inscripcion }}</p>
            <p><strong>Concepto:</strong> {{ $pago->concepto_pago }}</p>
            <p><strong>Monto Liquidado:</strong> Bs. {{ number_format($pago->monto, 2) }}</p>
            <p><strong>Método de Pago:</strong> {{ strtoupper($pago->metodo_pago ?? 'PayPal') }}</p>
            <p><strong>Nro de Comprobante:</strong> {{ $pago->nro_comprobante ?? 'Generado automáticamente' }}</p>
        </div>
        
        <p>Adjunto a este correo electrónico encontrará su comprobante oficial de pago en formato PDF.</p>
        
        <p>Si tiene alguna consulta adicional, por favor no dude en ponerse en contacto con la administración.</p>
    </div>
    
    <div class="footer">
        Este es un correo automático, por favor no responda directamente.<br>
        Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones - UAGRM
    </div>
</div>

</body>
</html>
