<?php

namespace App\Services;

use App\Mail\CitaNotificacion;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Crea, modifica, cancela y cambia de estado las citas.
 *
 * La web y la API usan esta misma clase, así que las reglas son las mismas en
 * los dos lados. Los datos llegan ya validados por AppointmentRequest; aquí se
 * vuelve a revisar la disponibilidad dentro de la transacción, con las citas del
 * día bloqueadas (SELECT … FOR UPDATE), para que dos personas que reservan al
 * mismo tiempo no se queden con el mismo horario.
 */
class ReservaCitas
{
    public function __construct(private Disponibilidad $disponibilidad)
    {
        //
    }

    /**
     * Reserva una cita nueva para el usuario y le envía el correo de confirmación.
     *
     * @param  array{fecha: string, hora: string, servicios: list<int>}  $datos
     *
     * @throws ValidationException si el horario se ocupó mientras tanto.
     */
    public function crear(User $usuario, array $datos): Appointment
    {
        $cita = DB::transaction(function () use ($usuario, $datos) {
            $servicios = $this->serviciosActivos($datos['servicios']);
            $this->asegurarDisponible($datos['fecha'], $datos['hora'], (int) $servicios->sum('duracion'));

            $cita = Appointment::create([
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
                'duracion' => $servicios->sum('duracion'),
                'usuarioId' => $usuario->id,
                'total' => $servicios->sum('precio'),
                'estado' => Appointment::PENDIENTE,
            ]);
            $cita->asignarServicios($servicios);

            return $cita;
        });

        $this->notificar($cita, 'reservada');

        return $cita;
    }

    /**
     * Cambia fecha, hora o servicios de una cita. Vuelve a quedar pendiente
     * para que el salón la confirme otra vez.
     *
     * @param  array{fecha: string, hora: string, servicios: list<int>}  $datos
     *
     * @throws ValidationException si el horario nuevo está ocupado.
     */
    public function actualizar(Appointment $cita, array $datos): Appointment
    {
        DB::transaction(function () use ($cita, $datos) {
            $servicios = $this->serviciosActivos($datos['servicios'], $cita);
            $this->asegurarDisponible($datos['fecha'], $datos['hora'], (int) $servicios->sum('duracion'), $cita->id);

            $cita->update([
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
                'duracion' => $servicios->sum('duracion'),
                'total' => $servicios->sum('precio'),
                'estado' => Appointment::PENDIENTE,
            ]);
            $cita->asignarServicios($servicios);
        });

        $this->notificar($cita->refresh(), 'modificada');

        return $cita;
    }

    /**
     * Cancela la cita y libera su horario.
     */
    public function cancelar(Appointment $cita): Appointment
    {
        $cita->update(['estado' => Appointment::CANCELADA]);

        $this->notificar($cita, 'cancelada');

        return $cita;
    }

    /**
     * Cambio de estado hecho por el administrador. Avisa al cliente por correo
     * cuando la cita se confirma o se cancela.
     */
    public function cambiarEstado(Appointment $cita, string $estado): Appointment
    {
        $cita->update(['estado' => $estado]);

        if ($estado === Appointment::CONFIRMADA) {
            $this->notificar($cita, 'confirmada');
        } elseif ($estado === Appointment::CANCELADA) {
            $this->notificar($cita, 'cancelada');
        }

        return $cita;
    }

    /**
     * Servicios activos con esos ids. Al modificar una cita también se aceptan
     * los servicios que ya tenía aunque luego se hayan desactivado.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Service>
     */
    private function serviciosActivos(array $ids, ?Appointment $cita = null)
    {
        $actuales = $cita ? $cita->servicios()->pluck('servicios.id')->all() : [];

        return Service::whereIn('id', $ids)
            ->where(fn ($q) => $q->where('activo', true)->orWhereIn('id', $actuales))
            ->get();
    }

    /**
     * Bloquea las citas del día y vuelve a revisar el horario.
     *
     * @throws ValidationException
     */
    private function asegurarDisponible(string $fecha, string $hora, int $duracion, ?int $ignorarCitaId = null): void
    {
        Appointment::whereDate('fecha', $fecha)->lockForUpdate()->get(['id']);

        $motivo = $this->disponibilidad->motivoNoDisponible(Carbon::parse($fecha), $hora, $duracion, $ignorarCitaId);

        if ($motivo) {
            throw ValidationException::withMessages(['hora' => $motivo]);
        }
    }

    /**
     * Envía el correo al cliente. Si el envío falla, la cita igual queda
     * guardada: el error se escribe en el log y no se le muestra al usuario.
     */
    private function notificar(Appointment $cita, string $tipo): void
    {
        $usuario = $cita->usuario;

        if (! $usuario) {
            return;
        }

        try {
            Mail::to($usuario)->send(new CitaNotificacion($cita->loadMissing('servicios'), $tipo));
        } catch (Throwable $e) {
            Log::error('No se pudo enviar el correo de la cita.', [
                'citaId' => $cita->id,
                'tipo' => $tipo,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
