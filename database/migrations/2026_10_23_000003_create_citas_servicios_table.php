<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla pivote citasServicios (qué servicios incluye cada cita).
     * Las filas se borran en cascada con la cita o con el servicio.
     */
    public function up(): void
    {
        Schema::create('citasServicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citaId')->constrained('citas')->cascadeOnDelete();
            $table->foreignId('servicioId')->constrained('servicios')->cascadeOnDelete();
        });
    }

    /**
     * Elimina la tabla citasServicios.
     */
    public function down(): void
    {
        Schema::dropIfExists('citasServicios');
    }
};
