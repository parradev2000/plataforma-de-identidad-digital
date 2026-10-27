<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseDelegationListRequest_v1 extends FormRequest
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
            'father_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'state' => 'nullable|in:active,inactive',
        ];
    }
}
