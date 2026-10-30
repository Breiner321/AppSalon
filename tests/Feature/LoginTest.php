<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de inicio y cierre de sesión (A4), contra la base appsalon_mvc_php_test.
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La fábrica de usuarios usa la contraseña 'password'.
     */
    private const PASSWORD = 'password';

    public function test_el_cliente_inicia_sesion_y_va_al_catalogo(): void
    {
        $cliente = User::factory()->create(['nombre' => 'Laura']);

        $this->post('/login', ['email' => $cliente->email, 'password' => self::PASSWORD])
            ->assertRedirect(route('servicios.catalogo'))
            ->assertSessionHas('success', 'Bienvenido, Laura');

        $this->assertAuthenticatedAs($cliente);
    }

    public function test_el_admin_inicia_sesion_y_va_al_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => self::PASSWORD])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_una_contrasena_erronea_no_autentica(): void
    {
        $cliente = User::factory()->create();

        $this->from('/login')
            ->post('/login', ['email' => $cliente->email, 'password' => 'incorrecta'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Credenciales incorrectas'])
            ->assertSessionHasInput('email', $cliente->email)
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    /**
     * Un email que no existe recibe el mismo mensaje que una contraseña errónea.
     */
    public function test_un_email_inexistente_no_revela_que_no_existe(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => 'nadie@example.com', 'password' => 'loquesea'])
            ->assertSessionHasErrors(['email' => 'Credenciales incorrectas']);

        $this->assertGuest();
    }

    public function test_el_logout_cierra_la_sesion(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionHas('info', 'Sesión cerrada');

        $this->assertGuest();
    }

    public function test_un_usuario_autenticado_que_entra_a_login_es_redirigido(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect(route('servicios.catalogo'));

        $this->actingAs(User::factory()->admin()->create())
            ->get('/login')
            ->assertRedirect(route('admin.index'));
    }
}
