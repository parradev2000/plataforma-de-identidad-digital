<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilitancyUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'state' => 'nullable|boolean',
            'name' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_militancies,name,'.$this->route('militancy_id'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => '[name of the militancy]',
            'state' => '[state of the militancy]'
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