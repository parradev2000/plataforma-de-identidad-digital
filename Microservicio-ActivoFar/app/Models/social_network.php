<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class social_network extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_social_networks';

    protected $fillable = [
        'name',
    ];

    public function persons()
    {
        return $this->belongsToMany(person::class, 'nomencladores.nom_social_network_person');
    }

    protected $hidden = ['created_at', 'updated_at'];

}
