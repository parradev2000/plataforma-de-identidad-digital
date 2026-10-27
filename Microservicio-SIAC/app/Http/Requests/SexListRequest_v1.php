<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SexListRequest_v1 extends FormRequest
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
            'abbreviation' => 'nullable|string',
            'description' => 'nullable|string',
            'state' => 'nullable|in:active,inactive'           
        ];
    }

    public function attributes()
    {
        return [
            'paginate' => '[paginate]',
            'name' => '[name]',
            'abbreviation' => '[abbreviation]',
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



















?>