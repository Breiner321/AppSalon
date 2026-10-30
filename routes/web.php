<?php

/**
 * Contrato de rutas web de AppSalon (tarea A1).
 *
 * Los nombres de ruta son parte del contrato del equipo: las vistas y los
 * controladores los usan con route('...'), así que no deben cambiarse.
 */

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// Catálogo público de servicios. La raíz muestra el mismo catálogo.
Route::get('/', [ServiceController::class, 'catalog'])->name('inicio');
Route::get('/servicios', [ServiceController::class, 'catalog'])->name('servicios.catalogo');

// Registro e inicio de sesión: solo para visitantes sin sesión.
// Los POST admiten 5 intentos por minuto y por IP; el sexto responde 429.
// El tercer parámetro de throttle separa los contadores de login y de registro.
Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('registro');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:5,1,registro');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1,login');
});

// Rutas que requieren sesión iniciada.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Panel de administración: sesión iniciada y usuario admin (si no, 403).
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('servicios', ServiceController::class)->except('show');
    });
});
