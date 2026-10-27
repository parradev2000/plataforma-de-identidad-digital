<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

    class LoginRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario' => 'required|string|lowercase',
            'password' =>'required|string',
            'terminal' => 'required|string',
            'zona_horaria' => 'string',
            'inicio' => 'string',
            'intermedio' => 'string',
            'fin' => 'string',
        ];
    }

    public function attributes()
    {
        return [
            'password'=> '[contraseña]',
            'usuario' => '[nombre de usuario]',
            // 'remember' => '[Recordar cuenta del usuario]'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            // 'email' => 'El campo :attribute esta escrito incorrectamente.',
            'boolean' => 'El campo :attribute debe de ser de tipo booleano.',
            'string' => 'El campo :attribute debe ser de tipo string.',
            'lowercase' => 'El :attribute debe ser minusculas.'
        ];
    }
}
