<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class province extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_provinces';

    protected $fillable = [
        'name',
        'state'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function municipalities()
    {
        return $this->hasMany(municipality::class);
    }

    protected function name(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }
}
