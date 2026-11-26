<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guarda en citasServicios el precio que tenía el servicio al reservar.
     *
     * Así el reporte de ingresos por servicio no cambia si después se edita el
     * precio del servicio. Las filas que ya existían toman el precio actual.
     */
    public function up(): void
    {
        Schema::table('citasServicios', function (Blueprint $table) {
            $table->decimal('precio', 5, 2)->nullable()->after('servicioId');
        });

        DB::table('citasServicios')
            ->join('servicios', 'servicios.id', '=', 'citasServicios.servicioId')
            ->whereNull('citasServicios.precio')
            ->update(['citasServicios.precio' => DB::raw('servicios.precio')]);
    }

    /**
     * Quita la columna precio.
     */
    public function down(): void
    {
        Schema::table('citasServicios', function (Blueprint $table) {
            $table->dropColumn('precio');
        });
    }
};
