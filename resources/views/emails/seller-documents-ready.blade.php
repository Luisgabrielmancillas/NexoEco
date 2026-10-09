<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Solicitud de vendedor por revisar · NexoEco</title></head>
<body style="margin:0;padding:0;background:#FAF7F2;font-family:Arial,Helvetica,sans-serif;color:#1C1917;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:36px 16px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
            <tr><td style="padding:0 8px 24px;font-size:26px;font-weight:800;">Nexo<span style="color:#E85D2F;">Eco</span></td></tr>
            <tr><td style="padding:32px 28px;background:#FFFFFF;border:1px solid #E8E0D4;border-top:5px solid #E85D2F;border-radius:16px;">
                <p style="font-size:11px;letter-spacing:1.5px;color:#B83E19;font-weight:bold;">REVISIÓN DE VENDEDORES</p>
                <h1 style="font-size:25px;line-height:1.3;margin:16px 0 22px;">{{ $correction ? 'Hay documentos corregidos por revisar.' : 'Un nuevo vendedor espera tu revisión.' }}</h1>
                <p style="font-size:14px;line-height:1.8;">Hola, {{ $moderatorName }}:</p>
                <p style="font-size:14px;line-height:1.8;color:#645A51;"><strong>{{ $sellerName }}</strong> {{ $correction ? 'volvió a enviar su documentación con las correcciones solicitadas' : 'envió su documentación para habilitar su cuenta de vendedor' }}.</p>
                <p style="padding:16px;background:#FAF7F2;border-radius:10px;font-size:13px;line-height:1.8;color:#645A51;">Solicitud <strong>#{{ $applicationId }}</strong><br>Enviada el {{ $submittedAt }} · Hora de Ciudad de México.</p>
                <p style="font-size:14px;line-height:1.8;color:#645A51;">Revisa los documentos desde el panel y comunica el resultado para que pueda continuar con su tienda.</p>
                <p style="margin:26px 0;"><a href="{{ $reviewUrl }}" style="display:inline-block;padding:14px 22px;background:#E85D2F;color:#FFFFFF;text-decoration:none;border-radius:10px;font-size:14px;font-weight:bold;">Revisar solicitud</a></p>
                <p style="font-size:12px;line-height:1.7;color:#746C66;">El enlace requiere iniciar sesión con tu cuenta de moderación. Los documentos se consultan de forma privada dentro de NexoEco.</p>
            </td></tr>
        </table>
    </td></tr></table>
</body></html>
