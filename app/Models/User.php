<?php

namespace App\Models;

use App\Mail\RestablecerContrasena;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

/**
 * Usuario del sistema, tabla usuarios. Tiene uno de tres roles:
 * administrador (admin = 1), empleado (empleado = 1) o cliente (los dos en 0).
 *
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string|null $telefono
 * @property bool $admin
 * @property bool $empleado
 * @property bool $confirmado
 * @property bool $activo Si es false no puede iniciar sesión ni usar la API.
 * @property string|null $token
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Roles que el administrador puede asignar, con su nombre para mostrar.
     */
    public const ROLES = [
        'cliente' => 'Cliente',
        'empleado' => 'Empleado',
        'admin' => 'Administrador',
    ];

    protected $table = 'usuarios';

    /**
     * La tabla usuarios no tiene columna remember_token (contrato del equipo).
     * Con el nombre vacío Laravel no intenta leerla ni escribirla.
     */
    protected $rememberTokenName = '';

    /**
     * Atributos asignables en masa. admin, empleado, confirmado, activo y token
     * quedan fuera a propósito para que un formulario no pueda modificarlos.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'telefono',
    ];

    /**
     * Atributos ocultos al serializar.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'token',
    ];

    /**
     * Conversiones de tipo. 'hashed' aplica bcrypt al asignar la contraseña.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'admin' => 'boolean',
            'empleado' => 'boolean',
            'confirmado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Citas reservadas por el usuario.
     *
     * @return HasMany<Appointment, $this>
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Appointment::class, 'usuarioId');
    }

    /**
     * Indica si el usuario tiene permisos de administrador.
     */
    public function isAdmin(): bool
    {
        return $this->admin === true;
    }

    /**
     * Indica si el usuario es empleado del salón.
     */
    public function isEmpleado(): bool
    {
        return $this->empleado === true && ! $this->isAdmin();
    }

    /**
     * Indica si el usuario es personal del salón (administrador o empleado):
     * puede ver el panel, la agenda y las citas de todos los clientes.
     */
    public function esPersonal(): bool
    {
        return $this->isAdmin() || $this->isEmpleado();
    }

    /**
     * Rol del usuario: 'admin', 'empleado' o 'cliente' (claves de ROLES).
     */
    public function rol(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin',
            $this->isEmpleado() => 'empleado',
            default => 'cliente',
        };
    }

    /**
     * Asigna uno de los roles de ROLES ajustando las columnas admin y empleado.
     * No guarda: quien la llama decide cuándo hacer save().
     */
    public function asignarRol(string $rol): static
    {
        return $this->forceFill([
            'admin' => $rol === 'admin',
            'empleado' => $rol === 'empleado',
        ]);
    }

    /**
     * Solo los clientes (ni administradores ni empleados).
     *
     * @param  Builder<User>  $query
     */
    public function scopeClientes(Builder $query): void
    {
        $query->where('admin', false)->where('empleado', false);
    }

    /**
     * Indica si la cuenta está activa. Un administrador puede desactivarla
     * desde /admin/usuarios; la columna vale true por defecto.
     */
    public function isActivo(): bool
    {
        return $this->activo !== false;
    }

    /**
     * Envía el correo en español con el enlace para restablecer la contraseña
     * (lo llama Password::sendResetLink en PasswordResetController).
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        Mail::to($this)->send(new RestablecerContrasena($this, $token));
    }

    /**
     * Nombre y apellido.
     */
    public function nombreCompleto(): string
    {
        return trim($this->nombre.' '.$this->apellido);
    }
}
