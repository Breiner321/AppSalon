{{-- <x-alert />: muestra los mensajes flash de la sesión como alertas Bootstrap descartables.
     Claves admitidas: success, error, warning e info. Se incluye una sola vez, en el layout. --}}
@php
    $tipos = [
        'success' => ['clase' => 'success', 'icono' => 'bi-check-circle'],
        'error' => ['clase' => 'danger', 'icono' => 'bi-x-octagon'],
        'warning' => ['clase' => 'warning', 'icono' => 'bi-exclamation-triangle'],
        'info' => ['clase' => 'info', 'icono' => 'bi-info-circle'],
    ];
@endphp

@foreach ($tipos as $clave => $tipo)
    @if (session()->has($clave))
        <div class="alert alert-{{ $tipo['clase'] }} alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
            <i class="bi {{ $tipo['icono'] }}" aria-hidden="true"></i>
            <div>{{ session($clave) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
@endforeach
