<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'persona_id' => $this->persona_id,
            'estado_id' => $this->estado_id,
            'estado' => $this->estado ? $this->estado->nombre : null,
            'servicio_id' => $this->servicio_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($request->boolean('servicio_rel') && $this->relationLoaded('servicio')) {
            $data['servicio_rel'] = $this->servicio ? [
                'id' => $this->servicio->id,
                'nombre' => $this->servicio->nombre,
            ] : null;
        }

        if ($request->boolean('persona_rel')) {
            $data['persona'] = $this->persona ? [
                'id' => $this->persona->id,
                'nombres' => $this->persona->nombres,
                'apellidos' => $this->persona->apellidos,
                'ci' => $this->persona->ci,
            ] : null;
        }

        return $data;
    }
}