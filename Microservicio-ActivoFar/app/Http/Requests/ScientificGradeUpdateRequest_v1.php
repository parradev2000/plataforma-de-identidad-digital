<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScientificGradeUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'description' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_scientific_grades,description,'.$this->route('IdScientificGrade'),
            'state' => 'nullable|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'description' => '[name of the academic level]',
            'state' => '[state of the academic level]',
        ];
    }

    /*public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'unique' => 'El campo :attribute ya existe en la Base de Datos.',
            'regex' => 'El campo :attribute tiene problemas en su estructura.',
        ];
    }*/
}