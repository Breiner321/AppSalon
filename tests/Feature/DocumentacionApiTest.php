<?php

namespace Tests\Feature;

use Illuminate\Routing\Route as RutaLaravel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Documentación Swagger de la API (Entrega Final): la página carga Swagger UI
 * con la especificación OpenAPI y la especificación incluye todos los endpoints
 * de routes/api.php, para que la documentación no se quede atrás del código.
 */
class DocumentacionApiTest extends TestCase
{
    private function especificacion(): string
    {
        return file_get_contents(public_path('api-docs/openapi.yaml'));
    }

    public function test_la_pagina_de_documentacion_carga_swagger_ui(): void
    {
        $this->get('/api/documentacion')
            ->assertOk()
            ->assertSee('swagger-ui-bundle.js', false)
            ->assertSee(asset('api-docs/openapi.yaml'), false);
    }

    public function test_la_especificacion_es_openapi_y_documenta_servicios_y_citas(): void
    {
        $spec = $this->especificacion();

        $this->assertStringStartsWith('openapi: 3.', $spec);
        $this->assertStringContainsString('bearerAuth:', $spec);

        foreach (['/servicios:', '/citas:', '/citas/{cita}:', '/citas/usuario/{usuario}:'] as $ruta) {
            $this->assertStringContainsString("\n  ".$ruta."\n", $spec, "Falta documentar {$ruta}");
        }
    }

    public function test_todas_las_rutas_de_la_api_estan_documentadas(): void
    {
        $spec = $this->especificacion();

        $rutas = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RutaLaravel $r) => Str::startsWith($r->uri(), 'api/') && $r->uri() !== 'api/documentacion');

        $this->assertNotEmpty($rutas);

        foreach ($rutas as $ruta) {
            $path = Str::after($ruta->uri(), 'api');
            $this->assertStringContainsString("\n  ".$path.":\n", $spec, "Falta documentar {$path} en openapi.yaml");

            $metodo = strtolower(collect($ruta->methods())->reject(fn ($m) => $m === 'HEAD')->first());
            $bloque = Str::before(Str::after($spec, "\n  ".$path.":\n"), "\n  /");
            $this->assertStringContainsString("\n    ".$metodo.':', "\n".$bloque, "Falta el método {$metodo} de {$path}");
        }
    }
}
