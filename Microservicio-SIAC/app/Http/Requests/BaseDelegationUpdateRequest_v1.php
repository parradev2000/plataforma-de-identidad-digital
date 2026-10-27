<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseDelegationUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_base_delegation,name,'.$this->route('base_delegation'),
            'description' => 'nullable|string',
            'father' => 'nullable|integer',
            'state' => 'nullable|boolean',
        ];
    }

    // public function attributes()
    // {
    //     return [
    //         'rol' => '[nombre del rol]',
    //         'estado' => '[nombre del estado]'
    //     ];
    // }

    // public function messages()
    // {
    //     return [
    //         'required' => 'El campo :attribute es obligatorio llenarlo.',
    //         'unique' => 'El campo :attribute ya existe en la Base de Datos.',
    //         'regex' => 'El campo :attribute tiene problemas en su estructura.',
    //     ];
    // }
}