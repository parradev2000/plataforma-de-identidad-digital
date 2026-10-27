<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class base_delegation_charges_anec_person extends Model
{
    use HasFactory;

    protected $table = 'nomencladores.nom_base_delegation_charges_anec_person';

    protected $fillable = [
        'base_delegation_id',
        'charge_anec_id',
        'person_id',
    ];
    
    protected $hidden = ['created_at', 'updated_at'];

}
