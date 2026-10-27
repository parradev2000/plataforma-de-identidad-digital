<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueryListRegRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'year' => 'nullable|integer',
            'month' => 'nullable|integer',
        ];
    }
}
