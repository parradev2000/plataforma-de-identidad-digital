<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class militancy extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_militancies';

    protected $fillable = [
        'name',
        'state'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected function name (): Attribute
    {
        return new Attribute(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }
}
