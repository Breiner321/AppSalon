<?php

namespace Tests\Feature;

use App\Models\Bloqueo;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PreparaAgenda;
use Tests\TestCase;

/**
 * Gestión de horarios y disponibilidad del salón (requerimientos 6 y 7).
 * "Hoy" es el lunes 2026-12-07 (ver PreparaAgenda).
 */
class HorariosTest extends TestCase
{
    use PreparaAgenda, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararAgenda();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Los siete días con el mismo horario, cambiando lo indicado.
     *
     * @return array<int, array<string, mixed>>
     */
    private function semana(array $cambios = []): array
    {
        $semana = [];
        foreach (range(0, 6) as $dia) {
            $semana[$dia] = ($cambios[$dia] ?? []) + ['abierto' => $dia !== 0 ? '1' : '0', 'apertura' => '09:00', 'cierre' => '18:00'];
        }

        return $semana;
    }

    public function test_solo_el_admin_gestiona_horarios(): void
    {
        $cliente = User::factory()->create();

        $this->actingAs($cliente)->get(route('admin.horarios.index'))->assertForbidden();
        $this->actingAs($cliente)->put(route('admin.horarios.update'), ['horarios' => $this->semana()])->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.horarios.index'))->assertOk()->assertSee('Horario semanal');
    }

    public function test_actualiza_el_horario_y_cambia_la_disponibilidad(): void
    {
        $this->actingAs($this->admin)->put(route('admin.horarios.update'), [
            'horarios' => $this->semana([2 => ['apertura' => '12:00', 'cierre' => '14:00'], 3 => ['abierto' => '0']]),
        ])->assertRedirect(route('admin.horarios.index'))->assertSessionHas('success');

        $martes = Horario::where('diaSemana', 2)->first();
        $this->assertSame('12:00:00', $martes->apertura);
        $this->assertSame('14:00:00', $martes->cierre);
        $this->assertFalse(Horario::where('diaSemana', 3)->first()->abierto);

        $servicio = $this->servicio(60);
        $cliente = User::factory()->create();

        // Martes 8: solo de 12:00 a 14:00. Miércoles 9: cerrado.
        $turnos = $this->actingAs($cliente)
            ->getJson(route('citas.disponibilidad', ['fecha' => '2026-12-08', 'servicios' => [$servicio->id]]))
            ->json('turnos');
        $this->assertSame(['12:00', '12:30', '13:00'], $turnos);

        $this->actingAs($cliente)
            ->getJson(route('citas.disponibilidad', ['fecha' => '2026-12-09', 'servicios' => [$servicio->id]]))
            ->assertJson(['abierto' => false]);
    }

    public function test_valida_que_el_cierre_sea_despues_de_la_apertura(): void
    {
        $this->actingAs($this->admin)->put(route('admin.horarios.update'), [
            'horarios' => $this->semana([1 => ['apertura' => '18:00', 'cierre' => '09:00']]),
        ])->assertSessionHasErrors(['horarios.1.cierre' => 'El cierre debe ser después de la apertura.']);

        $this->actingAs($this->admin)->put(route('admin.horarios.update'), [
            'horarios' => $this->semana([1 => ['apertura' => 'nueve']]),
        ])->assertSessionHasErrors('horarios.1.apertura');

        $this->assertSame('09:00:00', Horario::where('diaSemana', 1)->first()->apertura);
    }

    public function test_bloquea_y_desbloquea_una_fecha(): void
    {
        $this->actingAs($this->admin)->post(route('admin.bloqueos.store'), ['fecha' => '2026-12-08', 'motivo' => 'Festivo'])
            ->assertRedirect(route('admin.horarios.index'))
            ->assertSessionHas('success');

        $bloqueo = Bloqueo::sole();
        $this->assertSame('Festivo', $bloqueo->motivo);

        // No se repite ni se bloquea el pasado.
        $this->actingAs($this->admin)->post(route('admin.bloqueos.store'), ['fecha' => '2026-12-08'])->assertSessionHasErrors('fecha');
        $this->actingAs($this->admin)->post(route('admin.bloqueos.store'), ['fecha' => '2026-12-01'])->assertSessionHasErrors('fecha');

        $servicio = $this->servicio();
        $this->actingAs(User::factory()->create())
            ->getJson(route('citas.disponibilidad', ['fecha' => '2026-12-08', 'servicios' => [$servicio->id]]))
            ->assertJson(['abierto' => false, 'motivo' => 'Festivo', 'turnos' => []]);

        $this->actingAs($this->admin)->delete(route('admin.bloqueos.destroy', $bloqueo))->assertSessionHas('success');
        $this->assertSame(0, Bloqueo::count());
    }

    public function test_avisa_si_la_fecha_bloqueada_ya_tiene_citas(): void
    {
        $this->citaExistente('2026-12-08', '10:00:00');

        $this->actingAs($this->admin)->post(route('admin.bloqueos.store'), ['fecha' => '2026-12-08'])
            ->assertSessionHas('warning');
    }
}
