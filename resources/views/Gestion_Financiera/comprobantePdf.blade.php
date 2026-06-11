<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago - CUP FICCT</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            padding: 30px;
            background: #fff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-table td {
            padding: 0;
            border: none;
            vertical-align: top;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #0b2d6b;
            letter-spacing: 1px;
        }
        .title {
            text-align: right;
            font-size: 20px;
            color: #555;
            text-transform: uppercase;
            font-weight: bold;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table td {
            padding: 8px 0;
            border: none;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #0b2d6b;
            width: 150px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background: #0b2d6b;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0b2d6b;
        }
        .items-table td {
            padding: 12px 10px;
            border: 1px solid #ddd;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-section {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #0b2d6b;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <table class="header-table">
        <tr>
            <td class="logo">
                CUP FICCT
            </td>
            <td class="title">
                Comprobante de Pago
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td class="info-label">Nro Comprobante:</td>
            <td>{{ $pago->nro_comprobante ?? 'Sin número' }}</td>
            <td class="info-label">Fecha de Emisión:</td>
            <td>{{ $pago->fecha_emision ?? date('Y-m-d') }}</td>
        </tr>
        <tr>
            <td class="info-label">Postulante:</td>
            <td>{{ $pago->nombre }} {{ $pago->apellido }}</td>
            <td class="info-label">C.I. Postulante:</td>
            <td>{{ $pago->ci }}</td>
        </tr>
        <tr>
            <td class="info-label">Carrera Principal:</td>
            <td>{{ $pago->carrera_principal ?? 'Sin carrera' }}</td>
            <td class="info-label">Código Inscripción:</td>
            <td>{{ $pago->codigo_inscripcion }}</td>
        </tr>
        <tr>
            <td class="info-label">Método de Pago:</td>
            <td>{{ strtoupper($pago->metodo_pago ?? 'manual') }}</td>
            <td class="info-label">Referencia / Id:</td>
            <td>{{ $pago->referencia_pago ?? '-' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Concepto de Pago</th>
                <th>Observaciones</th>
                <th style="text-align: right; width: 150px;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $pago->concepto_pago }}</td>
                <td>{{ $pago->observaciones ?? 'Pago de arancel por concepto de inscripción' }}</td>
                <td style="text-align: right; font-weight: bold;">Bs. {{ number_format($pago->monto, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        Total Liquidado: Bs. {{ number_format($pago->monto, 2) }}
    </div>

    <div class="footer">
        Este documento sirve como comprobante oficial del pago de inscripción al CUP FICCT.<br>
        Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones - UAGRM<br>
        <strong>¡Muchas gracias por su pago!</strong>
    </div>
</div>

</body>
</html>
