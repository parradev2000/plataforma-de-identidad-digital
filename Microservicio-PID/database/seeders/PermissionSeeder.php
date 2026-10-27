<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\rol;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()["cache"]->forget("spatie.permission.cache");

        // Permisos de usuarios
        $userPermissions = [
            "users.list",
            "users.view",
            "users.create",
            "users.edit",
            "users.delete",
            "users.reset_password",
            "users.change_password",
            "users.delete_masivo",
            "users.update_masivo",
        ];

        // Permisos de roles
        $rolePermissions = [
            "roles.list",
            "roles.view",
            "roles.create",
            "roles.edit",
            "roles.delete",
            "roles.update_masivo",
            "roles.delete_masivo",
            "roles.create_masivo",
        ];

        // Permisos de auditoria
        $auditPermissions = [
            "audits.list",
        ];

        // Permisos de gestion de permisos
        $permissionPermissions = [
            "permissions.list",
            "permissions.view",
            "permissions.create",
            "permissions.edit",
            "permissions.delete",
            "permissions.assign_role",
            "permissions.revoke_role",
            "permissions.assign_user",
            "permissions.revoke_user",
        ];

        $allPermissions = array_merge(
            $userPermissions,
            $rolePermissions,
            $auditPermissions,
            $permissionPermissions
        );

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate([
                "name" => $perm,
                "guard_name" => "web"
            ]);
        }

        // Asignar todos los permisos al rol Administrador (id=1)
        $adminRole = rol::find(1);
        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::all());
        }

        echo "Seeder completado: " . count($allPermissions) . " permisos creados\n";
    }
}
