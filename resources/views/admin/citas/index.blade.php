{{-- Listado de citas con búsqueda y filtros. Recibe $citas (paginado) y $filtros
     desde Admin\CitaController@index. --}}
@extends('layouts.app')

@section('title', 'Citas')

@php $transiciones = \App\Http\Controllers\Admin\CitaController::TRANSICIONES; @endphp

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Panel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Citas</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="mb-0">Citas</h1>
        <a href="{{ route('admin.agenda') }}" class="btn btn-outline-primary">
            <i class="bi bi-calendar-week" aria-hidden="true"></i> Ver agenda
        </a>
    </div>

    <form method="GET" action="{{ route('admin.citas.index') }}" class="filtros p-3 mb-4" role="search">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label for="q" class="form-label small mb-1">Cliente</label>
                <input type="search" name="q" id="q" maxlength="60" class="form-control"
                       placeholder="Nombre o correo" value="{{ $filtros['q'] ?? '' }}">
            </div>
            <div class="col-12 col-sm-4 col-md-2">
                <label for="estado" class="form-label small mb-1">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Appointment::ESTADOS as $estado)
                        <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ ucfirst($estado) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <label for="desde" class="form-label small mb-1">Desde</label>
                <input type="date" name="desde" id="desde" class="form-control" value="{{ $filtros['desde'] ?? '' }}">
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <label for="hasta" class="form-label small mb-1">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="form-control @error('hasta') is-invalid @enderror" value="{{ $filtros['hasta'] ?? '' }}">
                <x-form-error field="hasta" />
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill" aria-label="Filtrar"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
                <a href="{{ route('admin.citas.index') }}" class="btn btn-outline-primary" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
            </div>
        </div>
    </form>

    <p class="text-body-secondary small" data-test="total-citas">{{ $citas->total() }} cita(s) encontrada(s)</p>

    @if ($citas->isEmpty())
        <div class="estado-vacio text-center p-5" role="status">
            <i class="bi bi-calendar-x display-4 text-primary" aria-hidden="true"></i>
            <h2 class="h5 mt-3">No hay citas con esos filtros</h2>
        </div>
    @else
        <div class="card card-tabla">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
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
                            <tr data-test="cita-{{ $cita->id }}">
                                <td class="text-body-secondary">{{ $cita->id }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.agenda', ['fecha' => $cita->fecha->toDateString()]) }}">{{ $cita->fecha->format('d/m/Y') }}</a>
                                </td>
                                <td class="text-nowrap">{{ $cita->horaCorta() }}–{{ $cita->horaFin() }}</td>
                                <td>
                                    <span class="d-block">{{ $cita->usuario?->nombreCompleto() ?? 'Usuario eliminado' }}</span>
                                    <span class="small text-body-secondary">{{ $cita->usuario?->email }}</span>
                                </td>
                                <td class="small">{{ $cita->servicios->pluck('nombre')->join(', ') }}</td>
                                <td class="text-end text-nowrap precio">${{ number_format((float) $cita->total, 2) }}</td>
                                <td>@include('admin.citas._estado')</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $citas->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
