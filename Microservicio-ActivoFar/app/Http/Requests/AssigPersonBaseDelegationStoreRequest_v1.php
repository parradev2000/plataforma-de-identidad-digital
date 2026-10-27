<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssigPersonBaseDelegationStoreRequest_v1 extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'person_id' => 'required|integer',
            'charge_anec_id' => 'required|integer',
            'base_delegation_id' => 'required|integer',
        ];
    }
}
