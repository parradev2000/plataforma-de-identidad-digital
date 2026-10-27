<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Casts\Attribute;

class rol extends SpatieRole
{
    protected $table = "autenticacion.rols";

    protected $fillable = [
        "name",
        "rol",
        "guard_name",
        "estado"
    ];

    protected $hidden = ["created_at", "updated_at"];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($role) {
            $attrs = $role->getAttributes();
            if ((empty($attrs['name']) || $attrs['name'] === null) && isset($attrs['rol']) && !empty($attrs['rol'])) {
                $role->setRawAttributes(array_merge($attrs, ['name' => strtolower($attrs['rol'])]), true);
            } elseif (!empty($attrs['name'])) {
                $role->setRawAttributes(array_merge($attrs, ['name' => strtolower($attrs['name'])]), true);
            }
            $attrs = $role->getAttributes();
            if (empty($attrs['guard_name'])) {
                $role->setRawAttributes(array_merge($attrs, ['guard_name' => 'web']), true);
            }
            if (!array_key_exists('estado', $attrs) || $attrs['estado'] === null) {
                $role->setRawAttributes(array_merge($attrs, ['estado' => true]), true);
            }
        });
    }

    public function getRolAttribute()
    {
        $name = $this->attributes['name'] ?? null;
        return $name ? ucwords($name) : null;
    }

    public function setRolAttribute($value)
    {
        if ($value !== null) {
            $this->attributes['name'] = strtolower($value);
        }
    }

    public function getNameAttribute($value = null)
    {
        if (array_key_exists('name', $this->attributes) && $this->attributes['name'] !== null) {
            return $this->attributes['name'];
        }
        if (array_key_exists('rol', $this->attributes) && $this->attributes['rol'] !== null) {
            $this->attributes['name'] = strtolower($this->attributes['rol']);
            return $this->attributes['name'];
        }
        return $value;
    }

    public function setNameAttribute($value)
    {
        if ($value !== null) {
            $v = strtolower($value);
            $this->attributes['name'] = $v;
        }
    }
}
