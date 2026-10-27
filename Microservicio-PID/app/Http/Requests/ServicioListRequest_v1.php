<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicioListRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paginate' => 'nullable|integer',
            'nombre' => 'nullable|string',
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[opcion paginate]',
            'nombre' => '[opcion nombre]',
        ];
    }

    public function messages()
    {
        return [
            'integer' => 'El campo :attribute debe ser un número entero.',
            'string' => 'El campo :attribute debe ser de tipo string.',
        ];
    }
}