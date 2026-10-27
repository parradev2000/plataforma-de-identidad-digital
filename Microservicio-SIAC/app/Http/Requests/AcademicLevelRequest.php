<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcademicLevelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'description' => 'required|string'
        ];
    }

    public function attributes()
    {
        return [
            'description' => '[the opcion description]'
        ];
    }

    /*
    public function messages()
    {
        return [
            'boolean' => 'El campo :attribute debe ser booleano.',
            'required' => 'El campo :attribute es obligatorio llenarlo.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'string' => 'El campo :attribute debe ser de tipo string.',
        ];
    }
    */
}
