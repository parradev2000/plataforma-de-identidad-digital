<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SexCreateRequest_v1 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_sexs,name',
            'abbreviation'=> 'required|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_sexs,abbreviation',
            'description' => 'nullable|string'               
        ];
    }

    public function attributes()
    {
        return [
            'name' => '[name]',
            'abbreviation' => '[abbreviation]',
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