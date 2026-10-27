<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserDeleteRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'all' => 'boolean',
            'reset' => 'boolean',
            'items' => '',
        ];
    }

    public function attributes()
    {
        return [
            'all' => '[opcion all]',
            'reset' => '[opcion reset]',
            'items' => '[opcion items]',
        ];
    }

    public function messages()
    {
        return [
            'boolean' => 'El campo :attribute debe ser booleano.',
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'string' => 'El campo :attribute debe ser de tipo string.',
        ];
    }
}
