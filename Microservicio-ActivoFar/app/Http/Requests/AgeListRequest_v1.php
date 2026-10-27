<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgeListRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paginate' => 'nullable|integer',
            'age' => 'nullable|string',
            'description' => 'nullable|string',
            'state' => 'nullable|in:active,inactive'   
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[paginate]',
            'age' => '[age]',
            'description' => '[description]',
            'state' => '[state]'           
        ];
    }

    public function messages()
    {
        return [
            'boolean' => 'The field :attribute must be boolean.',
            'required' => 'The field :attribute cannot be empty.',
            'integer' => 'The field :attribute must be a integer number.',
            'string' => 'The field :attribute must be string type.',
        ];
    }


}