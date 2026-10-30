{{-- Correo de confirmación de cuenta (A7). Lo envía App\Mail\ConfirmarCuenta.
     Los correos no cargan CSS externo, por eso los estilos van en línea. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Confirma tu cuenta</title>
</head>
<body style="margin:0; padding:24px; background:#FAF6F8; font-family:Arial, Helvetica, sans-serif; color:#2B2B2B;">
    <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border-radius:8px; padding:24px;">
        <h1 style="color:#7B2D5B; font-size:22px; margin-top:0;">Hola, {{ $usuario->nombre }}</h1>

        <p>Gracias por crear tu cuenta en {{ config('app.name') }}.</p>
        <p>Para poder iniciar sesión, confirma tu cuenta con este enlace:</p>

        <p style="text-align:center; margin:28px 0;">
            <a href="{{ $enlace }}"
               style="background:#7B2D5B; color:#FFFFFF; text-decoration:none; padding:12px 20px; border-radius:6px; display:inline-block;">
                Confirmar mi cuenta
            </a>
        </p>

        <p style="font-size:13px; color:#6C6C6C;">
            Si el botón no funciona, copia esta dirección en el navegador:<br>
            {{ $enlace }}
        </p>
        <p style="font-size:13px; color:#6C6C6C;">Si no creaste esta cuenta, ignora este correo.</p>
    </div>
</body>
</html>
