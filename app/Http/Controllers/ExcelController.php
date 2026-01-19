<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Programa;
use App\Models\Competencia;
use App\Models\Resultado;
use DB;
use Illuminate\Support\Facades\Schema;

class ExcelController extends Controller
{
    public function showUploadForm()
    {
        return view('excel.upload');
    }
    
    public function preview(Request $request)
    {
        // Validar archivo
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:10240'
        ]);
        
        $file = $request->file('excel_file');
        
        // Crear carpeta temp si no existe
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        // Guardar archivo temporal con nombre único
        $fileName = time() . '_' . $file->getClientOriginalName();
        $fullPath = $tempDir . '/' . $fileName;
        $file->move($tempDir, $fileName);
        
        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            
            // Leer datos del programa desde fila 4 (columnas A-D)
            $codigo = trim($sheet->getCell('A4')->getValue());
            $nombre = trim($sheet->getCell('B4')->getValue());
            $nivel = trim($sheet->getCell('C4')->getValue());
            $version = trim($sheet->getCell('D4')->getValue());
            
            // Validar que los campos del programa no estén vacíos
            if (empty($nivel) || empty($nombre) || empty($codigo) || empty($version)) {
                return redirect()->route('excel.upload')
                    ->with('error', '⚠️ Los datos del programa son incompletos. Verifica nivel, nombre, código y versión en la fila 4.');
            }
            
            // Verificar si el programa ya existe
            $programaExiste = Programa::where('id_prog', $codigo)->exists();
            
            if ($programaExiste) {
                return redirect()->route('excel.upload')
                    ->with('error', '⚠️ El programa con código ' . $codigo . ' ya está registrado.');
            }
            
            // Leer competencias y resultados (empezar desde fila 4)
            $filaMaxima = $sheet->getHighestRow();
            $competencias = [];
            $competenciaActual = null; // Para manejar celdas fusionadas
            
            $usaTablaDuracion = Schema::hasTable('duracion');
            $usaMatrsExt = Schema::hasTable('matrs_ext');

            for ($fila = 4; $fila <= $filaMaxima; $fila++) {
                // Leer datos de competencia
                $nombre_comp = trim($sheet->getCell("E$fila")->getValue());
                $cod_comp = trim($sheet->getCell("F$fila")->getValue());
                $duracion = $sheet->getCell("G$fila")->getValue();
                
                // Si hay datos de competencia, actualizar la competencia actual
                if (!empty($cod_comp)) {
                    $competenciaActual = [
                        'codigo' => $cod_comp,
                        'nombre' => $nombre_comp,
                        'duracion' => $duracion
                    ];
                    
                    // Inicializar array de resultados si no existe
                    if (!isset($competencias[$cod_comp])) {
                        $competencias[$cod_comp] = [
                            'codigo' => $cod_comp,
                            'nombre' => $nombre_comp,
                            'duracion' => $duracion,
                            'resultados' => []
                        ];
                    }
                }
                
                // Leer datos del resultado (columnas H-L, calcular M automáticamente)
                $resultado = trim($sheet->getCell("H$fila")->getValue());
                $hora_max = $sheet->getCell("I$fila")->getCalculatedValue() ?: 0;
                $hora_min = round($sheet->getCell("J$fila")->getCalculatedValue() ?: 0);
                $trimestre = $sheet->getCell("K$fila")->getCalculatedValue() ?: 0;
                $hora_sema_raw = $sheet->getCell("L$fila")->getCalculatedValue();
                $hora_sema = ($hora_sema_raw === null || $hora_sema_raw === '') ? null : $hora_sema_raw;
                $hora_trim = $hora_sema !== null ? ($hora_sema * 11) : null; // Cálculo automático sólo si hay H/Sem
                
                // Si hay resultado y tenemos una competencia activa, agregarlo
                if (!empty($resultado) && $competenciaActual !== null) {
                    $competencias[$competenciaActual['codigo']]['resultados'][] = [
                        'nombre' => $resultado,
                        'hora_max' => $hora_max,
                        'hora_min' => $hora_min,
                        'trimestre' => $trimestre,
                        'hora_sema' => $hora_sema,
                        'hora_trim' => $hora_trim
                    ];
                }
            }
            
            return view('excel.preview', compact('nivel', 'nombre', 'codigo', 'version', 'competencias', 'fileName'));
            
        } catch (\Exception $e) {
            return redirect()->route('excel.upload')
                ->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }
    
    public function process(Request $request)
    {
        $fileName = $request->input('file_name');
        $fullPath = storage_path('app/temp/' . $fileName);
        
        // Verificar que el archivo exista
        if (!file_exists($fullPath)) {
            return redirect()->route('excel.upload')
                ->with('error', 'El archivo temporal no existe. Por favor, vuelve a cargar el archivo.');
        }
        
        try {
            DB::beginTransaction();
            
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();

            // Detectar tablas auxiliares presentes en la BD
            $usaTablaDuracion = Schema::hasTable('duracion');
            $usaMatrsExt = Schema::hasTable('matrs_ext');
            
            // Insertar programa (fila 4, columnas A-D)
            $programa = new Programa();
            $programa->id_prog = trim($sheet->getCell('A4')->getValue());
            $programa->nombre = trim($sheet->getCell('B4')->getValue());
            $programa->version = trim($sheet->getCell('D4')->getValue());
            $programa->nivel = trim($sheet->getCell('C4')->getValue());
            $programa->cant_trim = 0;
            $programa->save();
            
            // Procesar competencias y resultados (desde fila 4)
            $filaMaxima = $sheet->getHighestRow();
            $competenciasInsertadas = [];
            $competenciaActual = null;
            
            for ($fila = 4; $fila <= $filaMaxima; $fila++) {
                // Leer datos de competencia
                $nombre_comp = trim($sheet->getCell("E$fila")->getValue());
                $cod_comp = trim($sheet->getCell("F$fila")->getValue());
                
                // Si hay código de competencia, actualizar la competencia actual
                if (!empty($cod_comp)) {
                    $competenciaActual = $cod_comp;

                    // Crear o reutilizar competencia globalmente por código y (si existe) enlazar vía pivote
                    if (!isset($competenciasInsertadas[$cod_comp])) {
                        $compDb = Competencia::where('cod_comp', $cod_comp)->first();
                        if (!$compDb) {
                            $compDb = new Competencia();
                            $compDb->cod_comp = $cod_comp;
                            $compDb->nombre = $nombre_comp;
                            $compDb->duracion_hora = $sheet->getCell("G$fila")->getValue();
                            // mantener id_prog_fk del primer programa por compatibilidad
                            $compDb->id_prog_fk = $programa->id_prog;
                            $compDb->save();
                        }

                        // Enlazar en tabla pivote si existe; si no, seguimos usando id_prog_fk legacy
                        if (Schema::hasTable('programa_competencia')) {
                            $pivotExiste = DB::table('programa_competencia')
                                ->where('id_prog_fk', $programa->id_prog)
                                ->where('cod_comp_fk', $cod_comp)
                                ->exists();
                            if (!$pivotExiste) {
                                DB::table('programa_competencia')->insert([
                                    'id_prog_fk' => $programa->id_prog,
                                    'cod_comp_fk' => $cod_comp
                                ]);
                            }
                        } else {
                            // Si el modelo fue creado antes, ya tiene id_prog_fk asignado; si existía, actualizar propietario si no coincide
                            if ($compDb->id_prog_fk !== $programa->id_prog) {
                                // No cambiamos propietario para no romper referencias, solo dejamos el existente
                            }
                        }

                        $competenciasInsertadas[$cod_comp] = true;
                    }
                }
                
                // Insertar resultado (usar competencia actual si la celda está fusionada)
                $nombre_resultado = trim($sheet->getCell("H$fila")->getValue());
                if (!empty($nombre_resultado) && $competenciaActual !== null) {
                    // Normalizar nombre: trim + colapsar espacios
                    $nombreNormalizado = preg_replace('/\s+/u', ' ', trim($nombre_resultado));
                    $nombreCorto = mb_substr($nombreNormalizado, 0, 255);
                    // Obtener o crear resultado por competencia + nombre
                    $resultado = Resultado::where('cod_comp_fk', $competenciaActual)
                        ->where('nombre', $nombreCorto)
                        ->first();
                    if (!$resultado) {
                        $resultado = new Resultado();
                        $resultado->cod_resu = 0;
                        $resultado->nombre = $nombreCorto;
                        $resultado->cod_comp_fk = $competenciaActual;
                        if (!$usaTablaDuracion) {
                            // Esquema legado: guardar horas en la misma tabla
                            $resultado->duracion_hora_max = $sheet->getCell("I$fila")->getCalculatedValue() ?: 0;
                            $resultado->duracion_hora_min = round($sheet->getCell("J$fila")->getCalculatedValue() ?: 0);
                            $resultado->trim_prog = $sheet->getCell("K$fila")->getCalculatedValue() ?: 0;
                            $l_val2 = $sheet->getCell("L$fila")->getCalculatedValue();
                            $resultado->hora_sema_programar = ($l_val2 === null || $l_val2 === '') ? null : $l_val2;
                            $resultado->hora_trim_programar = ($resultado->hora_sema_programar !== null)
                                ? ($resultado->hora_sema_programar * 11)
                                : null;
                        }
                        $resultado->save();
                    }

                    // Enlazar en matrs_ext para este programa-competencia-resultado
                    if ($usaMatrsExt) {
                        $existeMx = DB::table('matrs_ext')
                            ->where('cod_prog_fk', $programa->id_prog)
                            ->where('cod_com_fk', $competenciaActual)
                            ->where('id_resu_fk', $resultado->id_resu)
                            ->exists();
                        if (!$existeMx) {
                            DB::table('matrs_ext')->insert([
                                'cod_prog_fk' => $programa->id_prog,
                                'cod_com_fk' => $competenciaActual,
                                'id_resu_fk' => $resultado->id_resu,
                            ]);
                        }
                    }

                    // Guardar horas específicas de esta matriz en tabla 'duracion' si existe
                    if ($usaTablaDuracion) {
                        $d_i = $sheet->getCell("I$fila")->getCalculatedValue() ?: 0;
                        $d_j = round($sheet->getCell("J$fila")->getCalculatedValue() ?: 0);
                        $d_k = $sheet->getCell("K$fila")->getCalculatedValue() ?: 0;
                        $l_val = $sheet->getCell("L$fila")->getCalculatedValue();
                        $h_sem = ($l_val === null || $l_val === '') ? null : $l_val;
                        $h_trim = ($h_sem !== null) ? ($h_sem * 11) : null;

                        $payload = [
                            'duracion_hora_max' => $d_i,
                            'duracion_hora_min' => $d_j,
                            'trim_prog' => $d_k,
                            'hora_sema_programar' => $h_sem,
                            'hora_trim_programar' => $h_trim,
                            'cod_resu_fk' => $resultado->id_resu,
                        ];
                        // Si existe columna id_prog_fk en duracion, la registramos
                        if (Schema::hasColumn('duracion', 'id_prog_fk')) {
                            $payload['id_prog_fk'] = $programa->id_prog;
                        }
                        // Insertar siempre la fila de duracion, aun si viene vacía,
                        // para permitir completar horas luego desde el sistema.
                        DB::table('duracion')->insert($payload);
                    }
                }
            }
            
            DB::commit();
            
            // Eliminar archivo temporal
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            return redirect()->route('matriz.show', ['id_prog' => $programa->id_prog])
                ->with('success', '✅ Programa cargado exitosamente: ' . $programa->nombre);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('excel.upload')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}
