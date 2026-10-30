<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Pruebas de la navegación por rol y del panel de administración (U7),
 * contra la base appsalon_mvc_php_test.
 */
class NavegacionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Revisa los enlaces del navbar por su URL (href), no por el texto,
     * para que no los confunda con botones del contenido de la página.
     *
     * @param  list<string>  $visibles
     * @param  list<string>  $ocultos
     */
    private function assertEnlaces(TestResponse $respuesta, array $visibles, array $ocultos): void
    {
        foreach ($visibles as $url) {
            $respuesta->assertSee('href="'.$url.'"', false);
        }

        foreach ($ocultos as $url) {
            $respuesta->assertDontSee('href="'.$url.'"', false);
        }
    }

    public function test_el_invitado_ve_servicios_login_y_registro(): void
    {
        $respuesta = $this->get('/servicios')->assertOk();

        $this->assertEnlaces(
            $respuesta,
            [route('servicios.catalogo'), route('login'), route('registro')],
            [route('admin.index'), route('admin.servicios.index')],
        );
        $respuesta->assertDontSee('Cerrar sesión');
    }

    public function test_el_cliente_ve_servicios_y_su_menu_pero_no_el_panel(): void
    {
        $cliente = User::factory()->create(['nombre' => 'Laura']);

        $respuesta = $this->actingAs($cliente)->get('/servicios')->assertOk();

        $this->assertEnlaces(
            $respuesta,
            [route('servicios.catalogo')],
            [route('login'), route('registro'), route('admin.index'), route('admin.servicios.index')],
        );
        $respuesta->assertSee('Laura')->assertSee('Cerrar sesión');
    }

    public function test_el_admin_ve_panel_gestionar_servicios_y_su_menu(): void
    {
        $admin = User::factory()->admin()->create(['nombre' => 'Marta']);

        $respuesta = $this->actingAs($admin)->get('/servicios')->assertOk();

        $this->assertEnlaces(
            $respuesta,
            [route('servicios.catalogo'), route('admin.index'), route('admin.servicios.index')],
            [route('login'), route('registro')],
        );
        $respuesta->assertSee('Gestionar servicios')->assertSee('Marta')->assertSee('Cerrar sesión');
    }

    /**
     * Devuelve true si el enlace del navbar hacia $url está marcado como activo.
     */
    private function enlaceActivo(TestResponse $respuesta, string $url): bool
    {
        preg_match_all('/<a\s[^>]*class="nav-link[^"]*"[^>]*>/s', $respuesta->getContent(), $enlaces);

        foreach ($enlaces[0] as $etiqueta) {
            if (str_contains($etiqueta, 'href="'.$url.'"')) {
                return str_contains($etiqueta, 'aria-current="page"');
            }
        }

        return false;
    }

    public function test_el_enlace_activo_cambia_segun_la_pagina(): void
    {
        $admin = User::factory()->admin()->create();

        $listado = $this->actingAs($admin)->get(route('admin.servicios.index'));
        $this->assertTrue($this->enlaceActivo($listado, route('admin.servicios.index')));
        $this->assertFalse($this->enlaceActivo($listado, route('admin.index')));

        $panel = $this->actingAs($admin)->get(route('admin.index'));
        $this->assertTrue($this->enlaceActivo($panel, route('admin.index')));
        $this->assertFalse($this->enlaceActivo($panel, route('admin.servicios.index')));
    }

    public function test_el_panel_muestra_los_conteos_y_los_accesos(): void
    {
        Service::factory()->count(3)->create();
        Service::factory()->inactivo()->count(2)->create();
        User::factory()->count(4)->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSeeInOrder(['data-test="servicios-activos">3<', 'data-test="servicios-inactivos">2<', 'data-test="usuarios-registrados">5<'], false)
            ->assertSee('Nuevo servicio')
            ->assertSee('Ver servicios');
    }
}
