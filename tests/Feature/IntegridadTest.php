<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de integridad referencial de citas y citasServicios (T5),
 * contra la base appsalon_mvc_php_test.
 */
class IntegridadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea una cita del usuario con los servicios indicados.
     *
     * @param  iterable<Service>  $servicios
     */
    private function crearCita(User $usuario, iterable $servicios): Appointment
    {
        $cita = Appointment::create([
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '10:00:00',
            'usuarioId' => $usuario->id,
            'total' => collect($servicios)->sum('precio'),
            'estado' => 'pendiente',
        ]);

        $cita->servicios()->attach(collect($servicios)->pluck('id'));

        return $cita;
    }

    public function test_al_borrar_un_usuario_sus_citas_quedan_con_usuario_null(): void
    {
        $usuario = User::factory()->create();
        $cita = $this->crearCita($usuario, Service::factory()->count(2)->create());

        $usuario->delete();

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'usuarioId' => null]);
    }

    public function test_al_borrar_una_cita_se_borran_sus_filas_en_citas_servicios(): void
    {
        $cita = $this->crearCita(User::factory()->create(), Service::factory()->count(2)->create());
        $this->assertDatabaseCount('citasServicios', 2);

        $cita->delete();

        $this->assertDatabaseMissing('citasServicios', ['citaId' => $cita->id]);
        $this->assertDatabaseCount('servicios', 2);
    }

    public function test_al_borrar_un_servicio_se_borran_sus_filas_en_citas_servicios(): void
    {
        [$borrado, $otro] = Service::factory()->count(2)->create();
        $cita = $this->crearCita(User::factory()->create(), [$borrado, $otro]);

        $borrado->delete();

        $this->assertDatabaseMissing('citasServicios', ['servicioId' => $borrado->id]);
        $this->assertDatabaseHas('citasServicios', ['citaId' => $cita->id, 'servicioId' => $otro->id]);
        $this->assertDatabaseHas('citas', ['id' => $cita->id]);
    }
}
