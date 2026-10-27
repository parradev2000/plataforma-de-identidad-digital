<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;


class year extends Model{

    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nomencladores.nom_years';

    protected $fillable = [
        'year',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

}