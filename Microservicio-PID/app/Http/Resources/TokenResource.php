<?php

namespace App\Http\Resources;

use App\Helpers\BestHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TokenResource extends JsonResource
{
    public function toArray($request)
    {
        $fecha_exp = Carbon::parse($this->user->password_expiry);
        $fecha_actual = Carbon::now();
        $diferenciaEnDias = $fecha_exp->diffInDays($fecha_actual);
        return [
            'token'=> $this->token,
            'id_usuario'=> $this->user_id,
            'usuario'=> $this->user->usuario,
            'nombre'=> $this->user->persona ? trim(($this->user->persona->nombres ?? '') . ' ' . ($this->user->persona->apellidos ?? '')) : null,
            'valid'=> $this->user->valid,
            'rol'=> $this->user->rol ? $this->user->rol->name : null,
            'terminal' => $this->dispositivo,
            'zona_horaria' => $this->timezone,
            'creacion'=> BestHelper::TimeZone($this->created_at, $this->timezone),
            'inicio'=> BestHelper::TimeZone(date_modify($this->created_at, $this->validez_ini), $this->timezone),
            'fin'=> BestHelper::TimeZone(date_modify($this->created_at, $this->validez_fin), $this->timezone),
            'intermedio'=> $this->validez_inter,
            'tiempo_exp' => $diferenciaEnDias
        ];
    }
}
