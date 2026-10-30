<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del catálogo público de servicios (U4), contra la base appsalon_mvc_php_test.
 */
class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_catalogo_muestra_los_activos_y_oculta_los_inactivos(): void
    {
        Service::factory()->create(['nombre' => 'Corte de prueba', 'precio' => 90, 'duracion' => 60]);
        Service::factory()->inactivo()->create(['nombre' => 'Servicio oculto']);

        foreach (['/', '/servicios'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertViewIs('services.index')
                ->assertSee('Corte de prueba')
                ->assertSee('$90.00')
                ->assertSee('60 min')
                ->assertDontSee('Servicio oculto');
        }
    }

    public function test_sin_servicios_activos_muestra_el_estado_vacio(): void
    {
        Service::factory()->inactivo()->create();

        $this->get('/servicios')
            ->assertOk()
            ->assertSee('Aún no hay servicios disponibles');
    }

    public function test_el_invitado_ve_la_invitacion_a_registrarse_y_el_cliente_no(): void
    {
        $this->get('/')->assertSee('Crea tu cuenta gratis');

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertDontSee('Crea tu cuenta gratis');
    }
}
