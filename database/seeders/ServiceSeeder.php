<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Catálogo inicial de servicios del salón.
 */
class ServiceSeeder extends Seeder
{
    /**
     * Crea o actualiza los servicios por su nombre.
     *
     * La columna precio es DECIMAL(5,2), así que todos los precios son menores a 1000.
     * Pendiente: confirmar con el profesor la unidad del precio (pesos, miles de pesos, dólares…).
     */
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Corte de cabello mujer', 'precio' => 35.00, 'duracion' => 45,
                'descripcion' => 'Corte a la medida con lavado y secado.'],
            ['nombre' => 'Corte de cabello hombre', 'precio' => 25.00, 'duracion' => 30,
                'descripcion' => 'Corte clásico o moderno con acabado a máquina y tijera.'],
            ['nombre' => 'Corte infantil', 'precio' => 20.00, 'duracion' => 30,
                'descripcion' => 'Corte para niños y niñas de hasta 12 años.'],
            ['nombre' => 'Tinte completo', 'precio' => 120.00, 'duracion' => 120,
                'descripcion' => 'Coloración de raíz a puntas con productos sin amoniaco.'],
            ['nombre' => 'Mechas o balayage', 'precio' => 180.00, 'duracion' => 150,
                'descripcion' => 'Iluminación del cabello con técnica de mechas o balayage.'],
            ['nombre' => 'Peinado para evento', 'precio' => 60.00, 'duracion' => 60,
                'descripcion' => 'Recogido o peinado suelto para bodas, grados y fiestas.'],
            ['nombre' => 'Manicure tradicional', 'precio' => 22.00, 'duracion' => 40,
                'descripcion' => 'Limpieza, limado y esmaltado de uñas de las manos.'],
            ['nombre' => 'Pedicure spa', 'precio' => 40.00, 'duracion' => 60,
                'descripcion' => 'Exfoliación, hidratación y esmaltado de uñas de los pies.'],
            ['nombre' => 'Tratamiento de keratina', 'precio' => 250.00, 'duracion' => 180,
                'descripcion' => 'Alisado y nutrición profunda que reduce el frizz.'],
            ['nombre' => 'Diseño de cejas', 'precio' => 18.00, 'duracion' => 20,
                'descripcion' => 'Depilación y perfilado de cejas con cera o pinza.'],
        ];

        foreach ($servicios as $datos) {
            Service::updateOrCreate(['nombre' => $datos['nombre']], $datos + ['activo' => true]);
        }
    }
}
