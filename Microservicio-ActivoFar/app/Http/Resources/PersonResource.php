<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'names' => $this->names,
            'identity' => $this->identity,
            'personal_profile' => $this->personal_profile->toJsonCustom(),
            'professional_profile' => $this->professional_profile->toJsonCustom(),
            'work_profile' => $this->work_profile->toJsonCustom(),
            'state' => $this->state,
            'social_networks' => $this->social_networks,
            'charges_anec' => $this->charges_anec->first()->makeHidden('pivot'),
            'base_delegation' => $this->base_delegation->first()->makeHidden('sub','pivot'),
        ];
    }
}
