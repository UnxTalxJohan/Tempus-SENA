<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ficha extends Model
{
    protected $table = 'ficha';
    protected $primaryKey = 'id_fich';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_fich',
        'caracterizacion_fich',
        'cod_prog_fk',
        'fecha_inic_lec',
        'fecha_fin_lec',
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'cod_prog_fk', 'id_prog');
    }
}
