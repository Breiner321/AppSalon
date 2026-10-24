<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Cita de un cliente, tabla citas.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $hora
 * @property int|null $usuarioId
 * @property string|null $total
 * @property string $estado pendiente, confirmada, completada o cancelada.
 */
class Appointment extends Model
{
    protected $table = 'citas';

    /**
     * La tabla solo tiene created_at.
     */
    const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'fecha',
        'hora',
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
            ->using(AppointmentService::class);
    }
}
