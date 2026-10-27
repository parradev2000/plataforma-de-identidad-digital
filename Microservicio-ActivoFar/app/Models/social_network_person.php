<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class social_network_person extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_social_network_person';

    protected $fillable = [
        'social_network_id',
        'person_id',
        'user'
    ];

    protected $hidden = ['created_at', 'updated_at'];

}
