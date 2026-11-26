{{-- Aviso de cita (reservada, modificada, confirmada o cancelada). Lo envía App\Mail\CitaNotificacion.
     Los correos no cargan CSS externo, por eso los estilos van en línea. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ \App\Mail\CitaNotificacion::TIPOS[$tipo]['asunto'] }}</title>
</head>
<body style="margin:0; padding:24px; background:#FAF6F8; font-family:Arial, Helvetica, sans-serif; color:#2B2B2B;">
    <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border-radius:8px; padding:24px;">
        <h1 style="color:#7B2D5B; font-size:22px; margin-top:0;">Hola, {{ $cita->usuario?->nombre }}</h1>

        <p>{{ $texto }}</p>

        <table style="width:100%; border-collapse:collapse; margin:16px 0;">
            <tr>
                <td style="padding:6px 0; color:#6C6C6C;">Fecha</td>
                <td style="padding:6px 0; text-align:right;">{{ $cita->fecha->translatedFormat('l j \d\e F \d\e Y') }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#6C6C6C;">Hora</td>
                <td style="padding:6px 0; text-align:right;">{{ $cita->horaCorta() }} a {{ $cita->horaFin() }}</td>
            </tr>
            @foreach ($cita->servicios as $servicio)
                <tr>
                    <td style="padding:6px 0; border-top:1px solid #EEE;">{{ $servicio->nombre }}</td>
                    <td style="padding:6px 0; border-top:1px solid #EEE; text-align:right;">${{ number_format((float) $servicio->precio, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="padding:8px 0; border-top:2px solid #7B2D5B; font-weight:bold;">Total</td>
                <td style="padding:8px 0; border-top:2px solid #7B2D5B; text-align:right; font-weight:bold;">${{ number_format((float) $cita->total, 2) }}</td>
            </tr>
        </table>

        <p style="text-align:center; margin:28px 0;">
            <a href="{{ $enlace }}"
               style="background:#7B2D5B; color:#FFFFFF; text-decoration:none; padding:12px 20px; border-radius:6px; display:inline-block;">
                Ver mis citas
            </a>
        </p>

        <p style="font-size:13px; color:#6C6C6C;">Si no reconoces esta cita, responde a este correo o comunícate con el salón.</p>
    </div>
</body>
</html>
