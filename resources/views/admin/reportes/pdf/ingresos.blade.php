{{-- Reporte de ingresos por servicio y por día en PDF (Admin\ReporteController@ingresosPdf).
     Recibe $desde, $hasta, $resumen, $porServicio y $porDia (solo días con ingresos).
     Las barras son divs con ancho proporcional: dompdf no ejecuta JavaScript. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de ingresos</title>
    @include('admin.reportes.pdf._estilos')
</head>
<body>
    <div class="pie">{{ config('app.name') }} · Generado el {{ now()->format('d/m/Y H:i') }} · Ingresos = citas completadas</div>

    <div class="marca">✂ {{ config('app.name') }}</div>
    <h1>Reporte de ingresos</h1>
    <p class="suave">Período: {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</p>

    <table class="resumen">
        <tr>
            <td><div class="tarjeta"><div class="suave">Ingresos</div><div class="valor">${{ number_format($resumen['ingresos'], 2) }}</div></div></td>
            <td><div class="tarjeta"><div class="suave">Citas completadas</div><div class="valor">{{ $resumen['completada'] }}</div></div></td>
            <td><div class="tarjeta"><div class="suave">Ticket promedio</div><div class="valor">${{ number_format($resumen['ticketPromedio'], 2) }}</div></div></td>
            <td><div class="tarjeta"><div class="suave">Clientes atendidos</div><div class="valor">{{ $resumen['clientes'] }}</div></div></td>
        </tr>
    </table>

    <h2>Ingresos por servicio</h2>
    @if (empty($porServicio))
        <p class="suave">No hay citas completadas en este período.</p>
    @else
        @php $maximo = max(array_column($porServicio, 'ingresos')) ?: 1; @endphp
        <table>
            <thead>
                <tr><th style="width:32%">Servicio</th><th class="num">Veces</th><th class="num">Ingresos</th><th class="num">%</th><th style="width:30%"></th></tr>
            </thead>
            <tbody>
                @foreach ($porServicio as $s)
                    <tr>
                        <td>{{ $s['servicio'] }}</td>
                        <td class="num">{{ $s['veces'] }}</td>
                        <td class="num">${{ number_format($s['ingresos'], 2) }}</td>
                        <td class="num">{{ number_format($s['porcentaje'], 1) }}</td>
                        <td><div class="barra-fondo"><div class="barra" style="width: {{ round($s['ingresos'] * 100 / $maximo, 1) }}%"></div></div></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="num">{{ array_sum(array_column($porServicio, 'veces')) }}</td>
                    <td class="num">${{ number_format(array_sum(array_column($porServicio, 'ingresos')), 2) }}</td>
                    <td class="num">100.0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    <h2>Ingresos por día</h2>
    @if (empty($porDia))
        <p class="suave">Sin ingresos en este período.</p>
    @else
        @php $maximoDia = max(array_column($porDia, 'ingresos')) ?: 1; @endphp
        <table>
            <thead><tr><th style="width:22%">Día</th><th class="num">Completadas</th><th class="num">Ingresos</th><th style="width:45%"></th></tr></thead>
            <tbody>
                @foreach ($porDia as $dia)
                    <tr>
                        <td>{{ ucfirst(\Illuminate\Support\Carbon::parse($dia['fecha'])->translatedFormat('D d/m/Y')) }}</td>
                        <td class="num">{{ $dia['completada'] }}</td>
                        <td class="num">${{ number_format($dia['ingresos'], 2) }}</td>
                        <td><div class="barra-fondo"><div class="barra" style="width: {{ round($dia['ingresos'] * 100 / $maximoDia, 1) }}%"></div></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
