<?php

namespace App\Observers;

use App\Models\rol;
use Spatie\Permission\Models\Permission;

class RolObserver
{
    /**
     * Handle the rol "deleted" event.
     */
    public function deleted(rol $rol): void
    {
        // Revocar todos los permisos del rol antes de eliminar
        $rol->syncPermissions([]);
    }
}
