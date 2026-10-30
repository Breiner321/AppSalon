<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla servicios: el catálogo de servicios del salón.
     * Solo registra created_at; no tiene updated_at.
     */
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->decimal('precio', 5, 2);
            $table->text('descripcion')->nullable();
            $table->integer('duracion')->default(60);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Elimina la tabla servicios.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
