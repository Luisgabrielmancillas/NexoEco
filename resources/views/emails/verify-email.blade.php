<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Verifica tu correo · NexoEco</title></head>
<body style="margin:0;padding:0;background-color:#FAF7F2;font-family:Arial,Helvetica,sans-serif;color:#1C1917;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $seller ? 'Tu registro de vendedor está listo. Confirma tu correo para continuar con la verificación.' : 'Confirma tu correo y descubre productos de tu comunidad.' }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAF7F2;">
        <tr><td align="center" style="padding:40px 16px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                <tr><td style="padding:0 8px 24px;font-size:26px;font-weight:800;letter-spacing:-1px;">Nexo<span style="color:#E85D2F;">Eco</span><span style="display:block;margin-top:6px;font-size:11px;font-weight:400;letter-spacing:2px;color:#746C66;">TU COMUNIDAD, MÁS CERCA</span></td></tr>
                <tr><td style="background-color:#FFFFFF;border:1px solid #E8E0D4;border-radius:20px;overflow:hidden;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr><td style="height:6px;background-color:#E85D2F;border-radius:20px 20px 0 0;"></td></tr>
                        <tr><td style="padding:36px 28px;">
                            <p style="margin:0 0 16px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#B83E19;">{{ $seller ? 'REGISTRO DE VENDEDOR' : 'BIENVENIDO A NEXOECO' }}</p>
                            <h1 style="margin:0 0 20px;font-size:28px;font-weight:800;line-height:1.25;letter-spacing:-.5px;">{{ $seller ? 'Tu próximo paso empieza aquí.' : 'Tu comunidad te espera.' }}</h1>
                            <p style="margin:0 0 14px;font-size:15px;line-height:1.8;">Hola, {{ $name }}:</p>
                            <p style="margin:0 0 24px;font-size:14px;line-height:1.8;color:#645A51;">{{ $seller ? 'Recibimos tu registro y documentación para vender en NexoEco. Confirma tu correo electrónico para continuar con la verificación de tu solicitud.' : 'Gracias por crear tu cuenta de comprador en NexoEco. Confirma tu correo electrónico para empezar a descubrir productos locales y conectar con tu comunidad.' }}</p>
                            <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#645A51;">Tu código de verificación</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td bgcolor="#FFF0EA" align="center" style="padding:22px;border:1px solid #F5D7C9;border-radius:12px;color:#B83E19;font-size:38px;font-weight:800;letter-spacing:14px;font-family:Arial,Helvetica,sans-serif;">{{ $code }}</td></tr></table>
                            <p style="margin:16px 0 24px;font-size:13px;line-height:1.7;color:#645A51;">Ingresa estos 4 dígitos en la pantalla de verificación de NexoEco. El código es válido durante {{ $expires }} minutos. Si vence, solicita uno nuevo desde tu cuenta.</p>
                            @if ($seller)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:18px;background-color:#FAF7F2;border-radius:12px;font-size:13px;line-height:1.8;color:#645A51;"><strong>Tu cuenta de vendedor está en revisión</strong><br>Al verificar tu correo entrarás al dashboard del comprador. Podrás comprar mientras nuestro equipo revisa tus datos y documentos para habilitarte como vendedor.</td></tr></table>
                            @endif
                            <p style="margin:24px 0 0;font-size:12px;line-height:1.7;color:#746C66;">No compartas este código. NexoEco no te lo solicitará por teléfono ni por mensajes.</p>
                        </td></tr>
                    </table>
                </td></tr>
                <tr><td style="padding:24px 16px;text-align:center;font-size:11px;line-height:1.8;color:#746C66;">Si no creaste esta cuenta, puedes ignorar este correo.<br>© {{ date('Y') }} NexoEco · Hecho para conectar con tu comunidad.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
