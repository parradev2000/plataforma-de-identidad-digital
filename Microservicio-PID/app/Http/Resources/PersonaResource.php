<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'ci' => $this->ci,
            'id_digital' => $this->id_digital,
            'usuario' => $this->usuario,
            'contrasena' => $this->contrasena,
        ];

        $solicitudesRel = $request->input('solicitudes_rel');
        $showSolicitudes = is_string($solicitudesRel) ? in_array(strtolower($solicitudesRel), ['true','1','on','yes']) : filter_var($solicitudesRel, FILTER_VALIDATE_BOOLEAN);
        if ($showSolicitudes === true) {
            $data['solicitudes'] = $this->solicitudes->map(function ($s) {
                return [
                    'id' => $s->id,
                    'estado_id' => $s->estado_id,
                    'servicio_id' => $s->servicio_id,
                ];
            });
        }

        return $data;
    }
}