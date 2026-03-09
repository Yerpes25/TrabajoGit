<!DOCTYPE html>
<html>
<head>
    <title>Configura tu contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Hola, {{ $client->name }}</h2>
    <p>El administrador te ha invitado a configurar tu contraseña para acceder a la plataforma de gestión de bonos.</p>
    
    <p>Por favor, haz clic en el siguiente botón para establecer tu contraseña. Este enlace es único y caducará en 48 horas por motivos de seguridad.</p>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Configurar mi contraseña
        </a>
    </p>

    <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
    <p><a href="{{ $url }}">{{ $url }}</a></p>

    <p>Un saludo,<br>El equipo de soporte.</p>
</body>
</html>