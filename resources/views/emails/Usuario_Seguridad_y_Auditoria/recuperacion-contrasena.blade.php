<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperacion de contrasena</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.12);">
                    <tr>
                        <td style="background:#082f6f;padding:24px 28px;text-align:center;">
                            <p style="margin:0;color:#ffffff;font-size:22px;font-weight:bold;letter-spacing:.5px;">
                                Sistema de Inscripcion
                            </p>
                            <p style="margin:8px 0 0;color:#d9e4ff;font-size:13px;">
                                Universidad Nacional
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 12px;color:#333;font-size:16px;">
                                Hola, <strong>{{ $nombreUsuario }}</strong>
                            </p>
                            <p style="margin:0 0 20px;color:#555;font-size:14px;line-height:1.5;">
                                Recibimos una solicitud para restablecer tu contrasena.
                                Usa el siguiente codigo en la pagina de recuperacion:
                            </p>
                            <div style="text-align:center;margin:24px 0;">
                                <span style="display:inline-block;background:#f0f4ff;border:2px dashed #082f6f;color:#082f6f;font-size:32px;font-weight:bold;letter-spacing:8px;padding:14px 24px;border-radius:8px;">
                                    {{ $codigo }}
                                </span>
                            </div>
                            <p style="margin:0 0 8px;color:#b00020;font-size:13px;font-weight:bold;">
                                Este codigo vence en 10 minutos.
                            </p>
                            <p style="margin:0;color:#777;font-size:12px;line-height:1.5;">
                                Si no solicitaste este cambio, ignora este correo.
                                Tu cuenta permanecera segura.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8f9fc;padding:16px 28px;text-align:center;border-top:3px solid #d71920;">
                            <p style="margin:0;color:#888;font-size:11px;">
                                &copy; {{ date('Y') }} Sistema de Inscripcion - Todos los derechos reservados
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
