{{-- Agenda del día o de la semana. Recibe $vista, $fecha, $desde, $hasta, $dias (citas agrupadas
     por fecha), $anterior, $siguiente, $transiciones y $resumen desde Admin\CitaController@agenda. --}}
@extends('layouts.app')

@section('title', 'Agenda')

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Panel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Agenda</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="mb-1">Agenda</h1>
            <p class="text-body-secondary mb-0" data-test="rango-agenda">
                @if ($vista === 'semana')
                    Del {{ $desde->translatedFormat('j \d\e F') }} al {{ $hasta->translatedFormat('j \d\e F \d\e Y') }}
                @else
                    {{ ucfirst($fecha->translatedFormat('l j \d\e F \d\e Y')) }}
                @endif
                · {{ $resumen['total'] }} cita(s) · ${{ number_format($resumen['ingresos'], 2) }}
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group" role="group" aria-label="Vista">
                <a href="{{ route('admin.agenda', ['vista' => 'dia', 'fecha' => $fecha->toDateString()]) }}"
                   class="btn {{ $vista === 'dia' ? 'btn-primary' : 'btn-outline-primary' }}" @if ($vista === 'dia') aria-current="page" @endif>Día</a>
                <a href="{{ route('admin.agenda', ['vista' => 'semana', 'fecha' => $fecha->toDateString()]) }}"
                   class="btn {{ $vista === 'semana' ? 'btn-primary' : 'btn-outline-primary' }}" @if ($vista === 'semana') aria-current="page" @endif>Semana</a>
            </div>
            <div class="btn-group" role="group" aria-label="Navegar">
                <a href="{{ route('admin.agenda', ['vista' => $vista, 'fecha' => $anterior]) }}" class="btn btn-outline-primary" aria-label="Anterior">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </a>
                <a href="{{ route('admin.agenda', ['vista' => $vista]) }}" class="btn btn-outline-primary">Hoy</a>
                <a href="{{ route('admin.agenda', ['vista' => $vista, 'fecha' => $siguiente]) }}" class="btn btn-outline-primary" aria-label="Siguiente">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            </div>
            <form method="GET" action="{{ route('admin.agenda') }}" class="d-flex gap-1">
                <input type="hidden" name="vista" value="{{ $vista }}">
                <label for="irFecha" class="visually-hidden">Ir a la fecha</label>
                <input type="date" name="fecha" id="irFecha" class="form-control" value="{{ $fecha->toDateString() }}">
                <button type="submit" class="btn btn-outline-primary" aria-label="Ir a la fecha"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </form>
        </div>
    </div>

    <div class="row g-3 {{ $vista === 'semana' ? 'row-cols-1 row-cols-md-2 row-cols-xxl-3' : 'row-cols-1' }}">
        @foreach ($dias as $dia => $citas)
            @php $carbonDia = \Illuminate\Support\Carbon::parse($dia); @endphp
            <div class="col">
                <section class="card agenda-dia h-100 {{ $carbonDia->isToday() ? 'es-hoy' : '' }}" aria-labelledby="dia-{{ $dia }}">
                    <div class="card-body">
                        <h2 class="h6 mb-3" id="dia-{{ $dia }}">
                            {{ ucfirst($carbonDia->translatedFormat('l j \d\e F')) }}
                            @if ($carbonDia->isToday()) <span class="badge text-bg-primary ms-1">Hoy</span> @endif
                        </h2>

                        @forelse ($citas as $cita)
                            <div class="agenda-cita estado-{{ $cita->estado }} py-2 mb-2" data-test="agenda-cita-{{ $cita->id }}">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                    <span class="agenda-hora">{{ $cita->horaCorta() }}–{{ $cita->horaFin() }}</span>
                                    @include('admin.citas._estado')
                                </div>
                                <span class="d-block fw-semibold">{{ $cita->usuario?->nombreCompleto() ?? 'Usuario eliminado' }}</span>
                                <span class="small text-body-secondary d-block">{{ $cita->servicios->pluck('nombre')->join(', ') }}</span>
                                <span class="small">
                                    @if ($cita->usuario?->telefono)<i class="bi bi-telephone" aria-hidden="true"></i> {{ $cita->usuario->telefono }} · @endif
                                    <span class="precio">${{ number_format((float) $cita->total, 2) }}</span>
                                </span>
                            </div>
                        @empty
                            <p class="text-body-secondary small mb-0">Sin citas.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        @endforeach
    </div>
@endsection
