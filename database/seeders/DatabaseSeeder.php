<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Llena la base con los datos de prueba. El orden importa: las citas
     * necesitan a los clientes, los servicios y el horario del salón.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServiceSeeder::class,
            HorarioSeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}
