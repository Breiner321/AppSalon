<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Pruebas de las páginas de error propias (T5).
 */
class ErroresTest extends TestCase
{
    /**
     * Una ruta inexistente responde 404 con la vista errors/404 y el botón al catálogo.
     *
     * El manejador de excepciones devuelve la vista ya renderizada, así que no se
     * puede usar assertViewIs: se comprueba su texto y que no sea la página de Laravel.
     */
    public function test_una_ruta_inexistente_muestra_la_vista_404_propia(): void
    {
        $this->get('/esta-ruta-no-existe')
            ->assertNotFound()
            ->assertSee('No encontramos esta página')
            ->assertSee(route('servicios.catalogo'), false)
            ->assertDontSee('Not Found');
    }
}
