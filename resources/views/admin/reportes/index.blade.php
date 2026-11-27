{{-- Reportes del panel (requerimiento 9): resumen, gráficas, tablas y exportación.
     Recibe $desde, $hasta, $estado, $servicio, $servicios, $resumen, $porServicio, $porDia y $citas desde Admin\ReporteController@index.
     Las gráficas usan Chart.js; cada una tiene su tabla de datos para quien no pueda verla. --}}
@extends('layouts.app')

@section('title', 'Reportes')

@php
    $filtros = array_filter(['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'servicio' => $servicio?->id]);
    $rangos = [
        'Este mes' => [today()->startOfMonth(), today()->endOfMonth()],
        'Mes anterior' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
        'Últimos 30 días' => [today()->subDays(29), today()],
        'Este año' => [today()->startOfYear(), today()->endOfYear()],
    ];
    // Colores por estado: paleta categórica validada (orden fijo, ver docs/manual-tecnico.md).
    $colores = ['pendiente' => '#257de0', 'confirmada' => '#f47230', 'completada' => '#00b894', 'cancelada' => '#f2a600'];
    $dinero = fn ($v) => '$'.number_format((float) $v, 2);
@endphp

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Panel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reportes</li>
        </ol>
    </nav>

    <div class="mb-3">
        <h1 class="mb-1">Reportes</h1>
        <p class="text-body-secondary mb-0" data-test="periodo">
            Del {{ $desde->translatedFormat('j \d\e F \d\e Y') }} al {{ $hasta->translatedFormat('j \d\e F \d\e Y') }}.
            @if ($servicio) Solo citas con <strong data-test="servicio-filtrado">{{ $servicio->nombre }}</strong>. @endif
            Los ingresos cuentan solo las citas completadas.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.reportes.index') }}" class="filtros p-3 mb-2">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label for="desde" class="form-label small mb-1">Desde</label>
                <input type="date" name="desde" id="desde" class="form-control @error('desde') is-invalid @enderror" value="{{ $desde->toDateString() }}">
                <x-form-error field="desde" />
            </div>
            <div class="col-6 col-md-2">
                <label for="hasta" class="form-label small mb-1">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="form-control @error('hasta') is-invalid @enderror" value="{{ $hasta->toDateString() }}">
                <x-form-error field="hasta" />
            </div>
            <div class="col-12 col-md-3">
                <label for="servicio" class="form-label small mb-1">Servicio</label>
                <select name="servicio" id="servicio" class="form-select @error('servicio') is-invalid @enderror">
                    <option value="">Todos los servicios</option>
                    @foreach ($servicios as $s)
                        <option value="{{ $s->id }}" @selected($servicio?->id === $s->id)>{{ $s->nombre }}</option>
                    @endforeach
                </select>
                <x-form-error field="servicio" />
            </div>
            <div class="col-12 col-md-3">
                <label for="estado" class="form-label small mb-1">Estado (tabla de citas)</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Appointment::ESTADOS as $e)
                        <option value="{{ $e }}" @selected($estado === $e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel" aria-hidden="true"></i> Ver reporte</button>
            </div>
        </div>
    </form>
    <p class="small mb-4">
        Rangos rápidos:
        @foreach ($rangos as $nombre => [$d, $h])
            <a href="{{ route('admin.reportes.index', array_filter(['desde' => $d->toDateString(), 'hasta' => $h->toDateString(), 'servicio' => $servicio?->id])) }}"
               class="me-2">{{ $nombre }}</a>
        @endforeach
    </p>

    {{-- Resumen --}}
    @php
        $tarjetas = [
            ['Citas del período', $resumen['total'], 'citas-total', $resumen['completada'].' completadas · '.($resumen['pendiente'] + $resumen['confirmada']).' por atender'],
            ['Ingresos', $dinero($resumen['ingresos']), 'ingresos', $resumen['clientes'].' cliente(s) atendido(s)'],
            ['Ticket promedio', $dinero($resumen['ticketPromedio']), 'ticket', 'Por cita completada'],
            ['Canceladas', $resumen['cancelada'], 'canceladas', $resumen['tasaCancelacion'].' % de las citas'],
        ];
    @endphp
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        @foreach ($tarjetas as [$titulo, $valor, $test, $detalle])
            <div class="col">
                <article class="card card-estadistica h-100">
                    <div class="card-body">
                        <h2 class="h6 text-body-secondary mb-1">{{ $titulo }}</h2>
                        <p class="numero-estadistica mb-0" data-test="{{ $test }}">{{ $valor }}</p>
                        <p class="small text-body-secondary mb-0">{{ $detalle }}</p>
                    </div>
                </article>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        {{-- Citas por día --}}
        <div class="col-12 col-xl-7">
            <section class="card card-form-plano h-100" aria-labelledby="tituloCitasDia">
                <div class="card-body">
                    <h2 class="h5" id="tituloCitasDia">Citas por día</h2>
                    <div class="grafica"><canvas id="graficaCitas" role="img" aria-label="Gráfica de barras apiladas: citas por día y estado"></canvas></div>
                    <details class="mt-2">
                        <summary class="small">Ver los datos de la gráfica</summary>
                        <div class="table-responsive tabla-datos mt-2">
                            <table class="table table-sm small mb-0">
                                <thead><tr><th scope="col">Día</th>@foreach (\App\Models\Appointment::ESTADOS as $e)<th scope="col" class="text-end">{{ ucfirst($e) }}</th>@endforeach</tr></thead>
                                <tbody>
                                    @foreach ($porDia as $dia)
                                        <tr><td>{{ \Illuminate\Support\Carbon::parse($dia['fecha'])->format('d/m') }}</td>@foreach (\App\Models\Appointment::ESTADOS as $e)<td class="text-end">{{ $dia[$e] }}</td>@endforeach</tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </section>
        </div>

        {{-- Ingresos por día --}}
        <div class="col-12 col-xl-5">
            <section class="card card-form-plano h-100" aria-labelledby="tituloIngresosDia">
                <div class="card-body">
                    <h2 class="h5" id="tituloIngresosDia">Ingresos por día</h2>
                    <div class="grafica"><canvas id="graficaIngresos" role="img" aria-label="Gráfica de barras: ingresos por día"></canvas></div>
                    <details class="mt-2">
                        <summary class="small">Ver los datos de la gráfica</summary>
                        <div class="table-responsive tabla-datos mt-2">
                            <table class="table table-sm small mb-0">
                                <thead><tr><th scope="col">Día</th><th scope="col" class="text-end">Ingresos</th></tr></thead>
                                <tbody>
                                    @foreach ($porDia as $dia)
                                        @if ($dia['ingresos'] > 0)
                                            <tr><td>{{ \Illuminate\Support\Carbon::parse($dia['fecha'])->format('d/m') }}</td><td class="text-end">{{ $dinero($dia['ingresos']) }}</td></tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </section>
        </div>
    </div>

    {{-- Ingresos por servicio --}}
    <section class="card card-form-plano mb-4" aria-labelledby="tituloServicios">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <h2 class="h5 mb-0" id="tituloServicios">Ingresos por servicio</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reportes.ingresos.pdf', $filtros) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> PDF</a>
                    <a href="{{ route('admin.reportes.ingresos.csv', $filtros) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Excel (CSV)</a>
                </div>
            </div>

            @if (empty($porServicio))
                <p class="text-body-secondary mb-0">No hay citas completadas en este período.</p>
            @else
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="grafica" style="height: {{ max(14, count($porServicio) * 2.4) }}rem"><canvas id="graficaServicios" role="img" aria-label="Gráfica de barras horizontales: ingresos por servicio"></canvas></div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" data-test="tabla-servicios">
                                <thead>
                                    <tr>
                                        <th scope="col">Servicio</th>
                                        <th scope="col" class="text-end">Veces</th>
                                        <th scope="col" class="text-end">Ingresos</th>
                                        <th scope="col" class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($porServicio as $s)
                                        <tr>
                                            <td>{{ $s['servicio'] }}</td>
                                            <td class="text-end">{{ $s['veces'] }}</td>
                                            <td class="text-end text-nowrap">{{ $dinero($s['ingresos']) }}</td>
                                            <td class="text-end">{{ number_format($s['porcentaje'], 1) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-semibold">
                                        <td>Total</td>
                                        <td class="text-end">{{ collect($porServicio)->sum('veces') }}</td>
                                        <td class="text-end text-nowrap">{{ $dinero(collect($porServicio)->sum('ingresos')) }}</td>
                                        <td class="text-end">100.0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Citas del período --}}
    <section class="card card-form-plano" aria-labelledby="tituloCitas">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <h2 class="h5 mb-0" id="tituloCitas">
                    Citas del período @if ($estado)<span class="text-body-secondary">· {{ $estado }}</span>@endif
                    <span class="badge text-bg-light border" data-test="total-citas-reporte">{{ $citas->count() }}</span>
                </h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reportes.citas.pdf', $filtros + ['estado' => $estado]) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> PDF</a>
                    <a href="{{ route('admin.reportes.citas.csv', $filtros + ['estado' => $estado]) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Excel (CSV)</a>
                </div>
            </div>

            @if ($citas->isEmpty())
                <p class="text-body-secondary mb-0">No hay citas en este período.</p>
            @else
                <div class="table-responsive tabla-citas-reporte">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Fecha</th>
                                <th scope="col">Hora</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Servicios</th>
                                <th scope="col" class="text-end">Total</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($citas as $cita)
                                <tr>
                                    <td class="text-nowrap">{{ $cita->fecha->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">{{ $cita->horaCorta() }}</td>
                                    <td>{{ $cita->usuario?->nombreCompleto() ?? 'Usuario eliminado' }}</td>
                                    <td class="small">{{ $cita->servicios->pluck('nombre')->join(', ') }}</td>
                                    <td class="text-end text-nowrap">{{ $dinero($cita->total) }}</td>
                                    <td><span class="badge text-bg-{{ $cita->colorEstado() }} text-capitalize">{{ $cita->estado }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') {
                // Sin conexión al CDN las tablas de datos siguen disponibles.
                document.querySelectorAll('.grafica').forEach(function (g) { g.closest('.card-body').querySelector('details')?.setAttribute('open', ''); });
                return;
            }

            const porDia = @json($porDia);
            const porServicio = @json($porServicio);
            const colores = @json($colores);
            const estados = @json(\App\Models\Appointment::ESTADOS);
            const dinero = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
            const etiquetaDia = (f) => f.slice(8, 10) + '/' + f.slice(5, 7);

            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.color = '#6C6C6C';
            const ejes = {
                x: { grid: { display: false }, border: { color: '#ccc' } },
                y: { beginAtZero: true, grid: { color: '#eee' }, border: { display: false } },
            };

            new Chart(document.getElementById('graficaCitas'), {
                type: 'bar',
                data: {
                    labels: porDia.map((d) => etiquetaDia(d.fecha)),
                    datasets: estados.map((e) => ({
                        label: e.charAt(0).toUpperCase() + e.slice(1),
                        data: porDia.map((d) => d[e]),
                        backgroundColor: colores[e],
                        borderColor: '#fff',
                        borderWidth: { top: 2 },
                        borderRadius: 4,
                        borderSkipped: 'bottom',
                        maxBarThickness: 28,
                    })),
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top', align: 'start', labels: { boxWidth: 12, boxHeight: 12 } } },
                    scales: { x: { ...ejes.x, stacked: true }, y: { ...ejes.y, stacked: true, ticks: { precision: 0 } } },
                },
            });

            new Chart(document.getElementById('graficaIngresos'), {
                type: 'bar',
                data: {
                    labels: porDia.map((d) => etiquetaDia(d.fecha)),
                    datasets: [{ label: 'Ingresos', data: porDia.map((d) => d.ingresos), backgroundColor: '#7B2D5B', borderRadius: 4, maxBarThickness: 28 }],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => dinero.format(c.parsed.y) } } },
                    scales: { x: ejes.x, y: { ...ejes.y, ticks: { callback: (v) => '$' + v } } },
                },
            });

            const lienzoServicios = document.getElementById('graficaServicios');
            if (lienzoServicios) {
                new Chart(lienzoServicios, {
                    type: 'bar',
                    data: {
                        labels: porServicio.map((s) => s.servicio),
                        datasets: [{ label: 'Ingresos', data: porServicio.map((s) => s.ingresos), backgroundColor: '#7B2D5B', borderRadius: 4, maxBarThickness: 22 }],
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (c) => dinero.format(c.parsed.x) + ' · ' + porServicio[c.dataIndex].veces + ' vez/veces' } },
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#eee' }, border: { display: false }, ticks: { callback: (v) => '$' + v } },
                            y: { grid: { display: false }, border: { color: '#ccc' } },
                        },
                    },
                });
            }
        })();
    </script>
@endpush
