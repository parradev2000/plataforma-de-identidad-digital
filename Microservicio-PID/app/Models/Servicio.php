<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.servicios';

    protected $fillable = [
        'nombre',
    ];

    protected $hidden = ['created_at', 'updated_at'];
}