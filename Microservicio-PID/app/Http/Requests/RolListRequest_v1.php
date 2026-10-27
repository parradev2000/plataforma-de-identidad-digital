<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RolListRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paginate' => 'nullable|integer',
            'rol' => 'nullable|string',
            'estado' => 'nullable|in:active,inactive',
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[opcion paginate]',
            'rol' => '[opcion rol]',
            'estado' => '[opcion estado]'
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
