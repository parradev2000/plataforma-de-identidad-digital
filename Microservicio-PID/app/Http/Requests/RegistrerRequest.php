<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistrerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'meta.action' => 'required|string',
            'store.nombres' => 'required|string',
            'store.rol_id' =>'required|integer',
            'store.carne' =>'required|regex:/\d{11}/i|string|size:11|unique:pgsql.autenticacion.users,carne',
            // 'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised(),],
        ];
    }

    public function attributes()
    {
        return [
            'store.nombres'=> '[nombre y apellidos del usuario]',
            'store.carne' => '[carne del usuario]',
            'meta.action' => '[nombre de la accion]',
            'store.rol_id' => '[rol del usuario]'
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
