<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditoriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'identificador'=> $this->id,
            'usuario' => $this->usuario,
            'nombres' => $this->nombres,
            'accion' => $this->accion,
            'ip' => $this->ip,
            'fecha' => date('d-m-Y H:i:s', strtotime($this->created_at)),
        ];
    }
}
