<?php

namespace Tests\Feature;

use App\Http\Controllers\PasswordResetController;
use App\Mail\RestablecerContrasena;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Recuperación de contraseña por correo (módulo de autenticación).
 */
class RecuperarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_el_login_enlaza_a_recuperar_la_contrasena(): void
    {
        $this->get('/login')->assertSee(route('password.request'), false);
        $this->get(route('password.request'))->assertOk()->assertSee('¿Olvidaste tu contraseña?');
    }

    public function test_envia_el_enlace_al_correo_registrado(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@correo.com']);

        $this->post(route('password.email'), ['email' => 'ana@correo.com'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('info');

        Mail::assertSent(RestablecerContrasena::class, function (RestablecerContrasena $correo) use ($usuario) {
            return $correo->hasTo('ana@correo.com')
                && Password::tokenExists($usuario, $correo->token);
        });
    }

    public function test_no_revela_si_el_correo_existe(): void
    {
        $this->post(route('password.email'), ['email' => 'nadie@correo.com'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('info', PasswordResetController::MENSAJE_ENVIADO);

        Mail::assertNothingSent();

        $this->post(route('password.email'), ['email' => 'no-es-correo'])->assertSessionHasErrors('email');
    }

    public function test_restablece_la_contrasena_con_el_enlace(): void
    {
        $usuario = User::factory()->unconfirmed()->create(['email' => 'ana@correo.com', 'password' => 'vieja123']);
        $usuario->createToken('movil');
        $token = Password::createToken($usuario);

        $this->get(route('password.reset', ['token' => $token, 'email' => 'ana@correo.com']))
            ->assertOk()
            ->assertSee('value="ana@correo.com"', false);

        $this->post(route('password.update'), [
            'token' => $token, 'email' => 'ana@correo.com',
            'password' => 'nueva123', 'password_confirmation' => 'nueva123',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $usuario->refresh();
        $this->assertTrue(Hash::check('nueva123', $usuario->password));
        $this->assertTrue($usuario->confirmado);          // el enlace llegó a su correo
        $this->assertSame(0, $usuario->tokens()->count()); // se cierran los tokens de la API

        $this->post('/login', ['email' => 'ana@correo.com', 'password' => 'nueva123'])->assertRedirect();
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_un_enlace_vencido_o_usado_no_sirve(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@correo.com', 'password' => 'vieja123']);
        $datos = ['email' => 'ana@correo.com', 'password' => 'nueva123', 'password_confirmation' => 'nueva123'];

        $this->post(route('password.update'), $datos + ['token' => 'inventado'])
            ->assertSessionHasErrors(['email' => __('passwords.token')]);

        $token = Password::createToken($usuario);
        $this->travel(61)->minutes();
        $this->post(route('password.update'), $datos + ['token' => $token])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('vieja123', $usuario->refresh()->password));
    }

    public function test_valida_la_contrasena_nueva(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@correo.com']);
        $token = Password::createToken($usuario);

        $this->post(route('password.update'), [
            'token' => $token, 'email' => 'ana@correo.com', 'password' => '123', 'password_confirmation' => '456',
        ])->assertSessionHasErrors('password');
    }

    public function test_el_correo_tiene_el_enlace_en_espanol(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Ana', 'email' => 'ana@correo.com']);
        $correo = new RestablecerContrasena($usuario, 'abc123');

        $correo->assertHasSubject('Restablece tu contraseña de '.config('app.name'));
        $correo->assertSeeInHtml('Hola, Ana');
        $correo->assertSeeInHtml(route('password.reset', ['token' => 'abc123', 'email' => 'ana@correo.com']), false);
        $correo->assertSeeInHtml('60 minutos');
    }
}
