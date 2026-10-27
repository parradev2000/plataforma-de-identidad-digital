<?php

namespace App\Observers;

use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->syncRole($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('rol_id')) {
            $this->syncRole($user);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $user->syncRoles([]);
    }

    /**
     * Sincronizar el rol del usuario con Spatie
     */
    private function syncRole(User $user): void
    {
        if ($user->rol_id) {
            $rol = SpatieRole::find($user->rol_id);
            if ($rol) {
                $user->syncRoles([$rol]);
            }
        } else {
            $user->syncRoles([]);
        }
    }
}
