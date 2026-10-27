<?php

namespace App\Http\Middleware;

use App\Helpers\Helpers;
use Closure;
use Illuminate\Http\Request;

class RolePermissionMiddleware
{
    public function handle(Request $request, Closure $next, ...$allowedRoles)
    {
        $user = Helpers::user_log_all($request->header('Authorization'));

        if ($user) {
            if (!empty($allowedRoles) && !in_array($user->rol_id, $allowedRoles)) {
                return response()->json([
                    'error' => 'Sin permisos',
                    'message' => 'No tienes el rol requerido para realizar esta acción'
                ], 403);
            }
        }
        
        return $next($request);
    }
}