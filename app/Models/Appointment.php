<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Cita de un cliente, tabla citas.
 *
 * @property int $id
 * @property Carbon $fecha
 * @property string $hora Hora de inicio, HH:MM:SS.
 * @property int $duracion Duración total en minutos (suma de los servicios).
 * @property int|null $usuarioId
 * @property string|null $total
 * @property string $estado pendiente, confirmada, completada o cancelada.
 */
class Appointment extends Model
{
    protected $table = 'citas';

    public const PENDIENTE = 'pendiente';

    public const CONFIRMADA = 'confirmada';

    public const COMPLETADA = 'completada';

    public const CANCELADA = 'cancelada';

    /**
     * Todos los estados, en el orden en que se muestran.
     */
    public const ESTADOS = [self::PENDIENTE, self::CONFIRMADA, self::COMPLETADA, self::CANCELADA];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'fecha',
        'hora',
        'duracion',
        'usuarioId',
        'total',
        'estado',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'duracion' => 'integer',
            'usuarioId' => 'integer',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Cliente que reservó la cita (null si el usuario fue eliminado).
     *
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuarioId');
    }

    /**
     * Servicios incluidos en la cita, a través de la tabla citasServicios.
     *
     * @return BelongsToMany<Service, $this>
     */
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'citasServicios', 'citaId', 'servicioId')
            ->using(AppointmentService::class)
            ->withPivot('precio');
    }

    /**
     * Deja en la cita exactamente esos servicios, guardando en citasServicios
     * el precio que tiene cada uno en este momento.
     *
     * @param  iterable<Service>  $servicios
     */
    public function asignarServicios(iterable $servicios): void
    {
        $filas = [];
        foreach ($servicios as $servicio) {
            $filas[$servicio->id] = ['precio' => $servicio->precio];
        }

        $this->servicios()->sync($filas);
    }

    /**
     * Citas que ocupan la agenda (todas menos las canceladas).
     *
     * @param  Builder<Appointment>  $query
     */
    public function scopeOcupan(Builder $query): void
    {
        $query->whereIn('estado', config('salon.estados_que_ocupan'));
    }

    /**
     * Fecha y hora de inicio de la cita.
     */
    public function inicio(): Carbon
    {
        return Carbon::parse($this->fecha->toDateString().' '.$this->hora);
    }

    /**
     * Fecha y hora en que termina la cita.
     */
    public function fin(): Carbon
    {
        return $this->inicio()->addMinutes($this->duracion);
    }

    /**
     * Hora de inicio sin segundos (10:00).
     */
    public function horaCorta(): string
    {
        return substr($this->hora, 0, 5);
    }

    /**
     * Hora de fin sin segundos (10:45).
     */
    public function horaFin(): string
    {
        return $this->fin()->format('H:i');
    }

    /**
     * El cliente puede modificarla o cancelarla: está pendiente o confirmada
     * y todavía no empieza.
     */
    public function puedeModificarse(): bool
    {
        return in_array($this->estado, [self::PENDIENTE, self::CONFIRMADA], true)
            && $this->inicio()->isFuture();
    }

    /**
     * Clase de color Bootstrap del estado (para los badges).
     */
    public function colorEstado(): string
    {
        return match ($this->estado) {
            self::PENDIENTE => 'warning',
            self::CONFIRMADA => 'primary',
            self::COMPLETADA => 'success',
            default => 'secondary',
        };
    }
}
