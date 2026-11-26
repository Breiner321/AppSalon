{{-- Estilos comunes de los reportes PDF. dompdf solo admite CSS 2.1 y tablas, sin flexbox ni grid.
     DejaVu Sans viene con dompdf e incluye tildes y ñ. --}}
<style>
    @page { margin: 1.4cm 1.4cm 1.6cm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #2B2B2B; }
    h1 { color: #7B2D5B; font-size: 17pt; margin: 0 0 2pt; }
    h2 { color: #7B2D5B; font-size: 11.5pt; margin: 14pt 0 5pt; border-bottom: 1.5pt solid #E8A0BF; padding-bottom: 2pt; }
    .marca { color: #7B2D5B; font-weight: bold; font-size: 10pt; }
    .suave { color: #6C6C6C; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #FAF6F8; color: #5E2245; text-align: left; font-size: 8.5pt; padding: 4pt 5pt; border-bottom: 1pt solid #d9cfd5; }
    td { padding: 3.5pt 5pt; border-bottom: 0.5pt solid #eee3e9; vertical-align: top; }
    .num { text-align: right; white-space: nowrap; }
    tfoot td { font-weight: bold; border-top: 1pt solid #7B2D5B; border-bottom: 0; }
    .resumen td { border: 0; padding: 0 6pt 0 0; width: 25%; }
    .tarjeta { background: #FAF6F8; border-left: 3pt solid #7B2D5B; padding: 5pt 7pt; }
    .tarjeta .valor { font-size: 14pt; font-weight: bold; color: #2B2B2B; }
    .estado { font-size: 7.5pt; padding: 1pt 4pt; border-radius: 3pt; color: #fff; }
    .estado-pendiente { background: #997404; }
    .estado-confirmada { background: #7B2D5B; }
    .estado-completada { background: #198754; }
    .estado-cancelada { background: #6c757d; }
    .barra-fondo { background: #f1edef; height: 8pt; width: 100%; }
    .barra { background: #7B2D5B; height: 8pt; }
    .pie { position: fixed; bottom: -1cm; left: 0; right: 0; font-size: 7.5pt; color: #6C6C6C; }
</style>
