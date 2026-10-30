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

    public function test_el_admin_crea_edita_y_elimina_un_servicio(): void
    {
        $admin = $this->admin();

        // Crear.
        $this->actingAs($admin)
            ->post(route('admin.servicios.store'), $this->datos())
            ->assertRedirect(route('admin.servicios.index'));
        $servicio = Service::where('nombre', 'Corte con lavado')->firstOrFail();

        // Editar: el formulario carga con los datos actuales y el cambio se guarda.
        $this->actingAs($admin)
            ->get(route('admin.servicios.edit', $servicio))
            ->assertOk()
            ->assertSee('Editar servicio')
            ->assertSee('value="Corte con lavado"', false);

        $this->actingAs($admin)
            ->put(route('admin.servicios.update', $servicio), $this->datos([
                'nombre' => 'Corte premium',
                'precio' => '120.50',
                'activo' => '0',
            ]))
            ->assertRedirect(route('admin.servicios.index'))
            ->assertSessionHas('success', 'Servicio actualizado');

        $this->assertDatabaseHas('servicios', [
            'id' => $servicio->id,
            'nombre' => 'Corte premium',
            'precio' => 120.50,
            'activo' => false,
        ]);

        // Eliminar.
        $this->actingAs($admin)
            ->delete(route('admin.servicios.destroy', $servicio))
            ->assertRedirect(route('admin.servicios.index'))
            ->assertSessionHas('success', 'Servicio eliminado');

        $this->assertDatabaseMissing('servicios', ['id' => $servicio->id]);
    }

    public function test_el_listado_muestra_estado_y_el_modal_de_borrado(): void
    {
        Service::factory()->create(['nombre' => 'Servicio visible', 'precio' => 45]);
        Service::factory()->inactivo()->create(['nombre' => 'Servicio apagado']);

        $this->actingAs($this->admin())
            ->get(route('admin.servicios.index'))
            ->assertOk()
            ->assertViewIs('admin.servicios.index')
            ->assertSee('Servicio visible')
            ->assertSee('$45.00')
            ->assertSee('Activo')
            ->assertSee('Inactivo')
            ->assertSee('id="modalEliminar"', false)
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_el_cliente_recibe_403_en_todo_el_crud(): void
    {
        $cliente = User::factory()->create();
        $servicio = Service::factory()->create();

        $this->actingAs($cliente)->get(route('admin.servicios.create'))->assertForbidden();
        $this->actingAs($cliente)->post(route('admin.servicios.store'), $this->datos())->assertForbidden();
        $this->actingAs($cliente)->get(route('admin.servicios.edit', $servicio))->assertForbidden();
        $this->actingAs($cliente)->put(route('admin.servicios.update', $servicio), $this->datos())->assertForbidden();
        $this->actingAs($cliente)->delete(route('admin.servicios.destroy', $servicio))->assertForbidden();

        // Nada cambió.
        $this->assertSame(1, Service::count());
        $this->assertDatabaseMissing('servicios', ['nombre' => 'Corte con lavado']);
    }

    public function test_la_paginacion_muestra_10_por_pagina(): void
    {
        // Nombres con prefijo numérico para conocer el orden alfabético.
        foreach (range(1, 12) as $i) {
            Service::factory()->create(['nombre' => sprintf('Servicio %02d', $i)]);
        }
        $admin = $this->admin();

        $pagina1 = $this->actingAs($admin)->get(route('admin.servicios.index'))->assertOk();
        $this->assertCount(10, $pagina1->viewData('servicios'));
        $pagina1->assertSee('Servicio 10')->assertDontSee('Servicio 11');

        $pagina2 = $this->actingAs($admin)->get(route('admin.servicios.index', ['page' => 2]))->assertOk();
        $this->assertCount(2, $pagina2->viewData('servicios'));
        $pagina2->assertSee('Servicio 11')->assertSee('Servicio 12');
    }
}
