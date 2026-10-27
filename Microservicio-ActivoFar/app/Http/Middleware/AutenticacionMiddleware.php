<?php

namespace App\Http\Middleware;

use App\Helpers\BestHelper;
use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;

class AutenticacionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization') && $request->header('X-Authorization')) {
            return BestHelper::Data(null, 400, false, 'No puedes autenticarte con dos tokens');
        }
        if ($request->header('Authorization')) {
            $token = new AuthController;
            if ($token->checkLogin($request, $code, $message)) {
                return $next($request);
            } else {
                return BestHelper::Data(null, $code, false, $message);
            }
            return $next($request);
            
        }elseif ($request->header('X-Authorization')) {
            $token = new AuthController;
            if ($token->validar_token($request, $code, $message)) {
                return $next($request);
            } else {
                return BestHelper::Data(null, $code, false, $message);
            }
            return $next($request);
        }else {
            return BestHelper::Data(null, 408, false, 'No existe el token de acceso');
        }
        
    }
}
