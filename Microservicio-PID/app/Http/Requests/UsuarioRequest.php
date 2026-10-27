<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' =>'string',
            'apellidos' =>'string',
            'password' =>'string',
            'estado' =>'string',
            'roles' =>'string',
            'usuario' => 'string|lowercase|unique:pgsql.autenticacion.users,usuario',
            'remember' => 'boolean'
        ];
    }

    public function attributes()
    {
        return [
            'password'=> '[contraseña]',
            'usuario' => '[nombre de usuario]',
            'remember' => '[Recordar cuenta del usuario]',
            'estado' => '[estado del usuario]',
            'roles' => '[rol del usuario]'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'boolean' => 'El campo :attribute debe de ser de tipo booleano.',
            'string' => 'El campo :attribute debe ser de tipo string.',
            'lowercase' => 'El :attribute debe ser minusculas.'
        ];
    }
}
