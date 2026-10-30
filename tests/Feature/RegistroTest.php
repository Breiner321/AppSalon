<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pruebas del registro de usuarios (A3), contra la base appsalon_mvc_php_test.
 */
class RegistroTest extends TestCase
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
            'telefono' => '5512345678',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
        ], $cambios);
    }

    public function test_el_formulario_de_registro_carga(): void
    {
        $this->get('/registro')
            ->assertOk()
            ->assertViewIs('auth.registro')
            ->assertSee('Crear cuenta')
            ->assertSee('name="_token"', false);
    }

    public function test_un_registro_valido_crea_el_usuario_con_la_contrasena_hasheada(): void
    {
        $this->post('/registro', $this->datos())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Revisa tu correo para confirmar la cuenta');

        $usuario = User::where('email', 'ana@example.com')->firstOrFail();

        $this->assertSame('Ana', $usuario->nombre);
        $this->assertSame('5512345678', $usuario->telefono);
        $this->assertNotSame('secreto123', $usuario->password);
        $this->assertTrue(Hash::check('secreto123', $usuario->password));
        $this->assertFalse($usuario->isAdmin());
        $this->assertFalse($usuario->confirmado);
    }

    public function test_un_email_duplicado_falla(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->from('/registro')
            ->post('/registro', $this->datos())
            ->assertRedirect('/registro')
            ->assertSessionHasErrors(['email' => 'Ya existe una cuenta con ese correo electrónico.']);

        $this->assertSame(1, User::where('email', 'ana@example.com')->count());
    }

    public function test_una_contrasena_de_menos_de_6_caracteres_falla(): void
    {
        $this->from('/registro')
            ->post('/registro', $this->datos(['password' => '12345', 'password_confirmation' => '12345']))
            ->assertRedirect('/registro')
            ->assertSessionHasErrors(['password' => 'La contraseña debe tener al menos 6 caracteres.']);

        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_un_telefono_con_letras_falla(): void
    {
        $this->from('/registro')
            ->post('/registro', $this->datos(['telefono' => '55123abc78']))
            ->assertRedirect('/registro')
            ->assertSessionHasErrors(['telefono' => 'El teléfono debe tener exactamente 10 dígitos.']);

        $this->assertDatabaseCount('usuarios', 0);
    }

    /**
     * Un formulario no puede convertir al nuevo usuario en administrador.
     */
    public function test_el_registro_ignora_el_campo_admin(): void
    {
        $this->post('/registro', $this->datos(['admin' => '1']))->assertRedirect(route('login'));

        $this->assertFalse(User::where('email', 'ana@example.com')->firstOrFail()->isAdmin());
    }
}
