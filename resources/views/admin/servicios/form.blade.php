{{-- Formulario de servicios del panel (U5). Sirve para crear y para editar:
     recibe $servicio, que es nuevo en create y existente en edit. --}}
@extends('layouts.app')

@php
    $editando = $servicio->exists;
@endphp

@section('title', $editando ? 'Editar servicio' : 'Nuevo servicio')

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Panel</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.servicios.index') }}">Servicios</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $editando ? 'Editar' : 'Nuevo' }}</li>
        </ol>
    </nav>

    <div class="card card-form card-form-amplia">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">{{ $editando ? 'Editar servicio' : 'Nuevo servicio' }}</h1>

            <form method="POST" novalidate
                  action="{{ $editando ? route('admin.servicios.update', $servicio) : route('admin.servicios.store') }}">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif

                <p class="form-text mt-0 mb-3">Los campos marcados con <span class="text-danger">*</span> son obligatorios.</p>

                <div class="mb-3">
                    <label for="nombre" class="form-label requerido">Nombre</label>
                    <input type="text" name="nombre" id="nombre" maxlength="60" required
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $servicio->nombre) }}">
                    <x-form-error field="nombre" />
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="precio" class="form-label requerido">Precio</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">$</span>
                            <input type="number" name="precio" id="precio" step="0.01" min="0" max="999.99" required
                                   inputmode="decimal"
                                   class="form-control @error('precio') is-invalid @enderror"
                                   value="{{ old('precio', $servicio->precio) }}">
                            <x-form-error field="precio" />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="duracion" class="form-label requerido">Duración (min)</label>
                        <input type="number" name="duracion" id="duracion" min="10" max="480" required
                               inputmode="numeric" aria-describedby="duracion-ayuda"
                               class="form-control @error('duracion') is-invalid @enderror"
                               value="{{ old('duracion', $servicio->duracion) }}">
                        <div id="duracion-ayuda" class="form-text">Entre 10 y 480 minutos.</div>
                        <x-form-error field="duracion" />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción <span class="text-body-secondary">(opcional)</span></label>
                    <textarea name="descripcion" id="descripcion" rows="3" maxlength="500"
                              class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $servicio->descripcion) }}</textarea>
                    <x-form-error field="descripcion" />
                </div>

                <div class="mb-4">
                    {{-- El hidden envía 0 cuando el switch está apagado (un checkbox sin marcar no se envía). --}}
                    <input type="hidden" name="activo" value="0">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="activo" id="activo" value="1" role="switch"
                               class="form-check-input @error('activo') is-invalid @enderror"
                               @checked(old('activo', $servicio->activo))>
                        <label for="activo" class="form-check-label">Activo (visible en el catálogo)</label>
                        <x-form-error field="activo" />
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg" aria-hidden="true"></i> Guardar
                    </button>
                    <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline-primary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
