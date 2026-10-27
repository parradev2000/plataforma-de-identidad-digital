<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MunicipalityListRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paginate' => 'nullable|integer',
            'name' => 'nullable|string',
            'province_id' => 'nullable|integer',
            'state' => 'nullable|in:active,inactive',
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[the option paginate]',
            'name' => '[the option name]',
            'province_id' => '[the option province_id]',
            'state' => '[the option state]',
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'boolean' => 'El campo :attribute debe ser booleano.',
    //         'required' => 'El campo :attribute es obligatorio llenarlo.',
    //         'integer' => 'El campo :attribute debe ser un número entero.',
    //         'string' => 'El campo :attribute debe ser de tipo string.',
    //     ];
    // }
}
