<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonaUpdateRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('persona') instanceof \App\Models\Persona
            ? $this->route('persona')->id
            : $this->route('persona');

        return [
            'nombres' => 'sometimes|required|string',
            'apellidos' => 'sometimes|required|string',
            'ci' => 'sometimes|required|string|size:11|regex:/^[0-9]{11}$/|unique:pgsql.nomencladores.personas,ci,' . $id,
        ];
    }

    public function attributes()
    {
        return [
            'nombres' => '[nombres]',
            'apellidos' => '[apellidos]',
            'ci' => '[carnet de identidad]',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'unique' => 'El campo :attribute ya existe en la Base de Datos.',
            'string' => 'El campo :attribute debe ser de tipo string.',
            'size' => 'El :attribute debe contener 11 caracteres.',
            'regex' => 'El :attribute debe contener solo numeros.',
        ];
    }
}