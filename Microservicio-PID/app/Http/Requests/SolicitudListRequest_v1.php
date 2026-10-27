<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudListRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paginate' => 'nullable|integer',
            'persona_rel' => 'nullable',
            'personas_rel' => 'nullable',
            'persona_id' => 'nullable|integer',
            'persona_ci' => 'nullable|string',
            'persona_nombres' => 'nullable|string',
            'persona_apellidos' => 'nullable|string',
            'estado_id' => 'nullable|integer',
            'servicio_id' => 'nullable|integer',
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[opcion paginate]',
            'persona_rel' => '[true/false para incluir datos de la persona]',
            'personas_rel' => '[alias de persona_rel]',
            'persona_id' => '[opcion persona_id]',
            'persona_ci' => '[opcion persona_ci]',
            'persona_nombres' => '[opcion persona_nombres]',
            'persona_apellidos' => '[opcion persona_apellidos]',
            'estado_id' => '[opcion estado_id]',
            'servicio_id' => '[opcion servicio_id]',
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