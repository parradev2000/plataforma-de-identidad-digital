<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class person extends Model{

    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nomencladores.nom_persons';

    protected $fillable = [
        'names',
        'identity',
        'personal_profile_id',
        'professional_profile_id',
        'work_profile_id',
        'state',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function personal_profile()
    {
        return $this->belongsTo(personal_profile::class);
    }

    public function professional_profile()
    {
        return $this->belongsTo(professional_profile::class);
    }

    public function work_profile()
    {
        return $this->belongsTo(work_profile::class);
    }

    public function social_networks()
    {
        return $this->belongsToMany(social_network::class, 'nomencladores.nom_social_network_person')->withPivot('user');
    }

    public function charges_anec()
    {
        return $this->belongsToMany(charge_anec::class,'nomencladores.nom_base_delegation_charges_anec_person');
    }

    public function base_delegation()
    {
        return $this->belongsToMany(base_delegation::class,'nomencladores.nom_base_delegation_charges_anec_person');
    }

    protected function names(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }
}