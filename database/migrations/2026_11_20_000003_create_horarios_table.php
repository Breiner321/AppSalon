<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas de disponibilidad del salón:
     *
     * - horarios: una fila por día de la semana (0 = domingo … 6 = sábado) con
     *   la hora de apertura y de cierre. Si abierto = 0, ese día no hay citas.
     * - bloqueos: fechas puntuales en que el salón no atiende (festivos,
     *   vacaciones, capacitaciones).
     */
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('diaSemana')->unique();
            $table->time('apertura')->default('09:00:00');
            $table->time('cierre')->default('18:00:00');
            $table->boolean('abierto')->default(true);
        });

        Schema::create('bloqueos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->string('motivo', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Elimina las tablas de disponibilidad.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloqueos');
        Schema::dropIfExists('horarios');
    }
};
