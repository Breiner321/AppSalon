<?php

namespace Tests\Feature;

use App\Mail\CitaNotificacion;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\PreparaAgenda;
use Tests\TestCase;

/**
 * API REST (requerimiento 8): respuestas JSON, autenticación con Sanctum y
 * los endpoints de servicios y citas documentados en docs/api.md.
 * "Hoy" es el lunes 2026-12-07 (ver PreparaAgenda).
 */
class ApiTest extends TestCase
{
    use PreparaAgenda, RefreshDatabase;

    private User $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararAgenda();
        $this->cliente = User::factory()->create(['email' => 'laura@correo.com', 'password' => 'Cliente123']);
        Mail::fake();
    }

    public function test_login_devuelve_un_token_que_sirve_en_las_peticiones(): void
    {
        $respuesta = $this->postJson('/api/login', ['email' => 'laura@correo.com', 'password' => 'Cliente123'])
            ->assertOk()
            ->assertJsonStructure(['message', 'data' => ['token', 'tipo', 'expiraEl', 'usuario' => ['id', 'nombre', 'apellido', 'email', 'telefono', 'admin']]])
            ->assertJsonPath('data.tipo', 'Bearer')
            ->assertJsonPath('data.expiraEl', now()->addDay()->toIso8601String())
            ->assertJsonMissingPath('data.usuario.password');

        $token = $respuesta->json('data.token');

        $this->withToken($token)->getJson('/api/usuario')
            ->assertOk()
            ->assertJsonPath('data.email', 'laura@correo.com');
    }

    public function test_login_rechaza_credenciales_y_cuentas_no_validas(): void
    {
        $this->postJson('/api/login', ['email' => 'laura@correo.com', 'password' => 'mala'])
            ->assertUnauthorized()->assertExactJson(['message' => 'Credenciales incorrectas']);

        // Los acentos van sin escapar.
        $this->assertStringContainsString('Envía el token', $this->getJson('/api/usuario')->getContent());

        $this->postJson('/api/login', ['email' => 'no-es-correo'])
            ->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);

        User::factory()->unconfirmed()->create(['email' => 'nuevo@correo.com', 'password' => 'Cliente123']);
        $this->postJson('/api/login', ['email' => 'nuevo@correo.com', 'password' => 'Cliente123'])->assertForbidden();

        User::factory()->inactivo()->create(['email' => 'baja@correo.com', 'password' => 'Cliente123']);
        $this->postJson('/api/login', ['email' => 'baja@correo.com', 'password' => 'Cliente123'])->assertForbidden();
    }

    public function test_sin_token_responde_401_en_json(): void
    {
        foreach (['/api/usuario', '/api/citas/usuario/'.$this->cliente->id, '/api/citas/1'] as $url) {
            $this->get($url)->assertUnauthorized()->assertJsonPath('message', 'No autenticado. Envía el token en la cabecera Authorization: Bearer <token>.');
        }

        $this->post('/api/citas')->assertUnauthorized();
        $this->withToken('token-inventado')->getJson('/api/usuario')->assertUnauthorized();
    }

    public function test_logout_revoca_el_token(): void
    {
        $token = $this->cliente->createToken('pruebas')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertSame(0, $this->cliente->tokens()->count());

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/usuario')->assertUnauthorized();
    }

    public function test_get_servicios_es_publico_y_solo_muestra_los_activos(): void
    {
        $activo = Service::factory()->create(['nombre' => 'Corte', 'precio' => 35, 'duracion' => 45]);
        Service::factory()->inactivo()->create();

        $this->getJson('/api/servicios')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0', [
                'id' => $activo->id,
                'nombre' => 'Corte',
                'descripcion' => $activo->descripcion,
                'precio' => 35,
                'duracion' => 45,
                'activo' => true,
            ]);

        $this->getJson('/api/servicios/'.$activo->id)->assertOk()->assertJsonPath('data.nombre', 'Corte');
        $this->getJson('/api/servicios/99999')->assertNotFound()->assertExactJson(['message' => 'Recurso no encontrado.']);
    }

    public function test_citas_por_usuario_solo_las_propias_salvo_el_admin(): void
    {
        $mia = $this->citaExistente('2026-12-08', '10:00:00', 60, $this->cliente);
        $this->citaExistente('2026-12-09', '10:00:00', 60, $this->cliente, 'cancelada');
        $otro = User::factory()->create();
        $this->citaExistente('2026-12-10', '10:00:00', 60, $otro);

        Sanctum::actingAs($this->cliente);

        $this->getJson('/api/citas/usuario/'.$this->cliente->id)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data' => [['id', 'fecha', 'hora', 'horaFin', 'duracion', 'total', 'estado', 'modificable', 'usuarioId', 'servicios' => [['id', 'nombre', 'precio', 'duracion']]]]]);

        $this->getJson('/api/citas/usuario/'.$this->cliente->id.'?estado=pendiente')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mia->id);

        $this->getJson('/api/citas/usuario/'.$otro->id)->assertForbidden()->assertJsonPath('message', 'Solo puedes consultar tus propias citas.');
        $this->getJson('/api/citas/usuario/99999')->assertNotFound();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->getJson('/api/citas/usuario/'.$otro->id)->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_post_citas_reserva_y_valida(): void
    {
        $corte = $this->servicio(45, 35);
        $tinte = $this->servicio(120, 120);
        Sanctum::actingAs($this->cliente);

        $this->postJson('/api/citas', ['fecha' => '2026-12-08', 'hora' => '10:00:00', 'servicios' => [$corte->id, $tinte->id]])
            ->assertCreated()
            ->assertJsonPath('message', 'Cita reservada')
            ->assertJsonPath('data.fecha', '2026-12-08')
            ->assertJsonPath('data.hora', '10:00')
            ->assertJsonPath('data.horaFin', '12:45')
            ->assertJsonPath('data.duracion', 165)
            ->assertJsonPath('data.total', 155)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.usuarioId', $this->cliente->id)
            ->assertJsonCount(2, 'data.servicios');

        Mail::assertSent(CitaNotificacion::class);

        // Se sobrepone con la anterior.
        $this->postJson('/api/citas', ['fecha' => '2026-12-08', 'hora' => '12:00', 'servicios' => [$corte->id]])
            ->assertUnprocessable()
            ->assertJsonPath('errors.hora.0', 'Ese horario ya está ocupado. Elige otra hora.');

        // Datos con formato incorrecto.
        $this->postJson('/api/citas', ['fecha' => '08/12/2026', 'hora' => '10am', 'servicios' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fecha', 'hora', 'servicios']);

        $this->assertSame(1, Appointment::count());
    }

    public function test_put_citas_modifica_solo_las_propias(): void
    {
        $cita = $this->citaExistente('2026-12-08', '10:00:00', 60, $this->cliente, 'confirmada');
        $ajena = $this->citaExistente('2026-12-08', '15:00:00', 60);
        $servicio = $this->servicio(30, 20);
        Sanctum::actingAs($this->cliente);

        $this->putJson('/api/citas/'.$cita->id, ['fecha' => '2026-12-09', 'hora' => '11:00', 'servicios' => [$servicio->id]])
            ->assertOk()
            ->assertJsonPath('data.fecha', '2026-12-09')
            ->assertJsonPath('data.hora', '11:00')
            ->assertJsonPath('data.total', 20)
            ->assertJsonPath('data.estado', 'pendiente');

        $this->putJson('/api/citas/'.$ajena->id, ['fecha' => '2026-12-09', 'hora' => '14:00', 'servicios' => [$servicio->id]])
            ->assertForbidden();

        $this->putJson('/api/citas/99999', ['fecha' => '2026-12-09', 'hora' => '14:00', 'servicios' => [$servicio->id]])
            ->assertNotFound();

        $cita->update(['estado' => 'cancelada']);
        $this->putJson('/api/citas/'.$cita->id, ['fecha' => '2026-12-10', 'hora' => '11:00', 'servicios' => [$servicio->id]])
            ->assertStatus(409);
    }

    public function test_ver_y_cancelar_una_cita(): void
    {
        $cita = $this->citaExistente('2026-12-08', '10:00:00', 60, $this->cliente);
        Sanctum::actingAs($this->cliente);

        $this->getJson('/api/citas/'.$cita->id)->assertOk()->assertJsonPath('data.usuario.id', $this->cliente->id);

        $this->patchJson('/api/citas/'.$cita->id.'/cancelar')
            ->assertOk()
            ->assertJsonPath('data.estado', 'cancelada')
            ->assertJsonPath('data.modificable', false);

        $this->patchJson('/api/citas/'.$cita->id.'/cancelar')->assertStatus(409);
    }

    public function test_disponibilidad_por_api(): void
    {
        $this->citaExistente('2026-12-08', '09:00:00', 120);
        $servicio = $this->servicio(60);
        Sanctum::actingAs($this->cliente);

        $this->getJson('/api/disponibilidad?fecha=2026-12-08&servicios[]='.$servicio->id)
            ->assertOk()
            ->assertJsonPath('data.abierto', true)
            ->assertJsonPath('data.turnos.0', '11:00');

        $this->getJson('/api/disponibilidad')->assertUnprocessable()->assertJsonValidationErrors(['fecha', 'servicios']);
    }

    public function test_un_token_de_cuenta_desactivada_no_sirve(): void
    {
        $token = $this->cliente->createToken('movil')->plainTextToken;
        $this->cliente->forceFill(['activo' => false])->save();

        $this->withToken($token)->getJson('/api/usuario')
            ->assertForbidden()
            ->assertJsonPath('message', 'Tu cuenta está desactivada. Comunícate con el salón.');
    }

    public function test_metodo_no_permitido_responde_json(): void
    {
        $this->deleteJson('/api/servicios')->assertStatus(405)->assertJsonPath('message', 'Método HTTP no permitido en esta ruta.');
    }
}
