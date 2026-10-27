<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $table = 'autenticacion.tokens';

    protected $fillable = [
        'id',
        'dispositivo',
        'timezone',
        'validez_ini',
        'validez_inter',
        'validez_fin',
        'token'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
