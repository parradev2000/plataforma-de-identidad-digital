<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Http\Requests\PermissionUpdateRequest;
use App\Http\Requests\PermissionAssignRoleRequest;
use App\Http\Requests\PermissionAssignUserRequest;
use App\Http\Resources\PermissionResource;
use App\Models\rol;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
    * Listar permisos
    * 
    * Devuelve el listado de los permisos del sistema
    * Filtros: name, guard_name, paginate
    */
    public function list(Request $request)
    {
        $query = Permission::query()->orderByDesc('id');

        if ($request->has('name')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->name) . '%']);
        }

        if ($request->has('guard_name')) {
            $query->where('guard_name', $request->guard_name);
        }

        $cant = $query->count();

        if ($request->has('paginate')) {
            $result = $query->paginate($request->paginate);
            return PermissionResource::collection($result);
        }

        return response([
            'data' => PermissionResource::collection($query->get()),
            'total' => $cant,
            'message' => 'Consulta satisfactoria.'
        ], 200);
    }

    /**
    * Ver permiso
    * 
    * Devuelve un permiso por el ID
    */
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response([
            'data' => new PermissionResource($permission),
            'message' => 'Consulta satisfactoria.'
        ], 200);
    }

    /**
    * Crear permiso
    * 
    * Crea un nuevo permiso en el sistema
    */
    public function create(PermissionRequest $request)
    {
        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web'
        ]);

        AuditoriaController::auditoria_pro($request, 'Se creó el permiso ' . $permission->name);

        return response([
            'data' => new PermissionResource($permission),
            'message' => 'Creacion satisfactoria.'
        ], 201);
    }

    /**
    * Actualizar permiso
    * 
    * Actualiza un permiso existente
    */
    public function update(PermissionUpdateRequest $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->update([
            'name' => $request->name ?? $permission->name,
            'guard_name' => $request->guard_name ?? $permission->guard_name
        ]);

        AuditoriaController::auditoria_pro($request, 'Se actualizó el permiso ' . $permission->name);

        return response([
            'data' => new PermissionResource($permission),
            'message' => 'Actualizacion satisfactoria.'
        ], 200);
    }

    /**
    * Eliminar permiso
    * 
    * Elimina un permiso del sistema
    */
    public function delete(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        AuditoriaController::auditoria_pro($request, 'Se eliminó el permiso ' . $permission->name);

        return response([
            'data' => new PermissionResource($permission),
            'message' => 'Eliminacion satisfactoria.'
        ], 200);
    }

    /**
    * Permisos por rol
    * 
    * Devuelve los permisos asignados a un rol específico
    */
    public function permissions_by_role(Request $request, $rolId)
    {
        $rol = rol::findOrFail($rolId);
        $permissions = $rol->permissions;
        return response([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Consulta satisfactoria.'
        ], 200);
    }

    /**
    * Asignar permisos a rol
    * 
    * Asigna uno o varios permisos a un rol
    */
    public function assign_to_role(PermissionAssignRoleRequest $request)
    {
        $rol = rol::findOrFail($request->rol_id);
        $permissions = Permission::whereIn('id', $request->permission_ids)->get();
        $rol->givePermissionTo($permissions);

        AuditoriaController::auditoria_pro($request, 'Se asignaron permisos al rol ' . $rol->rol);

        return response([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Permisos asignados al rol correctamente.'
        ], 200);
    }

    /**
    * Revocar permisos de rol
    * 
    * Revoca permisos de un rol específico
    */
    public function revoke_from_role(PermissionAssignRoleRequest $request)
    {
        $rol = rol::findOrFail($request->rol_id);
        $permissions = Permission::whereIn('id', $request->permission_ids)->get();
        $rol->revokePermissionTo($permissions);

        AuditoriaController::auditoria_pro($request, 'Se revocaron permisos del rol ' . $rol->rol);

        return response([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Permisos revocados del rol correctamente.'
        ], 200);
    }

    /**
    * Permisos de usuario
    * 
    * Devuelve todos los permisos de un usuario (directos y via rol)
    */
    public function permissions_by_user(Request $request, $userId)
    {
        $user = $this->getUser($userId);
        $permissions = $user->getAllPermissions();
        return response([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Consulta satisfactoria.'
        ], 200);
    }

    /**
    * Asignar permisos a usuario
    * 
    * Asigna permisos directamente a un usuario
    */
    public function assign_to_user(PermissionAssignUserRequest $request)
    {
        $user = $this->getUser($request->user_id);
        $permissions = Permission::whereIn('id', $request->permission_ids)->get();
        $user->givePermissionTo($permissions);

        AuditoriaController::auditoria_pro($request, 'Se asignaron permisos al usuario ' . $user->usuario);

        return response([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Permisos asignados al usuario correctamente.'
        ], 200);
    }

    /**
    * Revocar permisos de usuario
    * 
    * Revoca permisos directos de un usuario
    */
    public function revoke_from_user(PermissionAssignUserRequest $request)
    {
        $user = $this->getUser($request->user_id);
        $permissions = Permission::whereIn('id', $request->permission_ids)->get();
        $user->revokePermissionTo($permissions);

        AuditoriaController::auditoria_pro($request, 'Se revocaron permisos del usuario ' . $user->usuario);

        return response([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Permisos revocados del usuario correctamente.'
        ], 200);
    }

    private function getUser($id)
    {
        return \App\Models\User::findOrFail($id);
    }
}