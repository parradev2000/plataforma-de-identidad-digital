<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseDelegationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'name' => $this->name,
            'father' => $this->father,
            'description' => $this->description,
            'state' => $this->state,
            'charges_anec' => $this->charges_anec,
            'sub' => $this->sub,
        ];
    }
}
