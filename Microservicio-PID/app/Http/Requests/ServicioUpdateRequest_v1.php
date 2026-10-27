<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicioUpdateRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('servicio') instanceof \App\Models\Servicio
            ? $this->route('servicio')->id
            : $this->route('servicio');

        return [
            'nombre' => 'sometimes|required|string|unique:pgsql.nomencladores.servicios,nombre,' . $id,
        ];
    }

    public function attributes()
    {
        return [
            'nombre' => '[nombre del servicio]',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'unique' => 'El campo :attribute ya existe en la Base de Datos.',
            'string' => 'El campo :attribute debe ser de tipo string.',
        ];
    }
}