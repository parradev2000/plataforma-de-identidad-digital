<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AcademicLevel extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_academic_levels';

    protected $fillable = [
        'description',
        'state'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    /*public function users()
    {
        return $this->hasMany(User::class);
    }

    protected function rol(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }*/
}
