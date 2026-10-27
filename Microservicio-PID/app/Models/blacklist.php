<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class blacklist extends Model
{
    use HasFactory;

    protected $table = 'autenticacion.blacklist';

    protected $fillable = [
        'id',
        'token'
    ];

    protected $hidden = ['created_at', 'updated_at'];
}
