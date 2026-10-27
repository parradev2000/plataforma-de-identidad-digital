<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class municipality extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_municipalities';

    protected $fillable = [
        'name',
        'province_id',
        'state'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function province()
    {
        return $this->belongsTo(province::class);
    }

    public function toJsonCustom()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'province' => $this->province,
        ];
    }

    protected function name(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }
}
