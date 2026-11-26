<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Bloqueo;
use App\Models\Horario;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Reglas de disponibilidad de la agenda del salón.
 *
 * El salón atiende una cita a la vez: dos citas que no estén canceladas no
 * pueden sobreponerse. Una cita ocupa el intervalo [hora, hora + duración).
 * Así, una cita de 10:00 a 10:45 y otra de 10:45 a 11:30 no se sobreponen.
 *
 * La usan la reserva web (AppointmentController), la API (Api\CitaController),
 * el Form Request AppointmentRequest y el seeder de citas.
 */
class Disponibilidad
{
    /**
     * Horario del día si el salón atiende esa fecha, o null si está cerrado
     * (día de la semana sin atención o fecha bloqueada).
     */
    public function horarioDe(Carbon $fecha): ?Horario
    {
        if ($this->bloqueoDe($fecha)) {
            return null;
        }

        $horario = Horario::where('diaSemana', $fecha->dayOfWeek)->first();

        return $horario && $horario->abierto ? $horario : null;
    }

    /**
     * Bloqueo de la fecha, si existe.
     */
    public function bloqueoDe(Carbon $fecha): ?Bloqueo
    {
        return Bloqueo::whereDate('fecha', $fecha->toDateString())->first();
    }

    /**
     * Intervalos ocupados de una fecha, en minutos desde la medianoche.
     *
     * @return Collection<int, array{inicio: int, fin: int, id: int}>
     */
    public function ocupados(Carbon $fecha, ?int $ignorarCitaId = null): Collection
    {
        return Appointment::ocupan()
            ->whereDate('fecha', $fecha->toDateString())
            ->when($ignorarCitaId, fn ($q) => $q->whereKeyNot($ignorarCitaId))
            ->get(['id', 'hora', 'duracion'])
            ->map(function (Appointment $cita) {
                $inicio = Horario::aMinutos($cita->hora);

                return ['inicio' => $inicio, 'fin' => $inicio + $cita->duracion, 'id' => $cita->id];
            });
    }

    /**
     * Horas de inicio libres ('HH:MM') para una cita de $duracion minutos.
     *
     * Un turno es válido si la cita termina antes del cierre, no se sobrepone
     * con otra y (si $soloFuturo) empieza después del tiempo mínimo de
     * anticipación configurado en config/salon.php.
     *
     * @param  int|null  $ignorarCitaId  Cita que se está modificando: su propio horario cuenta como libre.
     * @return list<string>
     */
    public function turnosLibres(Carbon $fecha, int $duracion, ?int $ignorarCitaId = null, bool $soloFuturo = true): array
    {
        $horario = $this->horarioDe($fecha);

        if (! $horario || $duracion <= 0) {
            return [];
        }

        $intervalo = max(5, (int) config('salon.intervalo_minutos'));
        $ocupados = $this->ocupados($fecha, $ignorarCitaId);
        $limite = $this->primerMomentoReservable();
        $turnos = [];

        for ($inicio = $horario->aperturaEnMinutos(); $inicio + $duracion <= $horario->cierreEnMinutos(); $inicio += $intervalo) {
            if ($soloFuturo && $fecha->copy()->startOfDay()->addMinutes($inicio)->lt($limite)) {
                continue;
            }

            if (! $this->seSobrepone($ocupados, $inicio, $inicio + $duracion)) {
                $turnos[] = Horario::aHora($inicio);
            }
        }

        return $turnos;
    }

    /**
     * Revisa si una cita puede quedar en esa fecha y hora. Devuelve null si es
     * posible o el mensaje para el usuario si no lo es.
     */
    public function motivoNoDisponible(Carbon $fecha, string $hora, int $duracion, ?int $ignorarCitaId = null): ?string
    {
        if ($bloqueo = $this->bloqueoDe($fecha)) {
            return 'El salón no atiende ese día'.($bloqueo->motivo ? " ({$bloqueo->motivo})." : '.');
        }

        $horario = $this->horarioDe($fecha);

        if (! $horario) {
            // sábado → sábados, domingo → domingos; lunes a viernes no cambian.
            $dia = mb_strtolower(Horario::DIAS[$fecha->dayOfWeek]);

            return 'El salón no atiende los '.(str_ends_with($dia, 's') ? $dia : $dia.'s').'.';
        }

        $inicio = Horario::aMinutos($hora);
        $fin = $inicio + $duracion;

        if ($inicio < $horario->aperturaEnMinutos() || $fin > $horario->cierreEnMinutos()) {
            return sprintf(
                'La cita debe quedar dentro del horario del salón: de %s a %s. Tus servicios duran %d minutos.',
                Horario::aHora($horario->aperturaEnMinutos()),
                Horario::aHora($horario->cierreEnMinutos()),
                $duracion,
            );
        }

        if ($fecha->copy()->startOfDay()->addMinutes($inicio)->lt($this->primerMomentoReservable())) {
            return sprintf(
                'La cita debe reservarse con al menos %d minutos de anticipación.',
                config('salon.anticipacion_minutos'),
            );
        }

        if ($this->seSobrepone($this->ocupados($fecha, $ignorarCitaId), $inicio, $fin)) {
            return 'Ese horario ya está ocupado. Elige otra hora.';
        }

        return null;
    }

    /**
     * Momento a partir del cual se aceptan reservas (ahora + anticipación mínima).
     */
    public function primerMomentoReservable(): Carbon
    {
        return now()->addMinutes((int) config('salon.anticipacion_minutos'));
    }

    /**
     * Último día que se puede reservar.
     */
    public function ultimoDiaReservable(): Carbon
    {
        return today()->addDays((int) config('salon.dias_maximos'));
    }

    /**
     * Datos que necesita el calendario interactivo: días de la semana con
     * atención, fechas bloqueadas y el rango de fechas permitido.
     *
     * @return array{diasAbiertos: list<int>, bloqueos: list<string>, desde: string, hasta: string}
     */
    public function datosCalendario(): array
    {
        return [
            'diasAbiertos' => Horario::where('abierto', true)->orderBy('diaSemana')->pluck('diaSemana')->all(),
            'bloqueos' => Bloqueo::whereBetween('fecha', [today()->toDateString(), $this->ultimoDiaReservable()->toDateString()])
                ->orderBy('fecha')
                ->get()
                ->map(fn (Bloqueo $b) => $b->fecha->toDateString())
                ->all(),
            'desde' => today()->toDateString(),
            'hasta' => $this->ultimoDiaReservable()->toDateString(),
        ];
    }

    /**
     * Resumen de un día para el calendario web y para GET /api/disponibilidad:
     * si el salón abre, su horario y las horas libres para los servicios dados.
     *
     * @param  list<int|string>  $servicios  Ids de los servicios elegidos.
     * @return array{fecha: string, abierto: bool, motivo: string|null, apertura: string|null, cierre: string|null, duracion: int, turnos: list<string>}
     */
    public function resumenDia(Carbon $fecha, array $servicios, ?int $ignorarCitaId = null): array
    {
        $duracion = (int) Service::whereIn('id', $servicios)->sum('duracion');
        $horario = $this->horarioDe($fecha);

        return [
            'fecha' => $fecha->toDateString(),
            'abierto' => (bool) $horario,
            'motivo' => $this->bloqueoDe($fecha)?->motivo,
            'apertura' => $horario ? substr($horario->apertura, 0, 5) : null,
            'cierre' => $horario ? substr($horario->cierre, 0, 5) : null,
            'duracion' => $duracion,
            'turnos' => $duracion > 0 ? $this->turnosLibres($fecha, $duracion, $ignorarCitaId) : [],
        ];
    }

    /**
     * Dos intervalos [a, b) y [c, d) se sobreponen si a < d y c < b.
     *
     * @param  Collection<int, array{inicio: int, fin: int, id: int}>  $ocupados
     */
    private function seSobrepone(Collection $ocupados, int $inicio, int $fin): bool
    {
        return $ocupados->contains(fn (array $o) => $inicio < $o['fin'] && $o['inicio'] < $fin);
    }
}
