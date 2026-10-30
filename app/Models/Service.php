<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Servicio del salón (corte, tinte, manicure…), tabla servicios.
 *
 * @property int $id
 * @property string $nombre
 * @property string $precio
 * @property string|null $descripcion
 * @property int $duracion Duración en minutos.
 * @property bool $activo
 */
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $table = 'servicios';

    /**
     * La tabla solo tiene created_at.
     */
    const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'duracion',
        'activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'duracion' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /**
     * Citas que incluyen este servicio.
     *
     * @return BelongsToMany<Appointment, $this>
     */
    public function citas(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'citasServicios', 'servicioId', 'citaId')
            ->using(AppointmentService::class);
    }
}
