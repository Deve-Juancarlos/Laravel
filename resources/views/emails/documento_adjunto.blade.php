<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $asunto ?? 'Envío de Documento' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="color: #333;">{{ $titulo }}</h2>
        <p>{{ $cuerpo }}</p>
        <p>Adjunto encontrará el documento en formato PDF.</p>
        <br>
        <p>Atentamente,<br>El equipo de SEDIMCORP SAC</p>
    </div>
</body>
</html>