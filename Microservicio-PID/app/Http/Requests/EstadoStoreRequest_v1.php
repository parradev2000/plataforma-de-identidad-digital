<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstadoStoreRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|unique:pgsql.nomencladores.estados,nombre',
        ];
    }

    public function attributes()
    {
        return [
            'nombre' => '[nombre del estado]',
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