{{-- Error 500: falla interna del servidor. Es HTML autónomo (no extiende layouts.app)
     para que se pueda mostrar aunque el layout, la sesión o la base de datos sean la causa del error. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del servidor · AppSalon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background: #FAF6F8; color: #2B2B2B; }
        .btn-salon { background: #7B2D5B; border-color: #7B2D5B; color: #fff; }
        .btn-salon:hover, .btn-salon:focus { background: #5E2245; border-color: #5E2245; color: #fff; }
        .titulo { color: #7B2D5B; }
        h1, .titulo { font-family: "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-weight: 600; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <main class="container text-center py-5">
        <p class="display-1 fw-bold titulo mb-0">500</p>
        <h1 class="h2 mt-3">Algo salió mal de nuestro lado</h1>
        <p class="text-body-secondary mb-4">
            Tuvimos un problema al procesar tu solicitud. No es tu culpa.
            Intenta de nuevo en unos minutos.
        </p>
        <a href="{{ route('servicios.catalogo') }}" class="btn btn-salon">Volver a los servicios</a>
    </main>
</body>
</html>
