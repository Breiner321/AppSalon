<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gestión de usuarios del panel (requerimiento 7): búsqueda, filtros,
 * activar o desactivar y cambiar el rol.
 */
class AdminUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['nombre' => 'Marta', 'apellido' => 'Admin']);
    }

    public function test_solo_el_admin_entra_a_usuarios(): void
    {
        $this->get(route('admin.usuarios.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.usuarios.index'))->assertOk();
    }

    public function test_busca_y_filtra_usuarios(): void
    {
        $camila = User::factory()->create(['nombre' => 'Camila', 'apellido' => 'Rojas', 'email' => 'camila@correo.com', 'telefono' => '3101234567']);
        $pedro = User::factory()->inactivo()->create(['nombre' => 'Pedro', 'apellido' => 'Díaz']);
        $nuevo = User::factory()->unconfirmed()->create(['nombre' => 'Nora', 'apellido' => 'Vega']);

        $ver = fn (array $filtros) => $this->actingAs($this->admin)->get(route('admin.usuarios.index', $filtros))->assertOk();
        $fila = fn (User $u) => 'data-test="usuario-'.$u->id.'"';

        $ver(['q' => 'camila@'])->assertSee($fila($camila), false)->assertDontSee($fila($pedro), false);
        $ver(['q' => '3101234'])->assertSee($fila($camila), false);
        $ver(['q' => 'Camila Rojas'])->assertSee($fila($camila), false);
        $ver(['rol' => 'admin'])->assertSee($fila($this->admin), false)->assertDontSee($fila($camila), false);
        $ver(['estado' => 'inactivo'])->assertSee($fila($pedro), false)->assertDontSee($fila($camila), false);
        $ver(['estado' => 'sin_confirmar'])->assertSee($fila($nuevo), false)->assertDontSee($fila($camila), false);
        $ver(['q' => 'nadie-se-llama-así'])->assertSee('No hay usuarios con esos filtros');
    }

    public function test_desactiva_una_cuenta_y_ya_no_puede_entrar(): void
    {
        $cliente = User::factory()->create(['email' => 'cliente@correo.com', 'password' => 'secreto123']);
        $cliente->createToken('movil');

        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $cliente))->assertSessionHas('success');
        $this->assertFalse($cliente->refresh()->activo);
        $this->assertSame(0, $cliente->tokens()->count());

        auth()->logout();
        $this->post('/login', ['email' => 'cliente@correo.com', 'password' => 'secreto123'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Tu cuenta está desactivada. Comunícate con el salón.');
        $this->assertGuest();

        // Se vuelve a activar.
        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $cliente));
        $this->assertTrue($cliente->refresh()->activo);
    }

    public function test_una_sesion_abierta_se_cierra_si_desactivan_la_cuenta(): void
    {
        $cliente = User::factory()->inactivo()->create();

        $this->actingAs($cliente)->get(route('citas.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_cambia_el_rol(): void
    {
        $cliente = User::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.usuarios.rol', $cliente))->assertSessionHas('success');
        $this->assertTrue($cliente->refresh()->isAdmin());

        $this->actingAs($this->admin)->patch(route('admin.usuarios.rol', $cliente));
        $this->assertFalse($cliente->refresh()->isAdmin());
    }

    public function test_el_admin_no_puede_desactivarse_ni_quitarse_el_rol(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $this->admin))->assertSessionHas('warning');
        $this->actingAs($this->admin)->patch(route('admin.usuarios.rol', $this->admin))->assertSessionHas('warning');

        $this->admin->refresh();
        $this->assertTrue($this->admin->activo);
        $this->assertTrue($this->admin->isAdmin());
    }
}
