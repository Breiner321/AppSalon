<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pruebas de la revisión de seguridad del CRUD de servicios (A10), contra la
 * base appsalon_mvc_php_test. Complementan ServicioCrudTest del Dev 2.
 * Los hallazgos están en docs/revision-A10.md.
 */
class RevisionCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_un_servicio_solo_se_borra_por_delete(): void
    {
        $servicio = Service::factory()->create();
        $url = route('admin.servicios.destroy', $servicio);

        $this->actingAs($this->admin)->get($url)->assertStatus(405);
        $this->actingAs($this->admin)->post($url)->assertStatus(405);
        $this->assertDatabaseHas('servicios', ['id' => $servicio->id]);

        $this->actingAs($this->admin)->delete($url)->assertRedirect(route('admin.servicios.index'));
        $this->assertDatabaseMissing('servicios', ['id' => $servicio->id]);
    }

    /**
     * Se reemplaza el middleware CSRF por una copia que no se salta la
     * verificación en los tests (igual que en AutenticacionTest).
     */
    public function test_un_delete_sin_token_csrf_devuelve_419_y_no_borra(): void
    {
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $servicio = Service::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.servicios.destroy', $servicio))
            ->assertStatus(419);

        $this->assertDatabaseHas('servicios', ['id' => $servicio->id]);
    }

    public function test_un_invitado_no_puede_crear_ni_borrar(): void
    {
        $servicio = Service::factory()->create();

        $this->post(route('admin.servicios.store'), ['nombre' => 'X', 'precio' => 10, 'duracion' => 30])
            ->assertRedirect(route('login'));
        $this->delete(route('admin.servicios.destroy', $servicio))->assertRedirect(route('login'));

        $this->assertDatabaseCount('servicios', 1);
    }

    /**
     * Solo se guardan los campos validados: los campos extra del formulario se ignoran.
     */
    public function test_los_campos_no_validados_se_ignoran(): void
    {
        $this->actingAs($this->admin)->post(route('admin.servicios.store'), [
            'id' => 999,
            'nombre' => 'Masaje capilar',
            'precio' => 30,
            'duracion' => 30,
            'activo' => 1,
            'created_at' => '2000-01-01 00:00:00',
        ])->assertRedirect(route('admin.servicios.index'));

        $servicio = Service::where('nombre', 'Masaje capilar')->firstOrFail();
        $this->assertNotSame(999, $servicio->id);
        $this->assertNotSame('2000-01-01', $servicio->created_at->toDateString());
    }

    public function test_el_html_del_nombre_y_la_descripcion_sale_escapado(): void
    {
        Service::factory()->create([
            'nombre' => '<script>alert(1)</script>',
            'descripcion' => '<img src=x onerror=alert(2)>',
        ]);

        $this->get(route('servicios.catalogo'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(2)>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);

        $this->actingAs($this->admin)->get(route('admin.servicios.index'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    /**
     * Sin N+1: el número de consultas no crece con la cantidad de servicios.
     */
    public function test_el_listado_y_el_catalogo_no_tienen_consultas_n_mas_1(): void
    {
        $consultas = function (string $url): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->admin)->get($url)->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        foreach ([route('admin.servicios.index'), route('servicios.catalogo'), route('admin.index')] as $url) {
            Service::query()->delete();
            Service::factory()->count(2)->create();
            $conPocos = $consultas($url);

            Service::factory()->count(8)->create();
            $conMuchos = $consultas($url);

            $this->assertSame($conPocos, $conMuchos, "Consultas N+1 en {$url}");
        }
    }
}
