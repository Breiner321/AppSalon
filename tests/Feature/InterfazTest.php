<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del pulido de interfaz (U9), contra la base appsalon_mvc_php_test.
 */
class InterfazTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_alertas_flash_se_cierran_solas(): void
    {
        $this->withSession(['success' => 'Servicio creado'])
            ->get('/servicios')
            ->assertSee('alert-success', false)
            ->assertSee('data-autocierre', false)
            ->assertSee('bootstrap.Alert.getOrCreateInstance(alerta).close()', false);
    }

    public function test_los_formularios_marcan_los_campos_obligatorios(): void
    {
        $this->get('/registro')
            ->assertSee('for="nombre" class="form-label requerido"', false)
            ->assertSee('Los campos marcados con', false);

        $this->get('/login')
            ->assertSee('for="email" class="form-label requerido"', false);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.servicios.create'))
            ->assertSee('for="precio" class="form-label requerido"', false)
            ->assertDontSee('for="descripcion" class="form-label requerido"', false);
    }

    public function test_el_listado_vacio_invita_a_crear_el_primer_servicio(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.servicios.index'))
            ->assertSee('Aún no hay servicios registrados')
            ->assertSee('Crear el primer servicio');
    }

    public function test_el_layout_tiene_favicon_y_marca(): void
    {
        $this->get('/')
            ->assertSee('favicon.svg', false)
            ->assertSee('navbar-brand', false)
            ->assertSee('AppSalon');

        $this->assertFileExists(public_path('favicon.svg'));
    }
}
