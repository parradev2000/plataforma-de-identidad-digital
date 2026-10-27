<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Casts\Attribute;

class rol extends SpatieRole
{
    protected $table = "autenticacion.rols";

    protected $fillable = [
        "rol",
        "estado"
    ];

    protected $hidden = ["created_at", "updated_at"];

    protected function rol(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }

    public function getNameAttribute()
    {
        return $this->attributes["rol"];
    }

    public function setNameAttribute($value)
    {
        $this->attributes["rol"] = strtolower($value);
    }
}
