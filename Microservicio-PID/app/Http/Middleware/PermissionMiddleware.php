<?php

namespace App\Http\Middleware;

use App\Helpers\Helpers;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Helpers::user_log_all($request->header("Authorization"));

        if (!$user) {
            return response()->json([
                "error" => "Unauthorized",
                "message" => "Usuario no autenticado"
            ], 401);
        }

        // Verificar permisos (directo o через rol)
        $permissions = is_array($permission) ? $permission : explode("|", $permission);
        
        foreach ($permissions as $perm) {
            if ($user->hasPermissionTo($perm)) {
                return $next($request);
            }
        }

        return response()->json([
            "error" => "Forbidden",
            "message" => "No tienes el permiso requerido para realizar esta acción"
        ], 403);
    }
}
