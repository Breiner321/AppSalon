<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Fecha en que el salón no atiende (festivo, vacaciones…), tabla bloqueos.
 *
 * @property int $id
 * @property Carbon $fecha
 * @property string|null $motivo
 */
class Bloqueo extends Model
{
    protected $table = 'bloqueos';

    /**
     * La tabla solo tiene created_at.
     */
    const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'fecha',
        'motivo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
