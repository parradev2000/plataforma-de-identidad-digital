<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $user = User::find($this->accessToken->tokenable_id);
        return [
            'access_token'=> $this->accessToken->token,
            'token_type' => 'Bearer',
            'token_id' => $this->accessToken->id,
            'expires_at' => $this->accessToken->expires_at,
            'nombres' => $user->nombres,
            'id_usuario' => $user->id,
            'usuario' => $user->usuario,
            'rol' => $user->rol->rol,
            'estado' => $user->estado,
        ];
    }
}
