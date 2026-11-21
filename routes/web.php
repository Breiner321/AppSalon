<?php

/**
 * Contrato de rutas web de AppSalon (tarea A1, ampliado en la Entrega Final).
 *
 * Los nombres de ruta son parte del contrato del equipo: las vistas y los
 * controladores los usan con route('...'), así que no deben cambiarse.
 * Las rutas de la API están en routes/api.php.
 */

use App\Http\Controllers\Admin\CitaController as AdminCitaController;
use App\Http\Controllers\Admin\HorarioController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
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

    // Enlace del correo de confirmación de cuenta (A7).
    Route::get('/confirmar/{token}', [AuthController::class, 'confirmar'])->name('confirmar');
});

// Rutas que requieren sesión iniciada y cuenta activa.
Route::middleware(['auth', 'activo'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->withoutMiddleware('activo');

    // Citas del cliente. {cita} se resuelve por id (route model binding) y
    // AppointmentPolicy revisa que sea del usuario.
    Route::get('/citas', [AppointmentController::class, 'index'])->name('citas.index');
    Route::get('/citas/nueva', [AppointmentController::class, 'create'])->name('citas.create');
    Route::get('/citas/disponibilidad', [AppointmentController::class, 'disponibilidad'])->name('citas.disponibilidad');
    Route::post('/citas', [AppointmentController::class, 'store'])->name('citas.store')->middleware('throttle:10,1,citas');
    Route::get('/citas/{cita}/editar', [AppointmentController::class, 'edit'])->name('citas.edit');
    Route::put('/citas/{cita}', [AppointmentController::class, 'update'])->name('citas.update');
    Route::patch('/citas/{cita}/cancelar', [AppointmentController::class, 'cancelar'])->name('citas.cancelar');
});

// Panel de administración: sesión iniciada, cuenta activa y usuario admin (si no, 403).
Route::middleware(['auth', 'activo', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('servicios', ServiceController::class)->except('show');

        // Citas: agenda del día o la semana, listado con filtros y cambio de estado.
        Route::get('/agenda', [AdminCitaController::class, 'agenda'])->name('agenda');
        Route::get('/citas', [AdminCitaController::class, 'index'])->name('citas.index');
        Route::patch('/citas/{cita}/estado', [AdminCitaController::class, 'cambiarEstado'])->name('citas.estado');

        // Usuarios: búsqueda, filtros, activar o desactivar y cambiar el rol.
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::patch('/usuarios/{usuario}/activo', [UsuarioController::class, 'cambiarActivo'])->name('usuarios.activo');
        Route::patch('/usuarios/{usuario}/rol', [UsuarioController::class, 'cambiarRol'])->name('usuarios.rol');

        // Disponibilidad horaria: horario semanal y fechas bloqueadas.
        Route::get('/horarios', [HorarioController::class, 'index'])->name('horarios.index');
        Route::put('/horarios', [HorarioController::class, 'update'])->name('horarios.update');
        Route::post('/bloqueos', [HorarioController::class, 'storeBloqueo'])->name('bloqueos.store');
        Route::delete('/bloqueos/{bloqueo}', [HorarioController::class, 'destroyBloqueo'])->name('bloqueos.destroy');
    });
});
