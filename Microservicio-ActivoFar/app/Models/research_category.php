<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class research_category extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_research_categories';

    protected $fillable = [
        'name',
        'state'
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
