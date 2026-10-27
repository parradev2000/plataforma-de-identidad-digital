<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonaListRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paginate' => 'nullable|integer',
            'solicitudes_rel' => 'nullable',
            'nombres' => 'nullable|string',
            'apellidos' => 'nullable|string',
            'ci' => 'nullable|string',
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[opcion paginate]',
            'solicitudes_rel' => '[true/false para incluir solicitudes de la persona]',
            'nombres' => '[opcion nombres]',
            'apellidos' => '[opcion apellidos]',
            'ci' => '[opcion ci]',
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