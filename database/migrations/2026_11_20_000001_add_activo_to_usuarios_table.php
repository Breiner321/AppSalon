<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna activo a usuarios (Entrega Final, gestión de usuarios).
     * Un usuario desactivado no puede iniciar sesión ni usar la API.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('confirmado');
        });
    }

    /**
     * Quita la columna activo.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
