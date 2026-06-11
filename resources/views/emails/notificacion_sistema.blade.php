<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
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
        .message-box {
            background-color: #f7f9fc;
            border-left: 4px solid #0b2d6b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 15px;
            white-space: pre-line;
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
        <h2>Notificación de Sistema</h2>
        <p>Estimado usuario,</p>
        <p>Le comunicamos la siguiente información importante sobre su cuenta o proceso académico:</p>
        
        <div class="message-box">
            {!! nl2br(e($mensaje)) !!}
        </div>
        
        <p>Si tiene alguna consulta o inconveniente, por favor póngase en contacto con el departamento de soporte o la administración de la facultad.</p>
    </div>
    
    <div class="footer">
        Este es un correo automático generado por el sistema de notificaciones. Por favor, no responda directamente a este correo.<br>
        Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones - UAGRM
    </div>
</div>

</body>
</html>
