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
    
    /**
     * Previsualización múltiple: acepta hasta 5 archivos, parsea cada uno y
     * prepara un resumen por archivo. Los inválidos se registran en sesión
     * como logs y se excluyen del flujo de carga.
     */
    public function previewMulti(Request $request)
    {
        $request->validate([
            'excel_files'   => 'required',
            'excel_files.*' => 'mimes:xlsx,xls|max:10240'
        ]);

        $files = $request->file('excel_files');
        if (count($files) > 5) {
            return redirect()->route('excel.upload')
                ->with('error', 'Solo se permiten hasta 5 archivos por carga.');
        }

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

        $previews = [];
        $seenCodes = [];
        $logs = session('upload_logs', []);

        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $fullPath = $tempDir . '/' . $fileName;
            $file->move($tempDir, $fileName);
            try {
                $parsed = $this->parseExcelForPreview($fullPath);
                // Validación de programa duplicado
                $programaExiste = Programa::where('id_prog', $parsed['codigo'])->exists();
                if ($programaExiste) {
                    $logs[] = "Programa ${parsed['codigo']} ya existe. Archivo: {$file->getClientOriginalName()}";
                    $previews[] = [ 'ok' => false, 'fileName' => $fileName, 'originalName' => $file->getClientOriginalName(), 'error' => 'Programa ya registrado' ];
                    continue;
                }
                // Duplicado dentro del lote de subida (mismo código de programa)
                if (isset($seenCodes[$parsed['codigo']])) {
                    $logs[] = "Código repetido en esta carga: {$parsed['codigo']} (archivo: {$file->getClientOriginalName()})";
                    $previews[] = [
                        'ok' => false,
                        'duplicate' => true,
                        'fileName' => $fileName,
                        'originalName' => $file->getClientOriginalName(),
                        'error' => 'Código de programa repetido en esta carga',
                    ];
                } else {
                    $previews[] = array_merge($parsed, [
                        'ok' => true,
                        'duplicate' => false,
                        'fileName' => $fileName,
                        'originalName' => $file->getClientOriginalName(),
                    ]);
                    $seenCodes[$parsed['codigo']] = true;
                }
            } catch (\Exception $e) {
                $logs[] = 'Error al leer ' . $file->getClientOriginalName() . ': ' . $e->getMessage();
                $previews[] = [ 'ok' => false, 'fileName' => $fileName, 'originalName' => $file->getClientOriginalName(), 'error' => $e->getMessage() ];
            }
        }

        session(['upload_logs' => $logs, 'previews_multi' => $previews]);
        return view('excel.preview_multi', compact('previews'));
    }

    /**
     * Previsualiza un archivo ya subido a temp dado su nombre.
     */
    public function previewFile(Request $request)
    {
        $request->validate(['file_name' => 'required|string']);
        $fullPath = storage_path('app/temp/' . $request->input('file_name'));
        if (!file_exists($fullPath)) {
            return redirect()->route('excel.upload')->with('error', 'El archivo temporal no existe.');
        }
        try {
            $data = $this->parseExcelForPreview($fullPath);
            $fileName = $request->input('file_name');
            // Detectar si este archivo fue marcado como duplicado en la lista múltiple
            $isDuplicate = false;
            $multi = session('previews_multi', []);
            foreach ($multi as $p) {
                if (($p['fileName'] ?? '') === $fileName) {
                    $isDuplicate = !empty($p['duplicate']);
                    break;
                }
            }
            return view('excel.preview', $data + compact('fileName', 'isDuplicate'));
        } catch (\Exception $e) {
            $logs = session('upload_logs', []);
            $logs[] = 'Error al previsualizar archivo: ' . $e->getMessage();
            session(['upload_logs' => $logs]);
            return redirect()->route('excel.upload')->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la lista de previsualización múltiple desde sesión, sin re-subir archivos.
     */
    public function previewMultiView()
    {
        $previews = session('previews_multi', []);
        if (empty($previews)) {
            return redirect()->route('excel.upload')->with('error', 'No hay una lista de previsualización activa. Carga archivos nuevamente.');
        }
        return view('excel.preview_multi', compact('previews'));
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
            $data = $this->parseExcelForPreview($fullPath);
            return view('excel.preview', $data + compact('fileName'));
            
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
            $programa = $this->processSingleExcel($fullPath);
            return redirect()->route('matriz.show', ['id_prog' => $programa->id_prog])
                ->with('success', '✅ Programa cargado exitosamente: ' . $programa->nombre);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('excel.upload')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Procesamiento múltiple: carga todos los archivos válidos.
     */
    public function processMulti(Request $request)
    {
        $request->validate(['file_names' => 'required|array']);
        $names = $request->input('file_names', []);
        $ok = 0; $fail = 0; $logs = session('upload_logs', []);
        $lastProg = null;
        foreach ($names as $fileName) {
            $fullPath = storage_path('app/temp/' . $fileName);
            if (!file_exists($fullPath)) { $fail++; $logs[] = 'Temp no encontrado: ' . $fileName; continue; }
            try {
                $prog = $this->processSingleExcel($fullPath);
                $ok++; $lastProg = $prog;
            } catch (\Exception $e) {
                $fail++; $logs[] = 'Error procesando ' . $fileName . ': ' . $e->getMessage();
            }
        }
        session(['upload_logs' => $logs]);
        if ($ok > 0 && $lastProg) {
            return redirect()->route('matriz.show', ['id_prog' => $lastProg->id_prog])
                ->with('success', "✅ Cargados ${ok} archivos. ${fail} con errores.");
        }
        return redirect()->route('excel.upload')->with('error', "No se pudo cargar ninguno. Errores: ${fail}");
    }

    /**
     * Helper: parsea un Excel y devuelve datos para preview.
     */
    private function parseExcelForPreview(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        // Validación de plantilla (encabezados principales)
        $vh = $this->validateMatrixHeaders($sheet);
        if (!$vh['ok']) {
            $msg = 'El archivo no coincide con la plantilla de la matriz';
            if (!empty($vh['missing'])) { $msg .= ' (faltan: ' . implode(', ', $vh['missing']) . ')'; }
            throw new \RuntimeException($msg . '.');
        }
        $codigo = trim($sheet->getCell('A4')->getValue());
        $nombre = trim($sheet->getCell('B4')->getValue());
        $nivel = trim($sheet->getCell('C4')->getValue());
        $version = trim($sheet->getCell('D4')->getValue());
        if (empty($nivel) || empty($nombre) || empty($codigo) || empty($version)) {
            throw new \RuntimeException('Los datos del programa son incompletos (fila 4).');
        }
        $filaMaxima = $sheet->getHighestRow();
        $competencias = [];
        $competenciaActual = null;
        for ($fila = 4; $fila <= $filaMaxima; $fila++) {
            $nombre_comp = trim($sheet->getCell("E$fila")->getValue());
            $cod_comp = trim($sheet->getCell("F$fila")->getValue());
            $duracion = $sheet->getCell("G$fila")->getValue();
            if (!empty($cod_comp)) {
                $competenciaActual = [ 'codigo' => $cod_comp, 'nombre' => $nombre_comp, 'duracion' => $duracion ];
                if (!isset($competencias[$cod_comp])) {
                    $competencias[$cod_comp] = [ 'codigo' => $cod_comp, 'nombre' => $nombre_comp, 'duracion' => $duracion, 'resultados' => [] ];
                }
            }
            $resultado = trim($sheet->getCell("H$fila")->getValue());
            $hora_max = $sheet->getCell("I$fila")->getCalculatedValue() ?: 0;
            $hora_min = round($sheet->getCell("J$fila")->getCalculatedValue() ?: 0);
            $trimestre = $sheet->getCell("K$fila")->getCalculatedValue() ?: 0;
            $hora_sema_raw = $sheet->getCell("L$fila")->getCalculatedValue();
            $hora_sema = ($hora_sema_raw === null || $hora_sema_raw === '') ? null : $hora_sema_raw;
            $hora_trim = $hora_sema !== null ? ($hora_sema * 11) : null;
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
        return compact('nivel','nombre','codigo','version','competencias');
    }

    /**
     * Valida que las primeras filas contengan los encabezados esperados de la plantilla.
     * Se hace comparación flexible (minúsculas, sin acentos y por patrones aproximados).
     */
    private function validateMatrixHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $normalize = function($s){
            $s = trim((string)$s);
            if ($s === '') return '';
            $s = mb_strtolower($s, 'UTF-8');
            // eliminar acentos de forma segura sin depender solo de iconv
            if (function_exists('transliterator_transliterate')) {
                $s = transliterator_transliterate('Any-Latin; Latin-ASCII', $s);
            } else if (function_exists('iconv')) {
                $conv = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
                if ($conv !== false) $s = $conv;
            }
            $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
            $s = preg_replace('/\s+/', ' ', $s);
            return trim($s);
        };

        $patterns = [
            'A' => '/codigo.*programa/',
            'B' => '/programa.*formacion/',
            'C' => '/nivel/',
            'D' => '/version/',
            'E' => '/nombre.*competencia|unidad.*competencia|ncl|uc/',
            'F' => '/^codigo$/',
            'G' => '/duracion.*competencia.*hora/',
            'H' => '/resultados.*aprendizaje/',
            'I' => '/horas.*(maxim|maxima|maximas)/',
            'J' => '/horas.*(minim|minima|minimas)/',
            'K' => '/trimestre/',
            'L' => '/semana.*programar/',
            'M' => '/trimestre.*programar/'
        ];

        $found = 0; $missing = [];
        // esenciales mínimas para permitir carga
        $required = ['A','B','D','H']; // relajamos: no exigir C (Nivel)
        foreach ($patterns as $col => $regex) {
            $okCol = false;
            for ($r = 1; $r <= 5; $r++) {
                $val = $normalize($sheet->getCell($col.$r)->getValue());
                if ($val !== '' && preg_match($regex, $val)) { $okCol = true; break; }
            }
            if ($okCol) $found++; else if (in_array($col, $required, true)) $missing[] = $col;
        }
        // Al menos 7 columnas deben coincidir para considerarlo compatible
        $ok = (empty($missing) && $found >= 7);
        return ['ok' => $ok, 'missing' => $missing, 'found' => $found];
    }

    /**
     * Helper: procesa y guarda un Excel en BD (reutiliza la lógica original).
     * Retorna el `Programa` creado.
     */
    private function processSingleExcel(string $fullPath): Programa
    {
        DB::beginTransaction();
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $usaTablaDuracion = Schema::hasTable('duracion');
        $usaMatrsExt = Schema::hasTable('matrs_ext');
        $programa = new Programa();
        $programa->id_prog = trim($sheet->getCell('A4')->getValue());
        $programa->nombre = trim($sheet->getCell('B4')->getValue());
        $programa->version = trim($sheet->getCell('D4')->getValue());
        $programa->nivel = trim($sheet->getCell('C4')->getValue());
        $programa->cant_trim = 0;
        $programa->save();
        $filaMaxima = $sheet->getHighestRow();
        $competenciasInsertadas = [];
        $competenciaActual = null;
        for ($fila = 4; $fila <= $filaMaxima; $fila++) {
            $nombre_comp = trim($sheet->getCell("E$fila")->getValue());
            $cod_comp = trim($sheet->getCell("F$fila")->getValue());
            if (!empty($cod_comp)) {
                $competenciaActual = $cod_comp;
                if (!isset($competenciasInsertadas[$cod_comp])) {
                    $compDb = Competencia::where('cod_comp', $cod_comp)->first();
                    if (!$compDb) {
                        $compDb = new Competencia();
                        $compDb->cod_comp = $cod_comp;
                        $compDb->nombre = $nombre_comp;
                        $compDb->duracion_hora = $sheet->getCell("G$fila")->getValue();
                        $compDb->id_prog_fk = $programa->id_prog;
                        $compDb->save();
                    }
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
                    }
                    $competenciasInsertadas[$cod_comp] = true;
                }
            }
            $nombre_resultado = trim($sheet->getCell("H$fila")->getValue());
            if (!empty($nombre_resultado) && $competenciaActual !== null) {
                $nombreNormalizado = preg_replace('/\s+/u', ' ', trim($nombre_resultado));
                $nombreCorto = mb_substr($nombreNormalizado, 0, 255);
                $resultado = Resultado::where('cod_comp_fk', $competenciaActual)
                    ->where('nombre', $nombreCorto)
                    ->first();
                if (!$resultado) {
                    $resultado = new Resultado();
                    $resultado->cod_resu = 0;
                    $resultado->nombre = $nombreCorto;
                    $resultado->cod_comp_fk = $competenciaActual;
                    if (!$usaTablaDuracion) {
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
                    if (Schema::hasColumn('duracion', 'id_prog_fk')) {
                        $payload['id_prog_fk'] = $programa->id_prog;
                    }
                    DB::table('duracion')->insert($payload);
                }
            }
        }
        DB::commit();
        if (file_exists($fullPath)) unlink($fullPath);
        return $programa;
    }
}
