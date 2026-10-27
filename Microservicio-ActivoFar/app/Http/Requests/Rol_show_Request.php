<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Rol_show_Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function attributes()
    {
        return [
            'rol' => '[nombre del rol]'
        ];
    }

    public function messages()
    {
        return [
            'boolean' => 'El campo :attribute debe ser booleano.',
        ];
    }
}
