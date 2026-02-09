<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programa;
use App\Helpers\RouteHasher;
use Illuminate\Support\Facades\DB;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::orderBy('nombre')->get();
        // Agregar hash a cada programa
        $programas->each(function($programa) {
            $programa->hash = RouteHasher::encode($programa->id_prog);
        });
        $countContratistas = DB::table('usuario')->where('id_rol_fk', 2)->count();
        $countTitulada = DB::table('usuario')->where('id_rol_fk', 3)->count();
        $countUsuarios = $countContratistas + $countTitulada;

        return view('home', compact('programas', 'countContratistas', 'countTitulada', 'countUsuarios'));
    }
}
