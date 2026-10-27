<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonStoreRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'names' => 'required|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_persons,names',
            'identity' => 'required|regex:/\d{11}/i|unique:pgsql.nomencladores.nom_persons,identity',
            'specialty' => 'nullable|string|max:255',
            'academic_postgraduate_specialty' => 'nullable|string|max:255',
            'scientific_postgraduate_specialty' => 'nullable|string|max:255',
            'professional_ability' => 'nullable|string|max:255',
            'work_center' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'work_phone' => 'nullable|string|max:8',
            'personal_phone' => 'nullable|string|max:8',
            'work_mail' => 'nullable|string|max:255|unique:pgsql.nomencladores.nom_work_profiles,mail',
            'private_mail' => 'nullable|string|max:255|unique:pgsql.nomencladores.nom_personal_profiles,mail',
            'municipality_id' => 'required|integer',
            'militancy_id' => 'required|integer',
            'year_entry_anec' => 'required|integer',
            'school_level_id' => 'required|integer',
            'academic_level_id' => 'required|integer',
            'scientific_grade_id' => 'required|integer',
            'teaching_category_id' => 'required|integer',
            'research_category_id' => 'required|integer',
            'entity_id' => 'required|integer',
            'business_system_id' => 'required|integer',
            'charge_id' => 'required|integer',
            'social_networks' => 'nullable',
        ];
    }

    // public function attributes()
    // {
    //     return [
    //         'names' => '[name of the names]',
    //         'identity' => '[name of the identity]',
    //         'especiality' => '[name of the especiality]',
    //         'academic_postgraduate_specialty' => '[name of the academic_postgraduate_specialty]',
    //         'scientific_postgraduate_specialty' => '[name of the scientific_postgraduate_specialty]',
    //         'professional_ability' => '[name of the professional_ability]',
    //         'work_center' => '[name of work_center]',
    //         'address' => '[name of the address]',
    //         'work_phone' => '[name of the work_phone]',
    //         'personal_phone' => '[name of the personal_phone]',
    //         'work_mail' => '[name of the work_mail]',
    //         'private_mail' => '[name of the private_mail]',
    //         'municipality_id' => '[name of the municipality_id]',
    //         'age_id' => '[name of the age_id]',
    //         'sex_id' => '[name of the sex_id]',
    //         'militancy_id' => '[name of the militancy_id]',
    //         'year_entry_anec' => '[name of the year_entry_anec]',
    //         'description' => '[name of the academic level]',
    //         'description' => '[name of the academic level]',
    //         'description' => '[name of the academic level]',
    //         'description' => '[name of the academic level]',
    //         'description' => '[name of the academic level]',
    //         'description' => '[name of the academic level]',
    //         'description' => '[name of the academic level]',
    //     ];
    // }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'unique' => 'El campo :attribute ya existe en la Base de Datos.',
            'regex' => 'El campo :attribute tiene problemas en su estructura.',
        ];
    }
}
