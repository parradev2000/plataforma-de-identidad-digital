<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChargeUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_charges,name,'.$this->route('charge_anec'),
            'state' => 'nullable|boolean',
            'description' => 'nullable|string',
        ];
    }

    // public function attributes()
    // {
    //     return [
    //         'name' => '[name of the charge]',
    //         'state' => '[state of the charge]',
    //         'description' => '[description of the charge]',
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