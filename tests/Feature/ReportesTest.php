<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PreparaAgenda;
use Tests\TestCase;

/**
 * Reportes y exportación (requerimiento 9): citas por período, ingresos por
 * servicio, gráficas, PDF y CSV. "Hoy" es el lunes 2026-12-07 (ver PreparaAgenda).
 */
class ReportesTest extends TestCase
{
    use PreparaAgenda, RefreshDatabase;

    private User $admin;

    private Service $tinte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararAgenda();
        $this->admin = User::factory()->admin()->create();

        // Diciembre: 2 completadas ($50 y $120), 1 cancelada y 1 pendiente. Noviembre: 1 completada.
        $corte = $this->servicio(60, 50);
        $tinte = $this->servicio(120, 120);
        $this->citaConServicios('2026-12-01', '10:00:00', [$corte], 'completada');
        $this->citaConServicios('2026-12-02', '10:00:00', [$corte, $tinte], 'completada');
        $this->citaConServicios('2026-12-03', '10:00:00', [$tinte], 'cancelada');
        $this->citaConServicios('2026-12-09', '10:00:00', [$corte], 'pendiente');
        $this->citaConServicios('2026-11-20', '10:00:00', [$tinte], 'completada');

        // Cambiar el precio después no altera el reporte: se usa el precio guardado en la cita.
        $corte->update(['precio' => 999]);
        $corte->update(['nombre' => 'Corte']);
        $tinte->update(['nombre' => 'Tinte']);
        $this->tinte = $tinte;
    }

    /**
     * Cita con varios servicios; el total es la suma de sus precios.
     */
    private function citaConServicios(string $fecha, string $hora, array $servicios, string $estado): Appointment
    {
        $cita = Appointment::create([
            'fecha' => $fecha, 'hora' => $hora,
            'duracion' => collect($servicios)->sum('duracion'),
            'usuarioId' => User::factory()->create()->id,
            'total' => collect($servicios)->sum('precio'),
            'estado' => $estado,
        ]);
        $cita->asignarServicios($servicios);

        return $cita;
    }

    private function periodo(): array
    {
        return ['desde' => '2026-12-01', 'hasta' => '2026-12-31'];
    }

    public function test_filtra_los_reportes_por_servicio(): void
    {
        // Diciembre con Tinte: la completada del 2 (Corte + Tinte) y la cancelada del 3.
        $filtro = $this->periodo() + ['servicio' => $this->tinte->id];

        $this->actingAs($this->admin)->get(route('admin.reportes.index', $filtro))
            ->assertOk()
            ->assertSee('data-test="servicio-filtrado">Tinte<', false)
            ->assertSee('data-test="citas-total">2<', false)
            ->assertSee('data-test="ingresos">$120.00<', false)       // solo lo cobrado por el Tinte
            ->assertSee('servicio='.$this->tinte->id, false);         // las exportaciones conservan el filtro

        $csv = $this->actingAs($this->admin)->get(route('admin.reportes.ingresos.csv', $filtro))->assertOk()->streamedContent();
        $this->assertStringContainsString('Tinte;1;120,00', $csv);
        $this->assertStringNotContainsString('Corte', $csv);

        $csvCitas = $this->actingAs($this->admin)->get(route('admin.reportes.citas.csv', $filtro))->streamedContent();
        $this->assertSame(3, substr_count(trim($csvCitas), "\n") + 1);   // encabezado + 2 citas

        $this->actingAs($this->admin)->get(route('admin.reportes.ingresos.pdf', $filtro))
            ->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->admin)->get(route('admin.reportes.index', $this->periodo() + ['servicio' => 99999]))
            ->assertSessionHasErrors('servicio');
    }

    public function test_solo_el_admin_ve_los_reportes(): void
    {
        $this->get(route('admin.reportes.index'))->assertRedirect(route('login'));

        $cliente = User::factory()->create();
        foreach (['index', 'citas.pdf', 'citas.csv', 'ingresos.pdf', 'ingresos.csv'] as $ruta) {
            $this->actingAs($cliente)->get(route('admin.reportes.'.$ruta))->assertForbidden();
        }
    }

    public function test_el_reporte_muestra_resumen_ingresos_por_servicio_y_graficas(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reportes.index', $this->periodo()))
            ->assertOk()
            ->assertSee('data-test="citas-total">4<', false)
            ->assertSee('data-test="ingresos">$220.00<', false)       // 50 + 170, solo completadas
            ->assertSee('data-test="ticket">$110.00<', false)
            ->assertSee('data-test="canceladas">1<', false)
            ->assertSee('25 % de las citas')
            ->assertSeeInOrder(['Tinte', '$120.00', 'Corte', '$100.00'])  // ordenado por ingresos
            ->assertSee('id="graficaCitas"', false)
            ->assertSee('id="graficaServicios"', false)
            ->assertSee('chart.js@4.4.1', false)
            ->assertSee('data-test="total-citas-reporte">4<', false);
    }

    public function test_sin_filtros_usa_el_mes_en_curso_y_valida_el_periodo(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reportes.index'))
            ->assertOk()
            ->assertSee('Del 1 de diciembre de 2026 al 31 de diciembre de 2026');

        $this->actingAs($this->admin)->get(route('admin.reportes.index', ['desde' => '2026-12-10', 'hasta' => '2026-12-01']))
            ->assertSessionHasErrors('hasta');
        $this->actingAs($this->admin)->get(route('admin.reportes.index', ['desde' => '2024-01-01', 'hasta' => '2026-12-01']))
            ->assertSessionHasErrors('hasta');
        $this->actingAs($this->admin)->get(route('admin.reportes.index', ['estado' => 'inventado']))
            ->assertSessionHasErrors('estado');
    }

    public function test_filtra_la_tabla_de_citas_por_estado(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reportes.index', $this->periodo() + ['estado' => 'completada']))
            ->assertSee('data-test="total-citas-reporte">2<', false);
    }

    public function test_exporta_las_citas_a_pdf(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.reportes.citas.pdf', $this->periodo()))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('reporte-citas_2026-12-01_2026-12-31.pdf');

        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_exporta_los_ingresos_a_pdf(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.reportes.ingresos.pdf', $this->periodo()))
            ->assertOk()
            ->assertDownload('reporte-ingresos_2026-12-01_2026-12-31.pdf');

        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_exporta_las_citas_a_csv_para_excel(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.reportes.citas.csv', $this->periodo() + ['estado' => 'completada']))
            ->assertOk()
            ->assertDownload('reporte-citas_2026-12-01_2026-12-31.csv');

        $csv = $respuesta->streamedContent();
        $lineas = array_values(array_filter(explode("\n", $csv)));

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);  // BOM: Excel respeta las tildes
        $this->assertStringContainsString('Id;Fecha;"Hora inicio"', $lineas[0]);
        $this->assertCount(3, $lineas);                      // encabezado + 2 completadas
        $this->assertStringContainsString(';01/12/2026;10:00;11:00;60;', $lineas[1]);
        $this->assertStringContainsString(';50,00;completada', $lineas[1]);
    }

    public function test_exporta_los_ingresos_por_servicio_a_csv(): void
    {
        $csv = $this->actingAs($this->admin)->get(route('admin.reportes.ingresos.csv', $this->periodo()))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Servicio;"Veces realizado";Ingresos;Porcentaje', $csv);
        $this->assertStringContainsString('Tinte;1;120,00;54,5', $csv);
        $this->assertStringContainsString('Corte;2;100,00;45,5', $csv);
        $this->assertStringContainsString('TOTAL;3;220,00;100,0', $csv);
    }

    public function test_el_csv_neutraliza_formulas(): void
    {
        User::query()->update(['nombre' => '=HYPERLINK("http://malo")']);

        $csv = $this->actingAs($this->admin)->get(route('admin.reportes.citas.csv', $this->periodo()))->streamedContent();

        $this->assertStringContainsString("\"'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(';"=HYPERLINK', $csv);
    }
}
