{{-- Panel de administración: estadísticas del día y del mes, citas de hoy y accesos rápidos.
     Recibe los conteos y $citasHoy desde AdminController@index. --}}
@extends('layouts.app')

@section('title', 'Panel de administración')

@php
    $tarjetas = [
        ['valor' => $citasHoyActivas, 'titulo' => 'Citas de hoy', 'icono' => 'bi-calendar-check', 'color' => 'text-primary', 'test' => 'citas-hoy',
            'detalle' => $proximasSemana.' en los próximos 7 días'],
        ['valor' => '$'.number_format($ingresosHoy, 2), 'titulo' => 'Ingresos de hoy', 'icono' => 'bi-cash-coin', 'color' => 'text-success', 'test' => 'ingresos-hoy',
            'detalle' => '$'.number_format($ingresosEsperadosHoy, 2).' esperados con las citas del día'],
        ['valor' => '$'.number_format($ingresosMes, 2), 'titulo' => 'Ingresos del mes', 'icono' => 'bi-graph-up-arrow', 'color' => 'text-success', 'test' => 'ingresos-mes',
            'detalle' => 'Citas completadas de '.today()->translatedFormat('F')],
        ['valor' => $clientes, 'titulo' => 'Clientes', 'icono' => 'bi-person-hearts', 'color' => 'text-primary', 'test' => 'clientes',
            'detalle' => $clientesNuevosMes.' nuevos este mes'],
    ];
@endphp

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item active" aria-current="page">Panel</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="mb-1">Panel de administración</h1>
            <p class="text-body-secondary mb-0">Hola, {{ auth()->user()->nombre }}. Este es el resumen del salón.</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <a href="{{ route('admin.agenda') }}" class="btn btn-primary">
                <i class="bi bi-calendar-week" aria-hidden="true"></i> Ver agenda
            </a>
            <a href="{{ route('admin.servicios.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo servicio
            </a>
            <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-list-check" aria-hidden="true"></i> Ver servicios
            </a>
        </div>
    </div>

    @if ($pendientes > 0)
        <div class="alert alert-warning d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <span><i class="bi bi-hourglass-split" aria-hidden="true"></i>
                Hay <strong data-test="pendientes">{{ $pendientes }}</strong> cita(s) pendiente(s) de confirmar.</span>
            <a href="{{ route('admin.citas.index', ['estado' => 'pendiente', 'desde' => today()->toDateString()]) }}" class="btn btn-sm btn-outline-dark">Revisarlas</a>
        </div>
    @endif

    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4 mb-4">
        @foreach ($tarjetas as $t)
            <div class="col">
                <article class="card card-estadistica h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="icono-estadistica {{ $t['color'] }}"><i class="bi {{ $t['icono'] }}" aria-hidden="true"></i></span>
                        <div>
                            <p class="numero-estadistica mb-0" data-test="{{ $t['test'] }}">{{ $t['valor'] }}</p>
                            <h2 class="h6 text-body-secondary mb-0">{{ $t['titulo'] }}</h2>
                            <p class="small text-body-secondary mb-0">{{ $t['detalle'] }}</p>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <section class="card agenda-dia h-100" aria-labelledby="tituloHoy">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0" id="tituloHoy">Citas de hoy · {{ today()->translatedFormat('l j \d\e F') }}</h2>
                        <a href="{{ route('admin.agenda') }}" class="small">Agenda completa</a>
                    </div>

                    @forelse ($citasHoy as $cita)
                        <div class="agenda-cita estado-{{ $cita->estado }} d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 mb-2">
                            <span class="agenda-hora">{{ $cita->horaCorta() }}–{{ $cita->horaFin() }}</span>
                            <span class="flex-grow-1">
                                <span class="d-block fw-semibold">{{ $cita->usuario?->nombreCompleto() ?? 'Usuario eliminado' }}</span>
                                <span class="small text-body-secondary">{{ $cita->servicios->pluck('nombre')->join(', ') }}</span>
                            </span>
                            <span class="badge text-bg-{{ $cita->colorEstado() }} text-capitalize">{{ $cita->estado }}</span>
                        </div>
                    @empty
                        <p class="text-body-secondary mb-0">No hay citas para hoy.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-4">
            <div class="d-flex flex-column gap-3">
                <article class="card card-estadistica">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="icono-estadistica text-success"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                        <div>
                            <p class="numero-estadistica mb-0" data-test="servicios-activos">{{ $serviciosActivos }}</p>
                            <h2 class="h6 text-body-secondary mb-0">Servicios activos</h2>
                        </div>
                    </div>
                </article>
                <article class="card card-estadistica">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="icono-estadistica text-secondary"><i class="bi bi-eye-slash" aria-hidden="true"></i></span>
                        <div>
                            <p class="numero-estadistica mb-0" data-test="servicios-inactivos">{{ $serviciosInactivos }}</p>
                            <h2 class="h6 text-body-secondary mb-0">Servicios inactivos</h2>
                        </div>
                    </div>
                </article>
                <article class="card card-estadistica">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="icono-estadistica text-primary"><i class="bi bi-people" aria-hidden="true"></i></span>
                        <div>
                            <p class="numero-estadistica mb-0" data-test="usuarios-registrados">{{ $usuariosRegistrados }}</p>
                            <h2 class="h6 text-body-secondary mb-0">Usuarios registrados</h2>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
@endsection
