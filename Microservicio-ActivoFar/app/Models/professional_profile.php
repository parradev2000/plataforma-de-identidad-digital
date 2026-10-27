<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class professional_profile extends Model{

    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nomencladores.nom_professional_profiles';

    protected $fillable = [
        'professional_ability',
        'specialty',
        'scientific_postgraduate_specialty',
        'academic_postgraduate_specialty',
        'school_level_id',
        'academic_level_id',
        'teaching_category_id',
        'research_category_id',
        'scientific_grade_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function toJsonCustom()
    {
        return [
            'id' => $this->id,
            'specialty' => $this->specialty,
            'professional_ability' => $this->professional_ability,
            'scientific_postgraduate_specialty' => $this->scientific_postgraduate_specialty,
            'academic_postgraduate_specialty' => $this->academic_postgraduate_specialty,
            'school_level' => $this->school_level,
            'academic_level' => $this->academic_level,
            'teaching_category' => $this->teaching_category,
            'research_category' => $this->research_category,
            'scientific_grade' => $this->scientific_grade,
        ];
    }
    
    public function school_level()
    {
        return $this->belongsTo(school_level::class);
    }

    public function academic_level()
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function teaching_category()
    {
        return $this->belongsTo(teaching_category::class);
    }

    public function research_category()
    {
        return $this->belongsTo(research_category::class);
    }

    public function scientific_grade()
    {
        return $this->belongsTo(ScientificGrade::class);
    }

    public function person()
    {
        return $this->hasOne(person::class);
    }

}