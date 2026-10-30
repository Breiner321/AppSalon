<?php

namespace Tests\Feature;

use App\Mail\ConfirmarCuenta;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Pruebas de la confirmación de cuenta por token (A7), contra la base appsalon_mvc_php_test.
 */
class ConfirmacionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea un usuario sin confirmar con el token indicado.
     */
    private function usuarioSinConfirmar(string $token = 'abcdefghij12345'): User
    {
        $usuario = User::factory()->unconfirmed()->create();
        $usuario->forceFill(['token' => $token])->save();

        return $usuario;
    }

    public function test_el_registro_crea_el_usuario_sin_confirmar_con_token_y_envia_el_correo(): void
    {
        Mail::fake();

        $this->post('/registro', [
            'nombre' => 'Ana',
            'apellido' => 'López',
            'email' => 'ana@example.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Revisa tu correo para confirmar la cuenta');

        $usuario = User::where('email', 'ana@example.com')->firstOrFail();

        $this->assertFalse($usuario->confirmado);
        $this->assertSame(15, strlen($usuario->token));

        Mail::assertSent(ConfirmarCuenta::class, function (ConfirmarCuenta $correo) use ($usuario) {
            return $correo->hasTo('ana@example.com')
                && str_contains($correo->render(), route('confirmar', $usuario->token));
        });
    }

    public function test_un_token_valido_confirma_la_cuenta_y_borra_el_token(): void
    {
        $usuario = $this->usuarioSinConfirmar();

        $this->get('/confirmar/abcdefghij12345')
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Cuenta confirmada');

        $usuario->refresh();
        $this->assertTrue($usuario->confirmado);
        $this->assertNull($usuario->token);

        // El enlace sirve una sola vez.
        $this->get('/confirmar/abcdefghij12345')->assertSessionHas('error', 'Token no válido');
    }

    public function test_un_token_invalido_muestra_el_error(): void
    {
        $this->usuarioSinConfirmar();

        $this->get('/confirmar/tokenquenoexist')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Token no válido');

        $this->assertDatabaseHas('usuarios', ['token' => 'abcdefghij12345', 'confirmado' => 0]);
    }

    public function test_un_usuario_no_confirmado_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->usuarioSinConfirmar();

        $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('warning', 'Confirma tu cuenta desde el enlace enviado a tu correo');

        $this->assertGuest();
    }

    public function test_los_usuarios_sembrados_si_pueden_iniciar_sesion(): void
    {
        $this->seed(UserSeeder::class);

        $this->post('/login', ['email' => 'admin@appsalon.com', 'password' => 'Admin123'])
            ->assertRedirect(route('admin.index'));
        $this->assertAuthenticated();

        $this->post('/logout');

        $this->post('/login', ['email' => 'cliente@appsalon.com', 'password' => 'Cliente123'])
            ->assertRedirect(route('servicios.catalogo'));
        $this->assertAuthenticated();
    }
}
