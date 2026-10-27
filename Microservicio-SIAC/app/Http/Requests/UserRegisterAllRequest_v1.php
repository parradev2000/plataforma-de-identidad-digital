<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRegisterAllRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombres' => 'required|string',
            'usuario' => 'required|string|lowercase',
            'rol_id' =>'required|integer',
            'carne' =>'required|regex:/\d{11}/i|string|size:11|unique:pgsql.autenticacion.users,carne',
            // 'password' => 'string'/* ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised(),] */,
        ];
    }

    public function attributes()
    {
        return [
            'nombres'=> '[nombre y apellidos del usuario]',
            'carne' => '[carne del usuario]',
            'rol_id' => '[atributo del usuario]',
            'carne' => '[atributo del carne]',
            // 'password' => '[atributo del password]'
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
