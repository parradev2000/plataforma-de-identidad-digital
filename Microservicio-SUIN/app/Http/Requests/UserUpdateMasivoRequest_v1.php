<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateMasivoRequest_v1 extends FormRequest
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
            'estado' => 'boolean',
            'rol_id' => 'integer',
        ];
    }

    public function attributes()
    {
        return [
            'all' => '[nombre del all]',
            'items' => '[nombre del items]',
            'rol_id' => '[rol del usuario]',
            'estado' => '[estado del usuario]',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'unique' => 'El campo :attribute ya existe en la Base de Datos.',
            'boolean' => 'El campo :attribute debe de ser de tipo booleano.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'string' => 'El campo :attribute debe ser de tipo string.',
            'lowercase' => 'El :attribute debe ser minusculas.',
            'size' => 'El :attribute debe contener 11 caracteres.',
            'regex' => 'El :attribute debe contener solo numeros.',
        ];
    }
}
