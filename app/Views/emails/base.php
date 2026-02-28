<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academia Heroicos</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family:'Segoe UI',system-ui,-apple-system,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:20px 0;">
        <tr>
            <td align="center">
                <!-- Header -->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">
                    <tr>
                        <td align="center" style="background:linear-gradient(135deg,#b720d2 0%,#8a189e 100%); padding:30px 20px; border-radius:12px 12px 0 0;">
                            <img src="<?= base_url('assets/images/heroicos.png') ?>" alt="Heroicos" width="70" height="70" style="border-radius:50%; background:#fff; padding:5px; display:block;">
                            <h1 style="color:#ffffff; margin:15px 0 0; font-size:22px; font-weight:700;">Heroicos Futbol Club</h1>
                            <p style="color:#f8e6fc; margin:5px 0 0; font-size:13px;">Academia de Futbol</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="background-color:#ffffff; padding:30px 35px; border-left:1px solid #e0e0e0; border-right:1px solid #e0e0e0;">
                            <?= $contenido ?>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9f9f9; padding:20px 35px; border-radius:0 0 12px 12px; border:1px solid #e0e0e0; border-top:none; text-align:center;">
                            <p style="color:#888; font-size:12px; margin:0 0 8px;">
                                &copy; <?= date('Y') ?> Heroicos Futbol Club - Academia de Futbol
                            </p>
                            <p style="color:#aaa; font-size:11px; margin:0;">
                                Este es un mensaje automatico, por favor no responda a este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
