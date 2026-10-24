<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Pruebas de los componentes Blade de mensajes (U3).
 */
class ComponentesUiTest extends TestCase
{
    /**
     * <x-alert /> pinta cada mensaje flash con su clase Bootstrap y escapa el HTML.
     */
    public function test_alert_muestra_los_mensajes_flash(): void
    {
        session()->flash('success', 'Guardado');
        session()->flash('error', '<b>Falló</b>');

        $vista = $this->blade('<x-alert />');

        $vista->assertSee('alert-success', false)
            ->assertSee('Guardado')
            ->assertSee('alert-danger', false)
            ->assertSee('&lt;b&gt;Falló&lt;/b&gt;', false)
            ->assertDontSee('alert-warning', false);
    }

    /**
     * <x-form-error /> muestra el primer error del campo dentro de invalid-feedback.
     */
    public function test_form_error_muestra_el_error_del_campo(): void
    {
        // En una petición real, ShareErrorsFromSession comparte $errors con todas las vistas.
        View::share('errors', (new ViewErrorBag)->put('default', new MessageBag([
            'email' => ['El correo electrónico es obligatorio.'],
        ])));

        $this->blade('<x-form-error field="email" />')
            ->assertSee('invalid-feedback', false)
            ->assertSee('El correo electrónico es obligatorio.');

        $this->blade('<x-form-error field="nombre" />')
            ->assertDontSee('invalid-feedback', false);
    }
}
