<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolLevelUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'state' => 'nullable|boolean',
            'name' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_school_levels,name,'.$this->route('school_level_id'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => '[name of the school level]',
            'state' => '[state of the school level]'
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'required' => 'El campo :attribute es obligatorio llenarlo.',
    //         'unique' => 'El campo :attribute ya existe en la Base de Datos.',
    //         'regex' => 'El campo :attribute tiene problemas en su estructura.',
    //     ];
    // }
}