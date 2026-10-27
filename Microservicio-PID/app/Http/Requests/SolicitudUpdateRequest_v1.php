<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudUpdateRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('solicitud') instanceof \App\Models\Solicitud
            ? $this->route('solicitud')->id
            : $this->route('solicitud');

        return [
            'persona_id' => 'sometimes|required|integer|exists:pgsql.nomencladores.personas,id|unique:pgsql.datos.solicitudes,persona_id,' . $id,
            'servicio_id' => 'sometimes|required|integer|exists:pgsql.nomencladores.servicios,id',
            'estado_id' => 'sometimes|required|integer|exists:pgsql.nomencladores.estados,id',
        ];
    }

    public function attributes()
    {
        return [
            'persona_id' => '[persona]',
            'servicio_id' => '[servicio]',
            'estado_id' => '[estado]',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'exists' => 'El :attribute no existe en el sistema.',
        ];
    }
}