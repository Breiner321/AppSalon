<?php

namespace Tests\Feature;

use App\Mail\CitaNotificacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\PreparaAgenda;
use Tests\TestCase;

/**
 * Panel avanzado (requerimiento 7): dashboard con estadísticas, agenda del día
 * y de la semana, búsqueda y filtros de citas y cambio de estado.
 * "Hoy" es el lunes 2026-12-07 (ver PreparaAgenda).
 */
class AdminCitasTest extends TestCase
{
    use PreparaAgenda, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararAgenda();
        $this->admin = User::factory()->admin()->create();
        Mail::fake();
    }

    public function test_un_cliente_no_entra_a_la_agenda_ni_a_las_citas_del_panel(): void
    {
        $cliente = User::factory()->create();
        $cita = $this->citaExistente('2026-12-08', '10:00:00');

        $this->actingAs($cliente)->get(route('admin.agenda'))->assertForbidden();
        $this->actingAs($cliente)->get(route('admin.citas.index'))->assertForbidden();
        $this->actingAs($cliente)->patch(route('admin.citas.estado', $cita), ['estado' => 'confirmada'])->assertForbidden();
    }

    public function test_el_dashboard_muestra_citas_de_hoy_ingresos_y_clientes(): void
    {
        $laura = User::factory()->create(['nombre' => 'Laura', 'apellido' => 'Gómez']);
        $hoy1 = $this->citaExistente('2026-12-07', '10:00:00', 60, $laura, 'completada');   // $50 cobrados
        $this->citaExistente('2026-12-07', '12:00:00', 60, null, 'confirmada');              // $50 esperados
        $this->citaExistente('2026-12-07', '15:00:00', 60, null, 'cancelada');               // no cuenta
        $this->citaExistente('2026-12-02', '10:00:00', 60, null, 'completada');              // mes: +$50
        $this->citaExistente('2026-11-20', '10:00:00', 60, null, 'completada');              // otro mes

        $this->actingAs($this->admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('data-test="citas-hoy">2<', false)
            ->assertSee('data-test="ingresos-hoy">$50.00<', false)
            ->assertSee('data-test="ingresos-mes">$100.00<', false)
            ->assertSee('data-test="clientes">5<', false)
            ->assertSee('$100.00 esperados')
            ->assertSee('Laura Gómez');
    }

    public function test_la_agenda_del_dia_y_de_la_semana(): void
    {
        $lunes = $this->citaExistente('2026-12-07', '10:00:00');
        $miercoles = $this->citaExistente('2026-12-09', '11:00:00');
        $siguienteSemana = $this->citaExistente('2026-12-14', '11:00:00');

        $this->actingAs($this->admin)->get(route('admin.agenda'))
            ->assertOk()
            ->assertSee('data-test="agenda-cita-'.$lunes->id.'"', false)
            ->assertDontSee('data-test="agenda-cita-'.$miercoles->id.'"', false);

        $this->actingAs($this->admin)->get(route('admin.agenda', ['vista' => 'semana', 'fecha' => '2026-12-09']))
            ->assertOk()
            ->assertSee('data-test="agenda-cita-'.$lunes->id.'"', false)
            ->assertSee('data-test="agenda-cita-'.$miercoles->id.'"', false)
            ->assertDontSee('data-test="agenda-cita-'.$siguienteSemana->id.'"', false)
            ->assertSee('Del 7 de diciembre al 13 de diciembre de 2026');
    }

    public function test_busca_y_filtra_citas(): void
    {
        $ana = User::factory()->create(['nombre' => 'Ana', 'apellido' => 'Pérez', 'email' => 'ana@correo.com']);
        $deAna = $this->citaExistente('2026-12-08', '10:00:00', 60, $ana, 'confirmada');
        $otra = $this->citaExistente('2026-12-10', '10:00:00', 60, null, 'pendiente');
        $vieja = $this->citaExistente('2026-11-10', '10:00:00', 60, $ana, 'completada');

        $ver = fn (array $filtros) => $this->actingAs($this->admin)->get(route('admin.citas.index', $filtros))->assertOk();

        $ver(['q' => 'Ana Pér'])
            ->assertSee('data-test="cita-'.$deAna->id.'"', false)
            ->assertSee('data-test="cita-'.$vieja->id.'"', false)
            ->assertDontSee('data-test="cita-'.$otra->id.'"', false);

        $ver(['estado' => 'pendiente'])
            ->assertSee('data-test="cita-'.$otra->id.'"', false)
            ->assertDontSee('data-test="cita-'.$deAna->id.'"', false);

        $ver(['desde' => '2026-12-01', 'hasta' => '2026-12-09'])
            ->assertSee('data-test="cita-'.$deAna->id.'"', false)
            ->assertDontSee('data-test="cita-'.$otra->id.'"', false)
            ->assertDontSee('data-test="cita-'.$vieja->id.'"', false)
            ->assertSee('1 cita(s) encontrada(s)');

        $this->actingAs($this->admin)->get(route('admin.citas.index', ['desde' => '2026-12-09', 'hasta' => '2026-12-01']))
            ->assertSessionHasErrors('hasta');
    }

    public function test_confirma_una_cita_y_avisa_al_cliente(): void
    {
        $cliente = User::factory()->create();
        $cita = $this->citaExistente('2026-12-08', '10:00:00', 60, $cliente);

        $this->actingAs($this->admin)->from(route('admin.agenda'))
            ->patch(route('admin.citas.estado', $cita), ['estado' => 'confirmada'])
            ->assertRedirect(route('admin.agenda'))
            ->assertSessionHas('success');

        $this->assertSame('confirmada', $cita->refresh()->estado);
        Mail::assertSent(CitaNotificacion::class, fn ($m) => $m->hasTo($cliente->email) && $m->tipo === 'confirmada');
    }

    public function test_no_permite_cambiar_una_cita_cancelada_o_completada(): void
    {
        $cancelada = $this->citaExistente('2026-12-08', '10:00:00', 60, null, 'cancelada');
        $completada = $this->citaExistente('2026-12-01', '10:00:00', 60, null, 'completada');

        $this->actingAs($this->admin)->patch(route('admin.citas.estado', $cancelada), ['estado' => 'confirmada'])
            ->assertSessionHasErrors('estado');
        $this->actingAs($this->admin)->patch(route('admin.citas.estado', $completada), ['estado' => 'cancelada'])
            ->assertSessionHasErrors('estado');
        $this->actingAs($this->admin)->patch(route('admin.citas.estado', $cancelada), ['estado' => 'inventado'])
            ->assertSessionHasErrors('estado');

        $this->assertSame('cancelada', $cancelada->refresh()->estado);
        $this->assertSame('completada', $completada->refresh()->estado);
        Mail::assertNothingSent();
    }
}
