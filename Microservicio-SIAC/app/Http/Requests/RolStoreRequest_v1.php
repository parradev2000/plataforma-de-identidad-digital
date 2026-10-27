<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RolStoreRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rol' => 'required|regex:/[a-z ]+/i|unique:pgsql.autenticacion.rols,rol',
            'estado' => 'nullable|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'rol' => '[nombre del rol]',
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
