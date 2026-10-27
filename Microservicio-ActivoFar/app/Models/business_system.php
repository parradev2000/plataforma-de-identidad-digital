<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class business_system extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_business_systems';

    protected $fillable = [
        'name',
        'state',
        'description'
    ];

    protected $hidden = ['created_at', 'updated_at'];
}
