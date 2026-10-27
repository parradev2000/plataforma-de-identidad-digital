<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditoriaDeleteRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'all' => 'required|in:false,true',
            'reset' => 'required|in:false,true',
            'id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'accion' => 'nullable|string',
            'fecha' => 'nullable|date',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[opcion paginate]',
            'id' => '[opcion id]',
            'user_id' => '[opcion user_id]',
            'accion' => '[opcion accion]',
            'fecha' => '[opcion fecha]',
            'fecha_ini' => '[opcion fecha_ini]',
            'fecha_fin' => '[opcion fecha_fin]',
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
