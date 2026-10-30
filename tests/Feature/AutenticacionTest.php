<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Casos de autenticación que completan RegistroTest, LoginTest y SeguridadTest (A8),
 * contra la base appsalon_mvc_php_test.
 */
class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Datos válidos de registro; cada prueba sobrescribe lo que necesite.
     *
     * @param  array<string, string>  $cambios
     * @return array<string, string>
     */
    private function datos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Ana',
            'apellido' => 'López',
            'email' => 'ana@example.com',
            'telefono' => '3001112233',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
        ], $cambios);
    }

    /**
     * Envía el registro desde /registro y comprueba que vuelve con el error y sin crear el usuario.
     *
     * @param  array<string, string>  $cambios
     */
    private function assertRegistroFalla(array $cambios, string $campo, string $mensaje): void
    {
        $this->from('/registro')
            ->post('/registro', $this->datos($cambios))
            ->assertRedirect('/registro')
            ->assertSessionHasErrors([$campo => $mensaje]);

        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_un_telefono_de_9_digitos_falla(): void
    {
        $this->assertRegistroFalla(['telefono' => '300111223'], 'telefono', 'El teléfono debe tener exactamente 10 dígitos.');
    }

    public function test_un_telefono_de_11_digitos_falla(): void
    {
        $this->assertRegistroFalla(['telefono' => '30011122334'], 'telefono', 'El teléfono debe tener exactamente 10 dígitos.');
    }

    public function test_un_email_de_mas_de_30_caracteres_falla(): void
    {
        $email = str_repeat('a', 20).'@example.com'; // 32 caracteres

        $this->assertRegistroFalla(['email' => $email], 'email', 'El correo electrónico no debe tener más de 30 caracteres.');
    }

    public function test_una_confirmacion_de_contrasena_distinta_falla(): void
    {
        $this->assertRegistroFalla(['password_confirmation' => 'otraclave123'], 'password', 'Las contraseñas no coinciden.');
    }

    public function test_admin_redirige_al_login_despues_del_logout(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk();

        $this->post('/logout')->assertRedirect(route('login'));

        $this->get('/admin')->assertRedirect(route('login'));
    }

    /**
     * En los tests Laravel omite la verificación CSRF; aquí se reemplaza el
     * middleware por una copia que sí la hace, para probarla de forma aislada.
     */
    public function test_un_post_sin_token_csrf_devuelve_419(): void
    {
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $this->post('/login', ['email' => 'nadie@example.com', 'password' => 'loquesea'])
            ->assertStatus(419)
            ->assertSee('La página expiró');

        $this->post('/registro', $this->datos())->assertStatus(419);
        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_el_throttle_del_login_devuelve_429(): void
    {
        $usuario = User::factory()->create();

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->post('/login', ['email' => $usuario->email, 'password' => 'incorrecta'])->assertRedirect();
        }

        // Al sexto intento ni siquiera la contraseña correcta pasa.
        $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])->assertStatus(429);
        $this->assertGuest();
    }
}
