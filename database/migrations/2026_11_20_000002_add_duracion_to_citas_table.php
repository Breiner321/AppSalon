<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega a citas la duración total en minutos (suma de sus servicios) y la
     * fecha de la última modificación.
     *
     * Se guarda la duración, en lugar de calcularla cada vez, para que la agenda
     * no cambie si después se edita la duración de un servicio. Con ella se
     * valida que dos citas no se sobrepongan. El índice (fecha, estado) acelera
     * esa consulta.
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->unsignedSmallInteger('duracion')->default(60)->after('hora');
            $table->timestamp('updated_at')->nullable()->after('created_at');
            $table->index(['fecha', 'estado']);
        });
    }

    /**
     * Quita las columnas agregadas.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex(['fecha', 'estado']);
            $table->dropColumn(['duracion', 'updated_at']);
        });
    }
};
