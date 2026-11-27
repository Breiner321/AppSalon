<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\PreparaAgenda;
use Tests\TestCase;

/**
 * Rol empleado (Entrega Final): ve el panel, la agenda y las citas y puede
 * cambiar su estado, pero no gestiona usuarios, servicios, horarios ni reportes.
 */
class EmpleadoTest extends TestCase
{
    use PreparaAgenda, RefreshDatabase;

    private User $empleado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararAgenda();
        Mail::fake();
        $this->empleado = User::factory()->empleado()->create(['nombre' => 'Sofía']);
    }

    public function test_el_empleado_entra_al_panel_la_agenda_y_las_citas(): void
    {
        $this->actingAs($this->empleado);

        $this->get(route('admin.index'))->assertOk()->assertSee('Panel del salón')->assertDontSee('Nuevo servicio');
        $this->get(route('admin.agenda'))->assertOk();
        $this->get(route('admin.citas.index'))->assertOk();
    }

    public function test_el_empleado_no_entra_a_lo_que_es_solo_del_admin(): void
    {
        $this->actingAs($this->empleado);

        foreach (['admin.usuarios.index', 'admin.servicios.index', 'admin.servicios.create', 'admin.horarios.index', 'admin.reportes.index'] as $ruta) {
            $this->get(route($ruta))->assertForbidden();
        }

        $this->patch(route('admin.usuarios.rol', User::factory()->create()), ['rol' => 'admin'])->assertForbidden();
    }

    public function test_el_menu_del_empleado_solo_muestra_sus_secciones(): void
    {
        $this->actingAs($this->empleado)->get(route('admin.index'))
            ->assertSee(route('admin.agenda'), false)
            ->assertSee(route('admin.citas.index'), false)
            ->assertDontSee(route('admin.usuarios.index'), false)
            ->assertDontSee(route('admin.reportes.index'), false);
    }

    public function test_el_empleado_confirma_una_cita(): void
    {
        $cita = $this->citaExistente('2026-12-09', '10:00:00');

        $this->actingAs($this->empleado)
            ->patch(route('admin.citas.estado', $cita), ['estado' => Appointment::CONFIRMADA])
            ->assertSessionHas('success');

        $this->assertSame(Appointment::CONFIRMADA, $cita->refresh()->estado);
    }

    public function test_al_iniciar_sesion_el_empleado_va_al_panel(): void
    {
        $empleado = User::factory()->empleado()->create(['email' => 'sofia@correo.com', 'password' => 'secreto123']);

        $this->post('/login', ['email' => 'sofia@correo.com', 'password' => 'secreto123'])
            ->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($empleado);
    }

    public function test_los_empleados_no_cuentan_como_clientes_en_el_panel(): void
    {
        User::factory()->count(2)->create();

        $this->actingAs($this->empleado)->get(route('admin.index'))
            ->assertSeeInOrder(['data-test="clientes"', '2'], false);
    }
}
