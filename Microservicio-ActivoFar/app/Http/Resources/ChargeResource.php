<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'name' => $this->name,
            'state' => $this->state,
            'description' => $this->description,
            // 'base_delegation' => $this->base_delegation->makeHidden('sub'),
            // 'person' => $this->person
        ];
    }
}
