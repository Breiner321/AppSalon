<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de los mensajes en español (A9), contra la base appsalon_mvc_php_test.
 */
class IdiomaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_aplicacion_esta_en_espanol(): void
    {
        $this->assertSame('es', app()->getLocale());
        $this->assertStringContainsString('APP_LOCALE=es', file_get_contents(base_path('.env.example')));
    }

    public function test_los_archivos_de_idioma_tienen_todas_las_claves_del_ingles(): void
    {
        foreach (['validation', 'auth', 'passwords', 'pagination'] as $archivo) {
            $en = require lang_path("en/{$archivo}.php");
            $es = require lang_path("es/{$archivo}.php");

            foreach ($en as $clave => $valor) {
                $this->assertArrayHasKey($clave, $es, "Falta {$archivo}.{$clave} en español");

                if (is_array($valor) && ! in_array($clave, ['custom', 'attributes'], true)) {
                    foreach (array_keys($valor) as $subclave) {
                        $this->assertArrayHasKey($subclave, $es[$clave], "Falta {$archivo}.{$clave}.{$subclave} en español");
                    }
                }
            }
        }
    }

    public function test_los_atributos_se_muestran_con_su_nombre_en_espanol(): void
    {
        $this->assertSame('correo electrónico', __('validation.attributes.email'));
        $this->assertSame('contraseña', __('validation.attributes.password'));
        $this->assertSame('teléfono', __('validation.attributes.telefono'));
        $this->assertSame('duración', __('validation.attributes.duracion'));

        // Una regla sin mensaje propio en el Form Request usa lang/es/validation.php.
        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.servicios.create'))
            ->post(route('admin.servicios.store'), ['nombre' => 'Prueba', 'precio' => '10', 'duracion' => '60', 'activo' => 'quizá'])
            ->assertSessionHasErrors(['activo' => 'El campo activo debe ser verdadero o falso.']);
    }

    public function test_un_error_sin_vista_propia_sale_en_espanol(): void
    {
        // GET a una ruta que solo acepta POST: 405.
        $this->get('/logout')
            ->assertStatus(405)
            ->assertSee('No pudimos completar la solicitud')
            ->assertDontSee('Method Not Allowed');
    }
}
