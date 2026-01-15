<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $table = 'programa';
    protected $primaryKey = 'id_prog';
    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = [
        'id_prog',
        'nombre',
        'version',
        'nivel',
        'cant_trim'
    ];
}
