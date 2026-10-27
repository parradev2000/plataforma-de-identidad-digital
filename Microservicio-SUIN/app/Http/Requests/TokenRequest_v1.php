<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TokenRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string',
            'client_id' => 'required|string',
            'redirect_uri' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'code' => '[opcion code]',
            'client_id' => '[opcion client_id]',
            'redirect_uri' => '[opcion redirect_uri]'
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
