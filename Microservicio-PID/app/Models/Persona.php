<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Solicitud;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.personas';

    protected $fillable = [
        'nombres',
        'apellidos',
        'ci',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'persona_id');
    }
}