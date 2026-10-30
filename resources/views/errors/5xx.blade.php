{{-- Respaldo para cualquier error 5xx sin vista propia (502, 503 en mantenimiento…).
     Reutiliza la 500, que es HTML autónomo y no depende del layout. --}}
@include('errors.500')
