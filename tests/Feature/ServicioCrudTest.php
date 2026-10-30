<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pruebas del CRUD de servicios del panel (U5 y U6), contra la base appsalon_mvc_php_test.
 */
class ServicioCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Datos válidos de un servicio; cada prueba sobrescribe lo que necesite.
     *
     * @param  array<string, mixed>  $cambios
     * @return array<string, mixed>
     */
    private function datos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Corte con lavado',
            'precio' => '90.00',
            'descripcion' => 'Corte a la medida con lavado y secado.',
            'duracion' => 60,
            'activo' => '1',
        ], $cambios);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_el_formulario_de_alta_carga(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.servicios.create'))
            ->assertOk()
            ->assertViewIs('admin.servicios.form')
            ->assertSee('Nuevo servicio')
            ->assertSee('name="_token"', false);
    }

    public function test_el_admin_crea_un_servicio(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.servicios.store'), $this->datos())
            ->assertRedirect(route('admin.servicios.index'))
            ->assertSessionHas('success', 'Servicio creado');

        $this->assertDatabaseHas('servicios', [
            'nombre' => 'Corte con lavado',
            'precio' => 90.00,
            'duracion' => 60,
            'activo' => true,
        ]);
    }

    public function test_el_switch_apagado_crea_el_servicio_inactivo(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.servicios.store'), $this->datos(['activo' => '0']))
            ->assertRedirect(route('admin.servicios.index'));

        $this->assertDatabaseHas('servicios', ['nombre' => 'Corte con lavado', 'activo' => false]);
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function datosInvalidos(): array
    {
        return [
            'precio negativo' => ['precio', '-1'],
            'precio de 1000' => ['precio', '1000'],
            'nombre vacío' => ['nombre', ''],
            'nombre de 61 caracteres' => ['nombre', str_repeat('a', 61)],
        ];
    }

    #[DataProvider('datosInvalidos')]
    public function test_se_rechazan_datos_invalidos(string $campo, mixed $valor): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.servicios.create'))
            ->post(route('admin.servicios.store'), $this->datos([$campo => $valor]))
            ->assertRedirect(route('admin.servicios.create'))
            ->assertSessionHasErrors($campo);

        $this->assertSame(0, Service::count());
    }
}
