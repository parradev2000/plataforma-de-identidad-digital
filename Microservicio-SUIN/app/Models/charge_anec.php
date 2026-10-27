<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class charge_anec extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_charges_anec';

    protected $fillable = [
        'name',
        'state',
        'description',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected function name(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }
}
