<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonListRequest_v1 extends FormRequest
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
            'identity' => 'nullable|regex:/\d{11}/i',
            'state' => 'nullable|in:active,inactive',
        ];
    }

}
