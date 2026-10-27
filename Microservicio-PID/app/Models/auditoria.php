<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria.dat_auditorias';

    protected $fillable = ['id',
                            'usuario',
                            'nombres',
                            'accion',
                            'ip'
                        ];

    protected $hidden = ['created_at', 'updated_at'];

    protected function accion(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucfirst($value),
            set: fn($value) => strtolower($value)
        );
    }
}
