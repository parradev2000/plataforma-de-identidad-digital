<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->filter['id'];
        return [
            'update.estado' => 'boolean',
            'update.nombres' => 'string',
            'update.rol_id' =>'integer',
            'update.usuario' => 'string|lowercase|unique:pgsql.autenticacion.users,usuario,'.$id,
            'update.carne' => 'regex:/\d{11}/i|string|size:11|unique:pgsql.autenticacion.users,carne,'.$id
        ];
    }

    public function attributes()
    {
        return [
            'update.nombres'=> '[nombre y apellidos del usuario]',
            'update.usuario'=> '[nombre del usuario]',
            'update.rol_id' => '[rol del usuario]',
            'update.estado' => '[estado del usuario]',
            'update.carne' => '[carne del usuario]'
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
