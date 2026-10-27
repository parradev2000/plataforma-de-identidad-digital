<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Persona;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'datos.solicitudes';

    protected $fillable = [
        'persona_id',
        'estado_id',
        'servicio_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($solicitud) {
            if (empty($solicitud->estado_id)) {
                $estado = Estado::where('nombre', 'creada')->first();
                if ($estado) {
                    $solicitud->estado_id = $estado->id;
                }
            }
        });
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}