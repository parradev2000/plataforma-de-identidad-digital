<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgeUpdateRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'age' => 'required|integer|unique:pgsql.nomencladores.nom_ages,age,'.$this->route('idage'),
            'description' => 'nullable|string'               
        ];
    }

    public function attributes()
    {
        return [
            'age' => '[age]',
            'description' => '[description]'            
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