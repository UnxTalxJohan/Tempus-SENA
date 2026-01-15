<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    protected $table = 'competencia';
    protected $primaryKey = 'cod_comp';
    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = [
        'cod_comp',
        'nombre',
        'duracion_hora',
        'id_prog_fk'
    ];
}
