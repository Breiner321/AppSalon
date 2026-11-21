<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Horario de atención de un día de la semana, tabla horarios.
 *
 * @property int $id
 * @property int $diaSemana 0 = domingo … 6 = sábado (como Carbon::dayOfWeek).
 * @property string $apertura Hora de apertura, HH:MM:SS.
 * @property string $cierre Hora de cierre, HH:MM:SS.
 * @property bool $abierto
 */
class Horario extends Model
{
    protected $table = 'horarios';

    public $timestamps = false;

    /**
     * Nombres de los días en el orden de diaSemana.
     */
    public const DIAS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'diaSemana',
        'apertura',
        'cierre',
        'abierto',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'diaSemana' => 'integer',
            'abierto' => 'boolean',
        ];
    }

    /**
     * Nombre del día (Lunes, Martes…).
     */
    public function nombreDia(): string
    {
        return self::DIAS[$this->diaSemana] ?? '';
    }

    /**
     * Minutos desde la medianoche de la apertura (09:00 → 540).
     */
    public function aperturaEnMinutos(): int
    {
        return self::aMinutos($this->apertura);
    }

    /**
     * Minutos desde la medianoche del cierre.
     */
    public function cierreEnMinutos(): int
    {
        return self::aMinutos($this->cierre);
    }

    /**
     * Convierte 'HH:MM' o 'HH:MM:SS' en minutos desde la medianoche.
     */
    public static function aMinutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora));

        return $h * 60 + $m;
    }

    /**
     * Convierte minutos desde la medianoche en 'HH:MM'.
     */
    public static function aHora(int $minutos): string
    {
        return sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
    }
}
