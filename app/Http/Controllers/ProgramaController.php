<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programa;
use App\Helpers\RouteHasher;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::orderBy('nombre')->get();
        // Agregar hash a cada programa
        $programas->each(function($programa) {
            $programa->hash = RouteHasher::encode($programa->id_prog);
        });
        return view('home', compact('programas'));
    }
}
