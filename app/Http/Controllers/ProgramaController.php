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

    public function toggleActivo($id_prog)
    {
        $programa = Programa::where('id_prog', $id_prog)->firstOrFail();
        // Si intenta activar y ya existe uno activo con el mismo código, bloquear
        if (!$programa->acti) {
            $existeActivo = Programa::where('id_prog', $id_prog)
                ->where('acti', 1)
                ->exists();
            if ($existeActivo) {
                return redirect()->route('dashboard')->with('error', 'No pueden haber 2 códigos de competencia repetidos.');
            }
        }
        $programa->acti = !$programa->acti;
        $programa->save();
        return redirect()->route('dashboard')->with('success', '✅ Estado de programa actualizado.');
    }
}
