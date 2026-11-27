{{-- Documentación interactiva de la API (Swagger UI). Lee la especificación OpenAPI de
     public/api-docs/openapi.yaml. Swagger UI se carga por CDN, igual que Bootstrap. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentación de la API · {{ config('app.name', 'AppSalon') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui.css">
    <style>
        /* Siempre en modo claro: el modo oscuro de Swagger UI deja textos con poco contraste. */
        html { color-scheme: light; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #fff; }
        .barra { background: #7B2D5B; color: #fff; padding: .75rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem; }
        .barra a { color: #fff; }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
    <div class="barra">
        <strong>✂ {{ config('app.name', 'AppSalon') }} · Documentación de la API (Swagger)</strong>
        <span><a href="{{ asset('api-docs/openapi.yaml') }}">openapi.yaml</a> · <a href="{{ route('inicio') }}">Volver al sitio</a></span>
    </div>
    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui-bundle.js" crossorigin="anonymous"></script>
    <script>
        window.ui = SwaggerUIBundle({
            url: @json(asset('api-docs/openapi.yaml')),
            dom_id: '#swagger-ui',
            deepLinking: true,
            persistAuthorization: true,
            tryItOutEnabled: true,
            displayRequestDuration: true,
        });
    </script>
</body>
</html>
