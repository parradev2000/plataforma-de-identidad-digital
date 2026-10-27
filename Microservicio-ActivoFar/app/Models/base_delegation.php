<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class base_delegation extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_base_delegation';

    protected $fillable = [
        'name',
        'description',
        'father',
        'state',
    ];

    protected $appends = ['sub'];
    
    
    protected $hidden = ['created_at', 'updated_at'];

    public function getSubAttribute()
    {
        return $this->where('father', $this->id)->get();
    }

    public function persons()
    {
        return $this->belongsToMany(person::class,'nomencladores.nom_base_delegation_charges_anec_person');
    }

    public function toJsonCustom()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
