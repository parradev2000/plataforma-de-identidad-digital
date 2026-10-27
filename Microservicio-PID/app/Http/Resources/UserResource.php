<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'nombres' => $this->nombres,
            'usuario' => $this->usuario,
            'estado' => $this->estado,
            'rol' => $this->rol->rol,
            'rol_id' => $this->rol->id,
        ];
    }
}
