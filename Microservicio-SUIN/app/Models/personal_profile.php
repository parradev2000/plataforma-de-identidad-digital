<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class personal_profile extends Model{

    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nomencladores.nom_personal_profiles';

    protected $fillable = [
        'address',
        'phone',
        'mail',
        'municipality_id',
        'militancy_id',
    ];

    protected $appends = ['sex', 'age'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function toJsonCustom()
    {
        return [
            'id' => $this->id,
            'address' => $this->address,
            'phone' => $this->phone,
            'mail' => $this->mail,
            'municipality' => $this->municipality->toJsonCustom(),
            'age' => $this->age,
            'sex' => $this->sex,
            'militancy' => $this->militancy,
        ];
    }

    public function getSexAttribute()
    {
        $digit_sex = $this->person->identity[9];
        if ($digit_sex % 2 == 0) {
            $sex = 'Masculino';
        }else {
            $sex = 'Femenino';
        }
        return $sex;
    }

    public function getAgeAttribute()
    {
        $digit_siglo = $this->person->identity[6];
        $digit_year = substr($this->person->identity, 0, 2);
        $digit_month = substr($this->person->identity, 2, 2);
        $digit_day = substr($this->person->identity, 4, 2);
        if ($digit_siglo <= 8  && $digit_siglo >= 6) {
            $year = '20'. $digit_year;
        }elseif ($digit_siglo <= 5  && $digit_siglo >= 0) {
            $year = '19'. $digit_year;
        }else {
            $year = '18'. $digit_year;
        }

        $year_now = date('Y');
        $month_now = date('m');
        $day_now = date('d');
        $age = $year_now - $year;

        if ( $month_now < $digit_month && $digit_day < $day_now) {
            $age = $age - 1 ;
        }
        return $age;
    }

    public function municipality()
    {
        return $this->belongsTo(municipality::class);
    }

    public function age()
    {
        return $this->belongsTo(age::class);
    }

    public function sex()
    {
        return $this->belongsTo(sex::class);
    }
    
    public function person()
    {
        return $this->hasOne(person::class);
    }

    public function militancy()
    {
        return $this->belongsTo(militancy::class);
    }
}