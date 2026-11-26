{{-- Reporte de citas por período en PDF (Admin\ReporteController@citasPdf).
     Recibe $desde, $hasta, $estado, $resumen y $citas. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de citas</title>
    @include('admin.reportes.pdf._estilos')
</head>
<body>
    <div class="pie">{{ config('app.name') }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>

    <div class="marca">✂ {{ config('app.name') }}</div>
    <h1>Reporte de citas</h1>
    <p class="suave">
        Período: {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}
        @if ($estado) · Solo citas {{ $estado }}s @endif
    </p>

    <table class="resumen">
        <tr>
            <td><div class="tarjeta"><div class="suave">Citas</div><div class="valor">{{ $resumen['total'] }}</div></div></td>
            <td><div class="tarjeta"><div class="suave">Completadas</div><div class="valor">{{ $resumen['completada'] }}</div></div></td>
            <td><div class="tarjeta"><div class="suave">Por atender</div><div class="valor">{{ $resumen['pendiente'] + $resumen['confirmada'] }}</div></div></td>
            <td><div class="tarjeta"><div class="suave">Canceladas</div><div class="valor">{{ $resumen['cancelada'] }} <span class="suave" style="font-size:9pt">({{ $resumen['tasaCancelacion'] }} %)</span></div></div></td>
        </tr>
    </table>

    <h2>Detalle ({{ $citas->count() }} citas)</h2>
    @if ($citas->isEmpty())
        <p class="suave">No hay citas en este período.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Teléfono</th><th>Servicios</th>
                    <th class="num">Min</th><th class="num">Total</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($citas as $cita)
                    <tr>
                        <td class="suave">{{ $cita->id }}</td>
                        <td class="num">{{ $cita->fecha->format('d/m/Y') }}</td>
                        <td class="num">{{ $cita->horaCorta() }}–{{ $cita->horaFin() }}</td>
                        <td>{{ $cita->usuario?->nombreCompleto() ?? 'Usuario eliminado' }}</td>
                        <td>{{ $cita->usuario?->telefono }}</td>
                        <td>{{ $cita->servicios->pluck('nombre')->join(', ') }}</td>
                        <td class="num">{{ $cita->duracion }}</td>
                        <td class="num">${{ number_format((float) $cita->total, 2) }}</td>
                        <td><span class="estado estado-{{ $cita->estado }}">{{ $cita->estado }}</span></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">Total de las citas listadas</td>
                    <td class="num">${{ number_format((float) $citas->sum('total'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif
</body>
</html>
