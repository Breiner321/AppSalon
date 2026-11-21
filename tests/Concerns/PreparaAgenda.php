<?php

namespace Tests\Concerns;

use App\Models\Appointment;
use App\Models\Horario;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Ayudas para las pruebas de citas: fija "hoy" en el lunes 7 de diciembre de
 * 2026 a las 08:00 y crea el horario del salón (lunes a sábado de 9:00 a 18:00,
 * domingo cerrado).
 */
trait PreparaAgenda
{
    protected function prepararAgenda(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-07 08:00:00'));

        foreach (range(0, 6) as $dia) {
            Horario::create([
                'diaSemana' => $dia,
                'apertura' => '09:00:00',
                'cierre' => '18:00:00',
                'abierto' => $dia !== 0,
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Servicio activo con la duración y el precio dados.
     */
    protected function servicio(int $duracion = 60, float $precio = 50): Service
    {
        return Service::factory()->create(['duracion' => $duracion, 'precio' => $precio]);
    }

    /**
     * Cita guardada directamente en la base (sin pasar por la validación).
     */
    protected function citaExistente(string $fecha, string $hora, int $duracion = 60, ?User $usuario = null, string $estado = 'pendiente'): Appointment
    {
        $servicio = $this->servicio($duracion);

        $cita = Appointment::create([
            'fecha' => $fecha,
            'hora' => $hora,
            'duracion' => $duracion,
            'usuarioId' => ($usuario ?? User::factory()->create())->id,
            'total' => $servicio->precio,
            'estado' => $estado,
        ]);
        $cita->servicios()->attach($servicio->id);

        return $cita;
    }
}
