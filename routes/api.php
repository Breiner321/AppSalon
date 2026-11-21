<?php

/**
 * API REST de AppSalon (Entrega Final). Todas las rutas empiezan con /api y
 * responden JSON. La documentación con ejemplos está en docs/api.md.
 *
 * Autenticación: POST /api/login devuelve un token de Laravel Sanctum, que se
 * envía en las demás peticiones con la cabecera Authorization: Bearer <token>.
 */

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\ServicioController;
use Illuminate\Support\Facades\Route;

// Públicas.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1,api-login')->name('api.login');
Route::get('/servicios', [ServicioController::class, 'index'])->name('api.servicios.index');
Route::get('/servicios/{id}', [ServicioController::class, 'show'])->whereNumber('id')->name('api.servicios.show');

// Con token y cuenta activa. 60 peticiones por minuto por usuario.
Route::middleware(['auth:sanctum', 'activo', 'throttle:60,1'])->name('api.')->group(function () {
    Route::get('/usuario', [AuthController::class, 'yo'])->name('usuario');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/disponibilidad', [CitaController::class, 'disponibilidad'])->name('disponibilidad');

    Route::get('/citas/usuario/{usuario}', [CitaController::class, 'porUsuario'])->whereNumber('usuario')->name('citas.usuario');
    Route::get('/citas/{cita}', [CitaController::class, 'show'])->whereNumber('cita')->name('citas.show');
    Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
    Route::put('/citas/{cita}', [CitaController::class, 'update'])->whereNumber('cita')->name('citas.update');
    Route::patch('/citas/{cita}/cancelar', [CitaController::class, 'cancelar'])->whereNumber('cita')->name('citas.cancelar');
});
