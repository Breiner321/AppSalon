<?php

namespace Database\Seeders;

use App\Models\Bloqueo;
use App\Models\Horario;
use Illuminate\Database\Seeder;

/**
 * Horario inicial del salón y un día bloqueado de ejemplo.
 *
 * Lunes a viernes de 9:00 a 19:00, sábado de 9:00 a 15:00 y domingo cerrado.
 * El administrador lo cambia desde /admin/horarios.
 */
class HorarioSeeder extends Seeder
{
    public function run(): void
    {
        $semana = [
            0 => ['apertura' => '09:00:00', 'cierre' => '13:00:00', 'abierto' => false], // domingo
            1 => ['apertura' => '09:00:00', 'cierre' => '19:00:00', 'abierto' => true],
            2 => ['apertura' => '09:00:00', 'cierre' => '19:00:00', 'abierto' => true],
            3 => ['apertura' => '09:00:00', 'cierre' => '19:00:00', 'abierto' => true],
            4 => ['apertura' => '09:00:00', 'cierre' => '19:00:00', 'abierto' => true],
            5 => ['apertura' => '09:00:00', 'cierre' => '19:00:00', 'abierto' => true],
            6 => ['apertura' => '09:00:00', 'cierre' => '15:00:00', 'abierto' => true], // sábado
        ];

        foreach ($semana as $dia => $datos) {
            Horario::updateOrCreate(['diaSemana' => $dia], $datos);
        }

        // Un día hábil dentro de tres semanas, para ver el bloqueo en el calendario.
        $fecha = today()->addWeeks(3);
        while (! in_array($fecha->dayOfWeek, [1, 2, 3, 4, 5], true)) {
            $fecha->addDay();
        }

        Bloqueo::updateOrCreate(['fecha' => $fecha->toDateString()], ['motivo' => 'Capacitación del personal']);
    }
}
