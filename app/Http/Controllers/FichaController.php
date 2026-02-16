<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FichaController extends Controller
{
    public function index()
    {
        // Lista de usuarios registrados a cada ficha con sus fechas lectivas
        $registros = DB::table('evento')
            ->join('ficha', 'evento.id_fich_fk', '=', 'ficha.id_fich')
            ->join('usuario', 'evento.cc_fk', '=', 'usuario.cc')
            ->select(
                'ficha.id_fich',
                'ficha.fecha_inic_lec',
                'ficha.fecha_fin_lec',
                'usuario.cc',
                'usuario.nombre as usuario_nombre'
            )
            ->groupBy(
                'ficha.id_fich',
                'ficha.fecha_inic_lec',
                'ficha.fecha_fin_lec',
                'usuario.cc',
                'usuario.nombre'
            )
            ->orderBy('ficha.id_fich')
            ->orderBy('usuario.nombre')
            ->get();

        return view('ficha.index', [
            'registros' => $registros,
        ]);
    }
}
