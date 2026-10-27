<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RolUpdateMasivoRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'all' => 'nullable|boolean',
            'items' => 'nullable',
            'estado' => 'nullable|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'all' => '[nombre del all]',
            'items' => '[nombre del items]',
            'estado' => '[nombre del estado]'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'unique' => 'El campo :attribute ya existe en la Base de Datos.',
            'regex' => 'El campo :attribute tiene problemas en su estructura.',
        ];
    }
}