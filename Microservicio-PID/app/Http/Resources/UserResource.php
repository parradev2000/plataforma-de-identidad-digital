<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $personaRel = $request->input('persona_rel');
        $showPersona = is_string($personaRel) ? in_array(strtolower($personaRel), ['true','1','on','yes']) : filter_var($personaRel, FILTER_VALIDATE_BOOLEAN);

        $rolRel = $request->input('rol_rel');
        $showRol = is_string($rolRel) ? in_array(strtolower($rolRel), ['true','1','on','yes']) : filter_var($rolRel, FILTER_VALIDATE_BOOLEAN);

        $data = [
            'id'=> $this->id,
            'usuario' => $this->usuario,
            'persona_id' => $this->persona_id,
            'rol_id' => $this->rol ? $this->rol->id : $this->rol_id,
            'estado' => $this->estado,
        ];

        if ($showPersona === true) {
            $data['persona'] = $this->persona ? [
                'id' => $this->persona->id,
                'nombres' => $this->persona->nombres,
                'apellidos' => $this->persona->apellidos,
                'ci' => $this->persona->ci,
            ] : null;
        }

        if ($showRol === true) {
            $data['rol'] = $this->rol ? [
                'id' => $this->rol->id,
                'name' => $this->rol->name,
                'rol' => $this->rol->rol ?? $this->rol->name,
                'guard_name' => $this->rol->guard_name,
                'estado' => $this->rol->estado ?? null,
            ] : null;
        }

        return $data;
    }
}
