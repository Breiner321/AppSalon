{{-- Selector para cambiar el estado de una cita. Recibe $cita y $transiciones (Admin\CitaController::TRANSICIONES).
     Si la cita está completada o cancelada (estados finales), solo muestra el badge. --}}
@php $opciones = $transiciones[$cita->estado] ?? []; @endphp

@if (empty($opciones))
    <span class="badge text-bg-{{ $cita->colorEstado() }} text-capitalize">{{ $cita->estado }}</span>
@else
    <form method="POST" action="{{ route('admin.citas.estado', $cita) }}" class="d-flex gap-1 align-items-center">
        @csrf
        @method('PATCH')
        <label for="estado-{{ $cita->id }}" class="visually-hidden">Estado de la cita #{{ $cita->id }}</label>
        <select name="estado" id="estado-{{ $cita->id }}" class="form-select form-select-sm w-auto">
            <option value="" selected disabled>{{ ucfirst($cita->estado) }}</option>
            @foreach ($opciones as $opcion)
                <option value="{{ $opcion }}">{{ ucfirst($opcion) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-outline-primary" aria-label="Cambiar estado de la cita #{{ $cita->id }}">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
        </button>
    </form>
@endif
