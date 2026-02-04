<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificacion';
    protected $primaryKey = 'id_noti';
    public $timestamps = false;

    protected $fillable = [
        'cc_usuario_fk',
        'fch_noti',
        'hora_noti',
        'titulo',
        'descripcion',
        'estado',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'cc_usuario_fk', 'cc');
    }
}
