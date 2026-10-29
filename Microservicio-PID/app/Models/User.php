<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Persona;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'autenticacion.users';

    protected $fillable = [
        'usuario',
        'persona_id',
        'rol_id',
        'estado',
        'int',
        'valid',
        'password',
    ];

    protected $hidden = [
        'password_expiry',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    public function rol()
    {
        return $this->belongsTo(rol::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    /**
     * Orden de listado por defecto: activos primero, luego por el nombre de la persona.
     *
     * La columna usuarios.nombres fue eliminada del esquema, por lo que el orden
     * se resuelve contra la persona relacionada.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderByDesc('estado')
            ->orderByRaw(
                '(SELECT p.nombres FROM nomencladores.personas p WHERE p.id = '.$query->getModel()->getTable().'.persona_id) ASC'
            )
            ->orderByDesc('id');
    }

    protected function usuario(): Attribute
    {
        return new Attribute(
            get: function($value){
                return strtolower($value);
            },
            set: function($value){
                return strtolower($value);
            }
        );
    }

        //Devuelve la fecha del mes siguiente
        public function password_expire()
        {
            $fechaActual = now(); // Crear objeto DateTime con la fecha actual
            $fechaActual->modify('+3 month'); // Sumar un mes al objeto DateTime
            return $fechaActual->format('Y-m-d H:i:s');
        }

        //Comprueba si la contraseña expiró
        public function passwordHasExpired()
        {
            return now() > $this->password_expiry;
        }
}
