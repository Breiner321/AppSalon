<?php

namespace Tests\Feature;

use App\Mail\CitaNotificacion;
use App\Models\Appointment;
use App\Models\Bloqueo;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\PreparaAgenda;
use Tests\TestCase;

/**
 * Reserva de citas desde la web (requerimiento 6): calendario, varios
 * servicios, validación de disponibilidad, horario del salón y correo.
 * "Hoy" es el lunes 2026-12-07 a las 08:00 (ver PreparaAgenda).
 */
class CitaReservaTest extends TestCase
{
    use PreparaAgenda, RefreshDatabase;

    private User $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararAgenda();
        $this->cliente = User::factory()->create();
        Mail::fake();
    }

    public function test_un_invitado_no_puede_reservar(): void
    {
        $this->get(route('citas.create'))->assertRedirect(route('login'));
        $this->post(route('citas.store'))->assertRedirect(route('login'));
    }

    public function test_el_formulario_muestra_el_calendario_y_los_servicios_activos(): void
    {
        $activo = $this->servicio();
        $inactivo = Service::factory()->inactivo()->create();

        $this->actingAs($this->cliente)->get(route('citas.create'))
            ->assertOk()
            ->assertSee('id="calendario"', false)
            ->assertSee('data-calendario=', false)
            ->assertSee($activo->nombre)
            ->assertDontSee($inactivo->nombre);
    }

    public function test_reserva_una_cita_con_varios_servicios_y_envia_el_correo(): void
    {
        $corte = $this->servicio(45, 35);
        $manicure = $this->servicio(40, 22);

        $this->actingAs($this->cliente)->post(route('citas.store'), [
            'fecha' => '2026-12-08',
            'hora' => '10:00',
            'servicios' => [$corte->id, $manicure->id],
        ])->assertRedirect(route('citas.index'))->assertSessionHas('success');

        $cita = Appointment::sole();
        $this->assertSame($this->cliente->id, $cita->usuarioId);
        $this->assertSame('2026-12-08', $cita->fecha->toDateString());
        $this->assertSame('10:00:00', $cita->hora);
        $this->assertSame(85, $cita->duracion);
        $this->assertSame('57.00', $cita->total);
        $this->assertSame('pendiente', $cita->estado);
        $this->assertEqualsCanonicalizing([$corte->id, $manicure->id], $cita->servicios->pluck('id')->all());

        Mail::assertSent(CitaNotificacion::class, fn ($m) => $m->hasTo($this->cliente->email) && $m->tipo === 'reservada');
    }

    public function test_no_permite_citas_que_se_sobreponen(): void
    {
        $this->citaExistente('2026-12-08', '10:00:00', 60);
        $servicio = $this->servicio(30);

        $this->actingAs($this->cliente)->from(route('citas.create'))->post(route('citas.store'), [
            'fecha' => '2026-12-08', 'hora' => '10:30', 'servicios' => [$servicio->id],
        ])->assertRedirect(route('citas.create'))->assertSessionHasErrors(['hora' => 'Ese horario ya está ocupado. Elige otra hora.']);

        // Termina justo cuando empieza la otra (09:30 + 30 min = 10:00): sí se puede.
        $this->actingAs($this->cliente)->post(route('citas.store'), [
            'fecha' => '2026-12-08', 'hora' => '09:30', 'servicios' => [$servicio->id],
        ])->assertSessionHasNoErrors();

        // Una cita larga que empieza antes y termina dentro de la existente: no.
        $largo = $this->servicio(90);
        $this->actingAs($this->cliente)->post(route('citas.store'), [
            'fecha' => '2026-12-08', 'hora' => '09:00', 'servicios' => [$largo->id],
        ])->assertSessionHasErrors('hora');

        $this->assertSame(2, Appointment::count());
    }

    public function test_una_cita_cancelada_libera_su_horario(): void
    {
        $this->citaExistente('2026-12-08', '10:00:00', 60, null, 'cancelada');
        $servicio = $this->servicio(60);

        $this->actingAs($this->cliente)->post(route('citas.store'), [
            'fecha' => '2026-12-08', 'hora' => '10:00', 'servicios' => [$servicio->id],
        ])->assertSessionHasNoErrors();
    }

    public function test_respeta_el_horario_del_salon(): void
    {
        $servicio = $this->servicio(60);
        $reservar = fn (array $datos) => $this->actingAs($this->cliente)->post(route('citas.store'), $datos + ['servicios' => [$servicio->id]]);

        // Antes de abrir y terminando después del cierre.
        $reservar(['fecha' => '2026-12-08', 'hora' => '08:00'])->assertSessionHasErrors('hora');
        $reservar(['fecha' => '2026-12-08', 'hora' => '17:30'])->assertSessionHasErrors('hora');
        // Domingo cerrado.
        $reservar(['fecha' => '2026-12-13', 'hora' => '10:00'])->assertSessionHasErrors(['hora' => 'El salón no atiende los domingo.']);
        // Fecha pasada y fecha demasiado lejana.
        $reservar(['fecha' => '2026-12-06', 'hora' => '10:00'])->assertSessionHasErrors('fecha');
        $reservar(['fecha' => '2027-06-01', 'hora' => '10:00'])->assertSessionHasErrors('fecha');
        // Son las 08:15: una cita hoy a las 09:00 no cumple la anticipación de 60 minutos; a las 09:30 sí.
        Carbon::setTestNow('2026-12-07 08:15:00');
        $reservar(['fecha' => '2026-12-07', 'hora' => '09:00'])->assertSessionHasErrors(['hora' => 'La cita debe reservarse con al menos 60 minutos de anticipación.']);
        $reservar(['fecha' => '2026-12-07', 'hora' => '09:30'])->assertSessionHasNoErrors();

        $this->assertSame(1, Appointment::count());
    }

    public function test_no_permite_reservar_en_una_fecha_bloqueada(): void
    {
        Bloqueo::create(['fecha' => '2026-12-08', 'motivo' => 'Festivo']);
        $servicio = $this->servicio();

        $this->actingAs($this->cliente)->post(route('citas.store'), [
            'fecha' => '2026-12-08', 'hora' => '10:00', 'servicios' => [$servicio->id],
        ])->assertSessionHasErrors(['hora' => 'El salón no atiende ese día (Festivo).']);
    }

    public function test_valida_los_servicios(): void
    {
        $inactivo = Service::factory()->inactivo()->create();
        $activo = $this->servicio();
        $reservar = fn (array $servicios) => $this->actingAs($this->cliente)
            ->post(route('citas.store'), ['fecha' => '2026-12-08', 'hora' => '10:00', 'servicios' => $servicios]);

        $reservar([])->assertSessionHasErrors('servicios');
        $reservar([$inactivo->id])->assertSessionHasErrors('servicios.0');
        $reservar([99999])->assertSessionHasErrors('servicios.0');
        $reservar([$activo->id, $activo->id])->assertSessionHasErrors('servicios.0');
        $this->assertSame(0, Appointment::count());
    }

    public function test_la_disponibilidad_devuelve_solo_las_horas_libres(): void
    {
        $this->citaExistente('2026-12-08', '10:00:00', 60);
        $servicio = $this->servicio(60);

        $respuesta = $this->actingAs($this->cliente)
            ->getJson(route('citas.disponibilidad', ['fecha' => '2026-12-08', 'servicios' => [$servicio->id]]))
            ->assertOk()
            ->assertJson(['abierto' => true, 'apertura' => '09:00', 'cierre' => '18:00', 'duracion' => 60]);

        $turnos = $respuesta->json('turnos');
        $this->assertContains('09:00', $turnos);
        $this->assertNotContains('09:30', $turnos); // terminaría 10:30, sobre la cita de las 10:00
        $this->assertNotContains('10:00', $turnos);
        $this->assertNotContains('10:30', $turnos);
        $this->assertContains('11:00', $turnos);
        $this->assertSame('17:00', end($turnos)); // la última que termina a las 18:00

        $this->actingAs($this->cliente)
            ->getJson(route('citas.disponibilidad', ['fecha' => '2026-12-13', 'servicios' => [$servicio->id]]))
            ->assertJson(['abierto' => false, 'turnos' => []]);
    }

    public function test_mis_citas_muestra_solo_las_propias(): void
    {
        $mia = $this->citaExistente('2026-12-09', '11:00:00', 60, $this->cliente);
        $ajena = $this->citaExistente('2026-12-10', '11:00:00', 60);

        $this->actingAs($this->cliente)->get(route('citas.index'))
            ->assertOk()
            ->assertSee('data-test="cita-'.$mia->id.'"', false)
            ->assertDontSee('data-test="cita-'.$ajena->id.'"', false);
    }

    public function test_modifica_su_cita_y_vuelve_a_quedar_pendiente(): void
    {
        $cita = $this->citaExistente('2026-12-09', '11:00:00', 60, $this->cliente, 'confirmada');
        $nuevo = $this->servicio(30, 20);

        $this->actingAs($this->cliente)->get(route('citas.edit', $cita))->assertOk()->assertSee('Modificar cita');

        // 11:30 se sobrepone con su propia cita (11:00 a 12:00), pero esa no cuenta.
        $this->actingAs($this->cliente)->put(route('citas.update', $cita), [
            'fecha' => '2026-12-09', 'hora' => '11:30', 'servicios' => [$nuevo->id],
        ])->assertRedirect(route('citas.index'))->assertSessionHasNoErrors();

        $cita->refresh();
        $this->assertSame('11:30:00', $cita->hora);
        $this->assertSame(30, $cita->duracion);
        $this->assertSame('20.00', $cita->total);
        $this->assertSame('pendiente', $cita->estado);
        $this->assertSame([$nuevo->id], $cita->servicios->pluck('id')->all());
        Mail::assertSent(CitaNotificacion::class, fn ($m) => $m->tipo === 'modificada');
    }

    public function test_no_puede_ver_ni_cambiar_citas_de_otro_cliente(): void
    {
        $ajena = $this->citaExistente('2026-12-09', '11:00:00');
        $servicio = $this->servicio();

        $this->actingAs($this->cliente)->get(route('citas.edit', $ajena))->assertForbidden();
        $this->actingAs($this->cliente)->put(route('citas.update', $ajena), [
            'fecha' => '2026-12-10', 'hora' => '10:00', 'servicios' => [$servicio->id],
        ])->assertForbidden();
        $this->actingAs($this->cliente)->patch(route('citas.cancelar', $ajena))->assertForbidden();

        $this->assertSame('pendiente', $ajena->refresh()->estado);
    }

    public function test_cancela_su_cita_y_recibe_el_correo(): void
    {
        $cita = $this->citaExistente('2026-12-09', '11:00:00', 60, $this->cliente);

        $this->actingAs($this->cliente)->patch(route('citas.cancelar', $cita))
            ->assertRedirect(route('citas.index'))
            ->assertSessionHas('success', 'Cita cancelada');

        $this->assertSame('cancelada', $cita->refresh()->estado);
        Mail::assertSent(CitaNotificacion::class, fn ($m) => $m->tipo === 'cancelada');
    }

    public function test_no_puede_modificar_ni_cancelar_una_cita_pasada_o_cancelada(): void
    {
        $pasada = $this->citaExistente('2026-12-01', '11:00:00', 60, $this->cliente, 'completada');
        $cancelada = $this->citaExistente('2026-12-09', '11:00:00', 60, $this->cliente, 'cancelada');

        foreach ([$pasada, $cancelada] as $cita) {
            $this->actingAs($this->cliente)->get(route('citas.edit', $cita))->assertRedirect(route('citas.index'));
            $this->actingAs($this->cliente)->patch(route('citas.cancelar', $cita))->assertSessionHas('warning');
        }

        $this->assertSame('completada', $pasada->refresh()->estado);
    }

    public function test_el_correo_de_la_cita_tiene_el_detalle(): void
    {
        $cita = $this->citaExistente('2026-12-09', '11:00:00', 60, $this->cliente);
        $correo = new CitaNotificacion($cita->load('servicios'), 'confirmada');

        $correo->assertHasSubject('Tu cita está confirmada · '.config('app.name'));
        $correo->assertSeeInHtml($cita->servicios->first()->nombre);
        $correo->assertSeeInHtml('11:00 a 12:00');
        $correo->assertSeeInHtml(route('citas.index'));
    }
}
