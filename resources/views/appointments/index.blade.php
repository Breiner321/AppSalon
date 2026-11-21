{{-- Mis citas: próximas citas con sus acciones e historial.
     Recibe $proximas y $historial desde AppointmentController@index. --}}
@extends('layouts.app')

@section('title', 'Mis citas')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="mb-1">Mis citas</h1>
            <p class="text-body-secondary mb-0">Consulta, modifica o cancela tus reservas.</p>
        </div>
        <a href="{{ route('citas.create') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus" aria-hidden="true"></i> Reservar cita
        </a>
    </div>

    <h2 class="h4 mb-3">Próximas</h2>

    @if ($proximas->isEmpty())
        <div class="estado-vacio text-center p-5 mb-4" role="status">
            <i class="bi bi-calendar-heart display-4 text-primary" aria-hidden="true"></i>
            <h3 class="h5 mt-3">No tienes citas próximas</h3>
            <p class="text-body-secondary">Elige tus servicios y la hora que más te convenga.</p>
            <a href="{{ route('citas.create') }}" class="btn btn-primary">
                <i class="bi bi-calendar-plus" aria-hidden="true"></i> Reservar mi primera cita
            </a>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
            @foreach ($proximas as $cita)
                <div class="col">
                    <article class="card tarjeta-cita h-100" data-test="cita-{{ $cita->id }}">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <h3 class="h5 mb-0">{{ ucfirst($cita->fecha->translatedFormat('l j \d\e F')) }}</h3>
                                    <p class="mb-0 text-body-secondary">
                                        <i class="bi bi-clock" aria-hidden="true"></i> {{ $cita->horaCorta() }} a {{ $cita->horaFin() }}
                                        · {{ $cita->duracion }} min
                                    </p>
                                </div>
                                <span class="badge text-bg-{{ $cita->colorEstado() }} text-capitalize">{{ $cita->estado }}</span>
                            </div>

                            <ul class="list-unstyled small mb-3">
                                @foreach ($cita->servicios as $servicio)
                                    <li class="d-flex justify-content-between">
                                        <span>{{ $servicio->nombre }}</span>
                                        <span>${{ number_format((float) $servicio->precio, 2) }}</span>
                                    </li>
                                @endforeach
                                <li class="d-flex justify-content-between border-top mt-1 pt-1 fw-semibold">
                                    <span>Total</span>
                                    <span class="precio">${{ number_format((float) $cita->total, 2) }}</span>
                                </li>
                            </ul>

                            @if ($cita->puedeModificarse())
                                <div class="d-flex flex-column flex-sm-row gap-2 mt-auto">
                                    <a href="{{ route('citas.edit', $cita) }}" class="btn btn-outline-primary flex-fill">
                                        <i class="bi bi-pencil" aria-hidden="true"></i> Modificar
                                    </a>
                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                            data-bs-toggle="modal" data-bs-target="#modalCancelar"
                                            data-accion="{{ route('citas.cancelar', $cita) }}"
                                            data-detalle="{{ $cita->fecha->translatedFormat('l j \d\e F') }} a las {{ $cita->horaCorta() }}">
                                        <i class="bi bi-x-circle" aria-hidden="true"></i> Cancelar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="h4 mb-3">Historial</h2>

    @if ($historial->isEmpty())
        <p class="text-body-secondary">Aquí aparecerán tus citas pasadas y canceladas.</p>
    @else
        <div class="card card-tabla">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Hora</th>
                            <th scope="col">Servicios</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($historial as $cita)
                            <tr>
                                <td class="text-nowrap">{{ $cita->fecha->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ $cita->horaCorta() }}</td>
                                <td>{{ $cita->servicios->pluck('nombre')->join(', ') }}</td>
                                <td class="text-end text-nowrap">${{ number_format((float) $cita->total, 2) }}</td>
                                <td><span class="badge text-bg-{{ $cita->colorEstado() }} text-capitalize">{{ $cita->estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Confirmación de cancelación: el script le pone la acción según el botón que lo abrió. --}}
    <div class="modal fade" id="modalCancelar" tabindex="-1" aria-labelledby="modalCancelarTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="" class="modal-content" id="formCancelar">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h2 class="modal-title h5" id="modalCancelarTitulo">Cancelar cita</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">¿Seguro que quieres cancelar tu cita del <strong id="modalCancelarDetalle"></strong>?
                        El horario quedará libre para otra persona.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle" aria-hidden="true"></i> Cancelar cita
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('modalCancelar').addEventListener('show.bs.modal', function (evento) {
            const boton = evento.relatedTarget;
            document.getElementById('formCancelar').action = boton.dataset.accion;
            document.getElementById('modalCancelarDetalle').textContent = boton.dataset.detalle;
        });
    </script>
@endpush
