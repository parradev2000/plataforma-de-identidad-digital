<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ci' => 'required|string'
        ];
    }

    public function attributes()
    {
        return [
            'ci' => '[carnet de identidad]'
        ];
    }

}
