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
            'servicio' => $this->servicio ? $this->servicio->nombre : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        $personasRel = $request->input('persona_rel') ?? $request->input('personas_rel');
        $showPersona = is_string($personasRel) ? in_array(strtolower($personasRel), ['true','1','on','yes']) : filter_var($personasRel, FILTER_VALIDATE_BOOLEAN);
        if ($showPersona === true) {
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