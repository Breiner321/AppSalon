{{-- Listado de servicios del panel (U6): tabla paginada con acciones de editar y eliminar.
     Recibe $servicios (paginado de 10) desde ServiceController@index. --}}
@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Servicios</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="mb-0">Servicios</h1>
        <a href="{{ route('admin.servicios.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo servicio
        </a>
    </div>

    @if ($servicios->isEmpty())
        <div class="alert alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i> Aún no hay servicios registrados.
        </div>
    @else
        <div class="card card-tabla">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col" class="text-end">Precio</th>
                            <th scope="col" class="text-nowrap">Duración</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($servicios as $servicio)
                            <tr>
                                <td>{{ $servicio->nombre }}</td>
                                <td class="text-end text-nowrap precio">${{ number_format((float) $servicio->precio, 2) }}</td>
                                <td class="text-nowrap">{{ $servicio->duracion }} min</td>
                                <td>
                                    @if ($servicio->activo)
                                        <span class="badge text-bg-success">Activo</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.servicios.edit', $servicio) }}"
                                       class="btn btn-sm btn-outline-primary" title="Editar"
                                       aria-label="Editar {{ $servicio->nombre }}">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                            aria-label="Eliminar {{ $servicio->nombre }}"
                                            data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                            data-accion="{{ route('admin.servicios.destroy', $servicio) }}"
                                            data-nombre="{{ $servicio->nombre }}">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $servicios->links('pagination::bootstrap-5') }}
        </div>
    @endif

    {{-- Modal de confirmación compartido: el script de abajo le pone la acción y el nombre
         del servicio según el botón que lo abrió. --}}
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="" class="modal-content" id="formEliminar">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h2 class="modal-title h5" id="modalEliminarTitulo">Eliminar servicio</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">¿Seguro que quieres eliminar <strong id="modalEliminarNombre"></strong>?</p>
                    <p class="mb-0 small text-body-secondary">
                        También se quitará de las citas que lo incluyan. Si solo quieres ocultarlo del catálogo,
                        edítalo y desactívalo.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Copia la URL y el nombre del botón que abrió el modal. textContent evita inyectar HTML.
        document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (evento) {
            const boton = evento.relatedTarget;
            document.getElementById('formEliminar').action = boton.dataset.accion;
            document.getElementById('modalEliminarNombre').textContent = boton.dataset.nombre;
        });
    </script>
@endpush
