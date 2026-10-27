<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'names' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_persons,names,'.$this->route('person'),
            'identity' => 'nullable|regex:/\d{11}/i|unique:pgsql.nomencladores.nom_persons,identity,'.$this->route('person'),
            'especiality' => 'nullable|string|max:255',
            'academic_postgraduate_specialty' => 'nullable|string|max:255',
            'scientific_postgraduate_specialty' => 'nullable|string|max:255',
            'professional_ability' => 'nullable|string|max:255',
            'work_center' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'work_phone' => 'nullable|string|max:8',
            'personal_phone' => 'nullable|string|max:8',
            'work_mail' => 'nullable|string|max:255',
            'private_mail' => 'nullable|string|max:255',
            'municipality_id' => 'nullable|integer',
            'age_id' => 'nullable|integer',
            'sex_id' => 'nullable|integer',
            'militancy_id' => 'nullable|integer',
            'year_entry_anec' => 'nullable|integer',
            'school_level_id' => 'nullable|integer',
            'academic_level_id' => 'nullable|integer',
            'scientific_grade_id' => 'nullable|integer',
            'teaching_category_id' => 'nullable|integer',
            'research_category_id' => 'nullable|integer',
            'entity_id' => 'nullable|integer',
            'business_system_id' => 'nullable|integer',
            'charge_id' => 'nullable|integer',
            'state' => 'nullable|boolean',
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
