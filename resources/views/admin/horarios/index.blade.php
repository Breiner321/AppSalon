{{-- Disponibilidad horaria: horario de cada día de la semana y fechas bloqueadas.
     Recibe $horarios (7 días, lunes primero) y $bloqueos desde Admin\HorarioController@index. --}}
@extends('layouts.app')

@section('title', 'Horarios del salón')

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Panel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Horarios</li>
        </ol>
    </nav>

    <h1 class="mb-1">Horarios del salón</h1>
    <p class="text-body-secondary mb-4">
        Los clientes solo pueden reservar dentro de este horario. Los cambios no mueven ni cancelan las citas ya reservadas.
    </p>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <section class="card card-form-plano" aria-labelledby="tituloSemana">
                <div class="card-body">
                    <h2 class="h5 mb-3" id="tituloSemana">Horario semanal</h2>

                    <form method="POST" action="{{ route('admin.horarios.update') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table align-middle mb-3">
                                <thead>
                                    <tr>
                                        <th scope="col">Día</th>
                                        <th scope="col">Abierto</th>
                                        <th scope="col">Apertura</th>
                                        <th scope="col">Cierre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($horarios as $h)
                                        @php $d = $h->diaSemana; @endphp
                                        <tr>
                                            <th scope="row">{{ $h->nombreDia() }}</th>
                                            <td>
                                                <input type="hidden" name="horarios[{{ $d }}][abierto]" value="0">
                                                <div class="form-check form-switch mb-0">
                                                    <input type="checkbox" class="form-check-input" role="switch" value="1"
                                                           name="horarios[{{ $d }}][abierto]" id="abierto-{{ $d }}"
                                                           @checked(old("horarios.$d.abierto", $h->abierto))>
                                                    <label class="visually-hidden" for="abierto-{{ $d }}">Abierto el {{ mb_strtolower($h->nombreDia()) }}</label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="visually-hidden" for="apertura-{{ $d }}">Apertura del {{ mb_strtolower($h->nombreDia()) }}</label>
                                                <input type="time" step="1800" name="horarios[{{ $d }}][apertura]" id="apertura-{{ $d }}"
                                                       class="form-control @error("horarios.$d.apertura") is-invalid @enderror"
                                                       value="{{ old("horarios.$d.apertura", substr($h->apertura, 0, 5)) }}">
                                                <x-form-error :field="'horarios.'.$d.'.apertura'" />
                                            </td>
                                            <td>
                                                <label class="visually-hidden" for="cierre-{{ $d }}">Cierre del {{ mb_strtolower($h->nombreDia()) }}</label>
                                                <input type="time" step="1800" name="horarios[{{ $d }}][cierre]" id="cierre-{{ $d }}"
                                                       class="form-control @error("horarios.$d.cierre") is-invalid @enderror"
                                                       value="{{ old("horarios.$d.cierre", substr($h->cierre, 0, 5)) }}">
                                                <x-form-error :field="'horarios.'.$d.'.cierre'" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="small text-body-secondary">
                            Las citas empiezan cada {{ config('salon.intervalo_minutos') }} minutos y deben terminar antes del cierre.
                        </p>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg" aria-hidden="true"></i> Guardar horario
                        </button>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-5">
            <section class="card card-form-plano mb-4" aria-labelledby="tituloBloquear">
                <div class="card-body">
                    <h2 class="h5 mb-3" id="tituloBloquear">Bloquear una fecha</h2>
                    <p class="small text-body-secondary">Festivos, vacaciones o capacitaciones: ese día no se podrá reservar.</p>

                    <form method="POST" action="{{ route('admin.bloqueos.store') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="fechaBloqueo" class="form-label requerido">Fecha</label>
                            <input type="date" name="fecha" id="fechaBloqueo" min="{{ today()->toDateString() }}" required
                                   class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha') }}">
                            <x-form-error field="fecha" />
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo <span class="text-body-secondary">(opcional)</span></label>
                            <input type="text" name="motivo" id="motivo" maxlength="100" placeholder="Ej.: Festivo"
                                   class="form-control @error('motivo') is-invalid @enderror" value="{{ old('motivo') }}">
                            <x-form-error field="motivo" />
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calendar-x" aria-hidden="true"></i> Bloquear fecha
                        </button>
                    </form>
                </div>
            </section>

            <section class="card card-form-plano" aria-labelledby="tituloBloqueos">
                <div class="card-body">
                    <h2 class="h5 mb-3" id="tituloBloqueos">Fechas bloqueadas</h2>

                    @forelse ($bloqueos as $bloqueo)
                        <div class="d-flex justify-content-between align-items-center gap-2 border-bottom py-2">
                            <span>
                                <span class="d-block fw-semibold">{{ ucfirst($bloqueo->fecha->translatedFormat('l j \d\e F \d\e Y')) }}</span>
                                <span class="small text-body-secondary">{{ $bloqueo->motivo ?? 'Sin motivo' }}</span>
                            </span>
                            <form method="POST" action="{{ route('admin.bloqueos.destroy', $bloqueo) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Quitar el bloqueo del {{ $bloqueo->fecha->format('d/m/Y') }}">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-body-secondary mb-0">No hay fechas bloqueadas.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
