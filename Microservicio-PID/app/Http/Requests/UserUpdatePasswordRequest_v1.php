<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserUpdatePasswordRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'password' => 'required|string',
            'new_password' => 'required|string' /* [Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()] */,
        ];
    }

    public function attributes()
    {
        return [
            'nombres' => '[atributo new_password]',
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
