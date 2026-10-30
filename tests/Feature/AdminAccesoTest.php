<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del acceso al panel de administración (A5), contra la base appsalon_mvc_php_test.
 */
class AdminAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_en_admin_va_al_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/servicios')->assertRedirect(route('login'));
    }

    public function test_el_cliente_recibe_403_en_admin_y_en_admin_servicios(): void
    {
        $cliente = User::factory()->create();

        $this->actingAs($cliente)->get('/admin')
            ->assertForbidden()
            ->assertSee('No tienes permiso para ver esta página');

        $this->actingAs($cliente)->get('/admin/servicios')->assertForbidden();
        $this->actingAs($cliente)->get('/admin/servicios/create')->assertForbidden();
        $this->actingAs($cliente)->post('/admin/servicios')->assertForbidden();
    }

    public function test_el_admin_recibe_200_en_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk();
    }
}
