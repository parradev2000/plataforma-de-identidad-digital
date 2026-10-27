<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessSystemUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_business_systems,name,'.$this->route('business_system'),
            'state' => 'nullable|boolean',
            'description' => 'nullable|string'
        ];
    }
}