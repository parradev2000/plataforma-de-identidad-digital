<?php

namespace App\Http\Middleware;

use App\Helpers\BestHelper;
use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;

class AuthIdentityServerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('X-Authorization')) {
            $token = new AuthController;
            if ($token->validar_token($request, $code, $message)) {
                return $next($request);
            } else {
                return BestHelper::Data(null, $code, false, $message);
            }
        }else {
            return BestHelper::Data(null, 409, false, 'No existe el token de acceso');
        }
    }
}
