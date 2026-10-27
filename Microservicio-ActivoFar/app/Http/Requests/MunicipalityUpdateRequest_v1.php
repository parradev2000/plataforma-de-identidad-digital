<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MunicipalityUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_municipalities,name,'.$this->route('municipality'),
            'province_id' => 'nullable|integer',
            'state' => 'nullable|boolean',
        ];
    }

    // public function attributes()
    // {
    //     return [
    //         'name' => '[the option name]',
    //         'state' => '[the option state]'
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