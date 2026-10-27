<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialNetworkUpdateRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|regex:/[a-z ]+/i|unique:pgsql.nomencladores.nom_social_networks,name,'.$this->route('social_network'),
        ];
    }
}