<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Usuario del sistema (cliente o administrador), tabla usuarios.
 *
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string|null $telefono
 * @property bool $admin
 * @property bool $confirmado
 * @property bool $activo Si es false no puede iniciar sesión ni usar la API.
 * @property string|null $token
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * La tabla usuarios no tiene columna remember_token (contrato del equipo).
     * Con el nombre vacío Laravel no intenta leerla ni escribirla.
     */
    protected $rememberTokenName = '';

    /**
     * Atributos asignables en masa. admin, confirmado, activo y token quedan fuera
     * a propósito para que un formulario no pueda modificarlos.
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
     * Indica si la cuenta está activa. Un administrador puede desactivarla
     * desde /admin/usuarios; la columna vale true por defecto.
     */
    public function isActivo(): bool
    {
        return $this->activo !== false;
    }

    /**
     * Nombre y apellido.
     */
    public function nombreCompleto(): string
    {
        return trim($this->nombre.' '.$this->apellido);
    }
}
