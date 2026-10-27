<?php

namespace App\Http\Middleware;

use App\Helpers\BestHelper;
use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;

class AuthEstandarMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization')) {
            $token = new AuthController;
            if ($token->checkLogin($request, $code, $message)) {
                return $next($request);
            } else {
                return BestHelper::Data(null, $code, false, $message);
            }        
        }else {
            return BestHelper::Data(null, 400, false, 'No existe el token de acceso');
        }        
    }
}
