<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FichaController extends Controller
{
    public function index()
    {
        // Listado de fichas con datos básicos y número de instructores asignados
        $registros = DB::table('ficha')
            ->leftJoin('programa', 'ficha.cod_prog_fk', '=', 'programa.id_prog')
            ->leftJoin('evento', 'evento.id_fich_fk', '=', 'ficha.id_fich')
            ->leftJoin('usuario', 'evento.cc_fk', '=', 'usuario.cc')
            ->select(
                'ficha.id_fich',
                'ficha.fecha_inic_lec',
                'ficha.fecha_fin_lec',
                'ficha.trimestre',
                'ficha.jornada',
                'ficha.abierta',
                'programa.nombre as programa_nombre',
                DB::raw('COUNT(DISTINCT usuario.cc) as total_instructores')
            )
            ->groupBy(
                'ficha.id_fich',
                'ficha.fecha_inic_lec',
                'ficha.fecha_fin_lec',
                'ficha.trimestre',
                'ficha.jornada',
                'ficha.abierta',
                'programa.nombre'
            )
            ->orderBy('ficha.id_fich')
            ->get();

        return view('ficha.index', [
            'registros' => $registros,
        ]);
    }

    public function create()
    {
        $programas = DB::table('programa')
            ->select('id_prog', 'nombre', 'nivel', 'cant_trim')
            ->orderBy('nombre')
            ->get();

        $dias = DB::table('horario')
            ->orderBy('id_horario')
            ->get();

        $instructores = DB::table('usuario')
            ->leftJoin('rol', 'usuario.id_rol_fk', '=', 'rol.id_rol')
            ->leftJoin('vinculacion', 'usuario.id_vinculacion_fk', '=', 'vinculacion.id_vinculacion')
            ->whereIn('usuario.id_rol_fk', [2, 3]) // solo contrato y planta
            ->orderBy('usuario.nombre')
            ->select(
                'usuario.cc',
                'usuario.nombre',
                'usuario.id_rol_fk',
                'rol.nombre_rol',
                'vinculacion.tip_vincul',
                'vinculacion.especialidad',
                'vinculacion.pregrado',
                'vinculacion.area',
                'vinculacion.red'
            )
            ->get();

        $instructoresContrato = $instructores->where('id_rol_fk', 2)->values();
        $instructoresPlanta   = $instructores->where('id_rol_fk', 3)->values();

        $competencias = DB::table('competencia')
            ->select('cod_comp', 'nombre', 'id_prog_fk')
            ->orderBy('nombre')
            ->get();

        $resultados = DB::table('resultado')
            ->select('cod_resu', 'nombre', 'cod_comp_fk', 'id_prog_fk')
            ->orderBy('cod_comp_fk')
            ->orderBy('cod_resu')
            ->get();

        return view('ficha.create', [
            'programas' => $programas,
            'dias' => $dias,
            'instructoresContrato' => $instructoresContrato,
            'instructoresPlanta' => $instructoresPlanta,
            'competencias' => $competencias,
            'resultados' => $resultados,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_fich' => 'required|integer|unique:ficha,id_fich',
            'cod_prog_fk' => 'required|integer|exists:programa,id_prog',
            'fecha_inic_lec' => 'nullable|date',
            'fecha_fin_lec' => 'nullable|date|after_or_equal:fecha_inic_lec',
            'proy_formativo_enruto' => 'nullable|string',
            'trimestre' => 'nullable|string|max:255',
            'abierta' => 'required|integer|in:1,2',
            'CDF' => 'nullable|string',
            'cerr_convenio' => 'nullable|string|max:255',
            'jornada' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $request) {
            DB::table('ficha')->insert([
                'id_fich' => $validated['id_fich'],
                'caracterizacion_fich' => null,
                'cod_prog_fk' => $validated['cod_prog_fk'],
                'fecha_inic_lec' => $validated['fecha_inic_lec'] ?? null,
                'fecha_fin_lec' => $validated['fecha_fin_lec'] ?? null,
                'proy_formativo_enruto' => $validated['proy_formativo_enruto'] ?? null,
                'trimestre' => $validated['trimestre'] ?? null,
                'abierta' => $validated['abierta'],
                'CDF' => $validated['CDF'] ?? null,
                'cerr_convenio' => $validated['cerr_convenio'] ?? null,
                'jornada' => $validated['jornada'] ?? null,
            ]);

            $horaInicio = $request->input('hora_inicio', []);
            $horaFin = $request->input('hora_fin', []);

            foreach ($horaInicio as $id_horario_fk => $hIni) {
                $hFin = $horaFin[$id_horario_fk] ?? null;
                if ($hIni && $hFin) {
                    DB::table('hora')->insert([
                        'hora_inicio' => $hIni,
                        'hora_fin' => $hFin,
                        'id_fich_fk' => $validated['id_fich'],
                        'id_horario_fk' => $id_horario_fk,
                    ]);
                }
            }
        });

        return redirect()
            ->route('ficha.index')
            ->with('success', 'Ficha creada correctamente. Ahora puedes asignar instructores y horarios.');
    }

    public function editSchedule($id_fich)
    {
        $ficha = DB::table('ficha')
            ->leftJoin('programa', 'ficha.cod_prog_fk', '=', 'programa.id_prog')
            ->where('ficha.id_fich', $id_fich)
            ->select('ficha.*', 'programa.nombre as programa_nombre', 'programa.nivel')
            ->first();

        if (!$ficha) {
            abort(404);
        }

        $dias = DB::table('horario')
            ->orderBy('id_horario')
            ->get();

        $horas = DB::table('hora')
            ->where('id_fich_fk', $id_fich)
            ->get()
            ->keyBy('id_horario_fk');

        return view('ficha.horario', [
            'ficha' => $ficha,
            'dias' => $dias,
            'horas' => $horas,
        ]);
    }

    public function updateSchedule(Request $request, $id_fich)
    {
        $ficha = DB::table('ficha')->where('id_fich', $id_fich)->first();
        if (!$ficha) {
            abort(404);
        }

        $inputDias = $request->input('dia', []);
        $inicio = $request->input('hora_inicio', []);
        $fin = $request->input('hora_fin', []);

        DB::transaction(function () use ($id_fich, $inputDias, $inicio, $fin) {
            DB::table('hora')->where('id_fich_fk', $id_fich)->delete();

            foreach ($inputDias as $id_horario_fk => $valor) {
                $hIni = $inicio[$id_horario_fk] ?? null;
                $hFin = $fin[$id_horario_fk] ?? null;

                if ($hIni && $hFin) {
                    DB::table('hora')->insert([
                        'hora_inicio' => $hIni,
                        'hora_fin' => $hFin,
                        'id_fich_fk' => $id_fich,
                        'id_horario_fk' => $id_horario_fk,
                    ]);
                }
            }
        });

        return redirect()
            ->route('ficha.index')
            ->with('success', 'Horario de la ficha actualizado correctamente.');
    }

    public function destroy($id)
    {
        DB::table('ficha')->where('id_fich', $id)->delete();

        return redirect()
            ->route('ficha.index')
            ->with('success', 'Ficha eliminada correctamente junto con sus registros asociados.');
    }
}
