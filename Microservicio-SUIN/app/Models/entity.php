<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class entity extends Model{

    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nomencladores.nom_entities';

    protected $fillable = [
        'name',
        'description',
        'state'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected function name(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }
}