<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class work_profile extends Model{

    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nomencladores.nom_work_profiles';

    protected $fillable = [
        'work_center',
        'phone',
        'mail',
        'charge_id',
        'year_id',
        'entity_id',
        'business_system_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function toJsonCustom()
    {
        return [
            'id' => $this->id,
            'work_center' => $this->work_center,
            'phone' => $this->phone,
            'mail' => $this->mail,
            'charge' => $this->charge,
            'year' => $this->year,
            'entity' => $this->entity,
            'business_system' => $this->business_system,
        ];
    }

    public function charge()
    {
        return $this->belongsTo(charge::class);
    }

    public function year()
    {
        return $this->belongsTo(year::class);
    }

    public function entity()
    {
        return $this->belongsTo(entity::class);
    }
    
    public function business_system()
    {
        return $this->belongsTo(business_system::class);
    }

    public function person()
    {
        return $this->hasOne(person::class);
    }

}