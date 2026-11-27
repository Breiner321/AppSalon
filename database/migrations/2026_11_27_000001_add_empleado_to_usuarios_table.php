<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna empleado a usuarios (Entrega Final, rol empleado).
     *
     * El rol sale de dos columnas: admin = 1 es administrador, empleado = 1 es
     * empleado y las dos en 0 es cliente. Se conserva admin para no romper las
     * consultas ni el script SQL de las entregas anteriores.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('empleado')->default(false)->after('admin');
        });
    }

    /**
     * Quita la columna empleado.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('empleado');
        });
    }
};
