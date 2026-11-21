<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Quién puede ver y cambiar una cita: su dueño o un administrador.
 *
 * Laravel la encuentra sola por el nombre (Appointment → AppointmentPolicy).
 * Se usa con Gate::authorize('update', $cita) en la web y en la API; si la
 * respuesta es no, el usuario recibe 403.
 */
class AppointmentPolicy
{
    /**
     * Ver el detalle de la cita.
     */
    public function view(User $usuario, Appointment $cita): bool
    {
        return $usuario->isAdmin() || $cita->usuarioId === $usuario->id;
    }

    /**
     * Modificar fecha, hora o servicios.
     */
    public function update(User $usuario, Appointment $cita): bool
    {
        return $this->view($usuario, $cita);
    }

    /**
     * Cancelar la cita.
     */
    public function cancel(User $usuario, Appointment $cita): bool
    {
        return $this->view($usuario, $cita);
    }
}
