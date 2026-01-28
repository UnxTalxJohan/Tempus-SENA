<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    protected $table = 'resultado';
    protected $primaryKey = 'id_resu';
    public $timestamps = false;
    
    protected $fillable = [
        'cod_resu',
        'nombre',
        'duracion_hora_max',
        'duracion_hora_min',
        'trim_prog',
        'hora_sema_programar',
        'hora_trim_programar',
        'cod_comp_fk',
        'id_prog_fk'
    ];
}
