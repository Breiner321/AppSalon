<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de la auditoría de seguridad (A6), contra la base appsalon_mvc_php_test.
 */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_sexto_intento_de_login_en_un_minuto_devuelve_429(): void
    {
        for ($intento = 1; $intento <= 5; $intento++) {
            $this->post('/login', ['email' => 'nadie@example.com', 'password' => 'incorrecta'])
                ->assertRedirect();
        }

        $this->post('/login', ['email' => 'nadie@example.com', 'password' => 'incorrecta'])
            ->assertStatus(429)
            ->assertSee('Demasiados intentos');
    }

    public function test_el_sexto_intento_de_registro_en_un_minuto_devuelve_429(): void
    {
        for ($intento = 1; $intento <= 5; $intento++) {
            $this->post('/registro', [])->assertRedirect();
        }

        $this->post('/registro', [])->assertStatus(429);
    }

    /**
     * admin está fuera de $fillable: aunque el formulario lo envíe, se guarda 0.
     */
    public function test_un_registro_con_admin_1_crea_un_usuario_con_admin_0(): void
    {
        $this->post('/registro', [
            'nombre' => 'Mario',
            'apellido' => 'Ruiz',
            'email' => 'mario@example.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'admin' => '1',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('usuarios', ['email' => 'mario@example.com', 'admin' => 0]);
        $this->assertFalse(User::where('email', 'mario@example.com')->firstOrFail()->isAdmin());
    }

    /**
     * La contraseña y el token nunca salen al serializar un usuario.
     */
    public function test_password_y_token_estan_ocultos_al_serializar(): void
    {
        $datos = User::factory()->create()->toArray();

        $this->assertArrayNotHasKey('password', $datos);
        $this->assertArrayNotHasKey('token', $datos);
    }
}
