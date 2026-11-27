{{-- Gestión de usuarios: búsqueda, filtros, activar o desactivar y cambiar el rol.
     Recibe $usuarios (paginado) y $filtros desde Admin\UsuarioController@index. --}}
@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Panel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
        </ol>
    </nav>

    <h1 class="mb-4">Usuarios</h1>

    <form method="GET" action="{{ route('admin.usuarios.index') }}" class="filtros p-3 mb-4" role="search">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label for="q" class="form-label small mb-1">Buscar</label>
                <input type="search" name="q" id="q" maxlength="60" class="form-control @error('q') is-invalid @enderror"
                       placeholder="Nombre, correo o teléfono" value="{{ $filtros['q'] ?? '' }}">
                <x-form-error field="q" />
            </div>
            <div class="col-6 col-md-2">
                <label for="rol" class="form-label small mb-1">Rol</label>
                <select name="rol" id="rol" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($roles as $valor => $nombre)
                        <option value="{{ $valor }}" @selected(($filtros['rol'] ?? '') === $valor)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="estado" class="form-label small mb-1">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
                    <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
                    <option value="sin_confirmar" @selected(($filtros['estado'] ?? '') === 'sin_confirmar')>Sin confirmar</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-primary">Limpiar</a>
            </div>
        </div>
    </form>

    <p class="text-body-secondary small" data-test="total-usuarios">{{ $usuarios->total() }} usuario(s) encontrado(s)</p>

    @if ($usuarios->isEmpty())
        <div class="estado-vacio text-center p-5" role="status">
            <i class="bi bi-person-x display-4 text-primary" aria-hidden="true"></i>
            <h2 class="h5 mt-3">No hay usuarios con esos filtros</h2>
        </div>
    @else
        <div class="card card-tabla">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Citas</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            @php $esYo = $usuario->is(auth()->user()); @endphp
                            <tr data-test="usuario-{{ $usuario->id }}">
                                <td class="text-nowrap">{{ $usuario->nombreCompleto() }} @if ($esYo)<span class="small text-body-secondary">(tú)</span>@endif</td>
                                <td>{{ $usuario->email }}</td>
                                <td class="text-nowrap">{{ $usuario->telefono ?? '—' }}</td>
                                <td>
                                    @if ($usuario->isAdmin())
                                        <span class="badge text-bg-primary">Administrador</span>
                                    @elseif ($usuario->isEmpleado())
                                        <span class="badge text-bg-info">Empleado</span>
                                    @else
                                        <span class="badge text-bg-light border">Cliente</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if (! $usuario->isActivo())
                                        <span class="badge text-bg-secondary">Inactivo</span>
                                    @elseif (! $usuario->confirmado)
                                        <span class="badge text-bg-warning">Sin confirmar</span>
                                    @else
                                        <span class="badge text-bg-success">Activo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.citas.index', ['q' => $usuario->email]) }}" aria-label="Ver citas de {{ $usuario->nombreCompleto() }}">{{ $usuario->citas_count }}</a>
                                </td>
                                <td class="text-end text-nowrap">
                                    @unless ($esYo)
                                        <form method="POST" action="{{ route('admin.usuarios.activo', $usuario) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            @if ($usuario->isActivo())
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Desactivar" aria-label="Desactivar a {{ $usuario->nombreCompleto() }}">
                                                    <i class="bi bi-person-slash" aria-hidden="true"></i> <span class="d-none d-xl-inline">Desactivar</span>
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Activar" aria-label="Activar a {{ $usuario->nombreCompleto() }}">
                                                    <i class="bi bi-person-check" aria-hidden="true"></i> <span class="d-none d-xl-inline">Activar</span>
                                                </button>
                                            @endif
                                        </form>
                                        <form method="POST" action="{{ route('admin.usuarios.rol', $usuario) }}" class="d-inline-flex gap-1 align-items-center">
                                            @csrf
                                            @method('PATCH')
                                            <label for="rol-{{ $usuario->id }}" class="visually-hidden">Rol de {{ $usuario->nombreCompleto() }}</label>
                                            <select name="rol" id="rol-{{ $usuario->id }}" class="form-select form-select-sm w-auto">
                                                @foreach ($roles as $valor => $nombre)
                                                    <option value="{{ $valor }}" @selected($usuario->rol() === $valor)>{{ $nombre }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-outline-primary btn-sm" title="Asignar rol"
                                                    aria-label="Asignar rol a {{ $usuario->nombreCompleto() }}">
                                                <i class="bi bi-shield-check" aria-hidden="true"></i>
                                                <span class="d-none d-xl-inline">Asignar</span>
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $usuarios->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
