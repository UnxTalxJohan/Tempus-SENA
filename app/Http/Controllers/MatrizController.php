<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programa;
use App\Models\Competencia;
use App\Models\Resultado;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Helpers\RouteHasher;

class MatrizController extends Controller
{
    public function index()
    {
        $programas = Programa::orderBy('nombre')->get();
        // Agregar hash a cada programa
        $programas->each(function($programa) {
            $programa->hash = RouteHasher::encode($programa->id_prog);
        });
        return view('matriz.index', compact('programas'));
    }
    
    public function show($hash)
    {
        $id_prog = RouteHasher::decode($hash);
        
        if (!$id_prog) {
            abort(404, 'Programa no encontrado');
        }
        
        $programa = Programa::where('id_prog', $id_prog)->firstOrFail();
        $programa->hash = $hash;
        
                // Obtener competencias: usar pivote si existe; si no, esquema antiguo por id_prog_fk
                if (Schema::hasTable('programa_competencia')) {
                    $competencias = Competencia::whereIn('cod_comp', function($q) use ($id_prog) {
                        $q->select('cod_comp_fk')
                          ->from('programa_competencia')
                          ->where('id_prog_fk', $id_prog);
                    })
                    ->orderBy('cod_comp')
                    ->get();
                } else {
                    $competencias = Competencia::where('id_prog_fk', $id_prog)
                    ->orderBy('cod_comp')
                    ->get();
                }
        
        $usaDuracion = \Illuminate\Support\Facades\Schema::hasTable('duracion');
        $usaMatrsExt = \Illuminate\Support\Facades\Schema::hasTable('matrs_ext');
        foreach ($competencias as $competencia) {
            if ($usaMatrsExt) {
                // Sólo resultados vinculados a este programa y competencia
                $resultados = Resultado::whereIn('id_resu', function($q) use ($id_prog, $competencia) {
                        $q->select('id_resu_fk')
                          ->from('matrs_ext')
                          ->where('cod_prog_fk', $id_prog)
                          ->where('cod_com_fk', $competencia->cod_comp);
                    })
                    ->orderBy('id_resu')
                    ->get();
            } else {
                // Esquema legado: listar todos por competencia
                $resultados = Resultado::where('cod_comp_fk', $competencia->cod_comp)
                    ->orderBy('id_resu')
                    ->get();
            }
            if ($usaDuracion) {
                // Mapear duraciones por resultado para este programa
                foreach ($resultados as $res) {
                    // Si la tabla duracion tiene id_prog_fk, filtrar por programa. Si no, usar join con matrs_ext
                    if (\Illuminate\Support\Facades\Schema::hasColumn('duracion', 'id_prog_fk')) {
                        $dur = \DB::table('duracion')
                            ->where('cod_resu_fk', $res->id_resu)
                            ->where('id_prog_fk', $id_prog)
                            ->orderByDesc('id_dura')
                            ->first();
                    } else {
                        $dur = \DB::table('duracion as d')
                            ->join('matrs_ext as m', 'm.id_resu_fk', '=', 'd.cod_resu_fk')
                            ->where('m.cod_prog_fk', $id_prog)
                            ->where('m.cod_com_fk', $competencia->cod_comp)
                            ->where('d.cod_resu_fk', $res->id_resu)
                            ->orderByDesc('d.id_dura')
                            ->select('d.*')
                            ->first();
                    }
                    $res->duracion_hora_max = $dur->duracion_hora_max ?? null;
                    $res->duracion_hora_min = $dur->duracion_hora_min ?? null;
                    $res->trim_prog = $dur->trim_prog ?? null;
                    $res->hora_sema_programar = $dur->hora_sema_programar ?? null;
                    $res->hora_trim_programar = $dur->hora_trim_programar ?? null;
                }
            }
            $competencia->resultados = $resultados;
        }
        
        return view('matriz.show', compact('programa', 'competencias'));
    }
    
    public function exportar($hash)
    {
        $id_prog = RouteHasher::decode($hash);
        
        if (!$id_prog) {
            abort(404, 'Programa no encontrado');
        }
        
        $programa = Programa::where('id_prog', $id_prog)->firstOrFail();
        
                if (Schema::hasTable('programa_competencia')) {
                    $competencias = Competencia::whereIn('cod_comp', function($q) use ($id_prog) {
                        $q->select('cod_comp_fk')
                          ->from('programa_competencia')
                          ->where('id_prog_fk', $id_prog);
                    })
                    ->orderBy('cod_comp')
                    ->get();
                } else {
                    $competencias = Competencia::where('id_prog_fk', $id_prog)
                    ->orderBy('cod_comp')
                    ->get();
                }
        
        $usaDuracion = \Illuminate\Support\Facades\Schema::hasTable('duracion');
        $usaMatrsExt = \Illuminate\Support\Facades\Schema::hasTable('matrs_ext');
        foreach ($competencias as $competencia) {
            if ($usaMatrsExt) {
                $resultados = Resultado::whereIn('id_resu', function($q) use ($id_prog, $competencia) {
                        $q->select('id_resu_fk')
                          ->from('matrs_ext')
                          ->where('cod_prog_fk', $id_prog)
                          ->where('cod_com_fk', $competencia->cod_comp);
                    })
                    ->orderBy('id_resu')
                    ->get();
            } else {
                $resultados = Resultado::where('cod_comp_fk', $competencia->cod_comp)
                    ->orderBy('id_resu')
                    ->get();
            }
            if ($usaDuracion) {
                foreach ($resultados as $res) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('duracion', 'id_prog_fk')) {
                        $dur = \DB::table('duracion')
                            ->where('cod_resu_fk', $res->id_resu)
                            ->where('id_prog_fk', $id_prog)
                            ->orderByDesc('id_dura')
                            ->first();
                    } else {
                        $dur = \DB::table('duracion as d')
                            ->join('matrs_ext as m', 'm.id_resu_fk', '=', 'd.cod_resu_fk')
                            ->where('m.cod_prog_fk', $id_prog)
                            ->where('m.cod_com_fk', $competencia->cod_comp)
                            ->where('d.cod_resu_fk', $res->id_resu)
                            ->orderByDesc('d.id_dura')
                            ->select('d.*')
                            ->first();
                    }
                    $res->duracion_hora_max = $dur->duracion_hora_max ?? null;
                    $res->duracion_hora_min = $dur->duracion_hora_min ?? null;
                    $res->trim_prog = $dur->trim_prog ?? null;
                    $res->hora_sema_programar = $dur->hora_sema_programar ?? null;
                    $res->hora_trim_programar = $dur->hora_trim_programar ?? null;
                }
            }
            $competencia->resultados = $resultados;
        }
        
        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // FILA 1: Encabezados principales (fusionados hasta fila 3)
        $sheet->setCellValue('A1', 'Codigo del programa');
        $sheet->setCellValue('B1', 'Programa de formacion');
        $sheet->setCellValue('C1', 'Nivel');
        $sheet->setCellValue('D1', 'Version');
        $sheet->setCellValue('E1', 'Nombre de la competencia Laboral/ Unidad de competencia /NCL /UC');
        $sheet->setCellValue('F1', 'Código');
        $sheet->setCellValue('G1', 'Duración de la Competencia en Horas');
        $sheet->setCellValue('H1', 'RESULTADOS DE APRENDIZAJE');
        $sheet->setCellValue('I1', 'DURACIÓN HORAS POR RESULTADO DE APRENDIZAJE(horas maximas a programar)');
        $sheet->setCellValue('J1', 'DURACIÓN HORAS POR RESULTADO DE APRENDIZAJE(horas minimo a programar)');
        $sheet->setCellValue('K1', 'TRIMESTRE A PROGRAMAR');
        $sheet->setCellValue('L1', 'HORAS A LA SEMANA A PROGRAMAR');
        $sheet->setCellValue('M1', 'HORAS DEL TRIMESTRE A PROGRAMAR');
        
        // Fusionar encabezados desde fila 1 hasta fila 3
        $sheet->mergeCells('A1:A3');
        $sheet->mergeCells('B1:B3');
        $sheet->mergeCells('C1:C3');
        $sheet->mergeCells('D1:D3');
        $sheet->mergeCells('E1:E3');
        $sheet->mergeCells('F1:F3');
        $sheet->mergeCells('G1:G3');
        $sheet->mergeCells('H1:H3');
        $sheet->mergeCells('I1:I3');
        $sheet->mergeCells('J1:J3');
        $sheet->mergeCells('K1:K3');
        $sheet->mergeCells('L1:L3');
        $sheet->mergeCells('M1:M3');
        
        // Estilos de encabezados
        $sheet->getStyle('A1:D3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4C7E7');
        $sheet->getStyle('E1:E3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
        $sheet->getStyle('F1:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
        $sheet->getStyle('G1:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('A9D08E');
        $sheet->getStyle('H1:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6E0B4');
        $sheet->getStyle('I1:I3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6E0B4');
        $sheet->getStyle('J1:J3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6E0B4');
        $sheet->getStyle('K1:K3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6E0B4');
        $sheet->getStyle('L1:L3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getStyle('M1:M3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        
        $sheet->getStyle('A1:M3')->getFont()->setBold(true);
        $sheet->getStyle('A1:M3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        
        // FILA 3: Incluida en encabezados (con dropdowns al subir)
        // FILA 4: Datos del programa + primera competencia/resultado
        $fila = 4;
        $primeraFila = true;
        
        foreach ($competencias as $competencia) {
            $filaInicio = $fila;
            $cantResultados = count($competencia->resultados);
            
            foreach ($competencia->resultados as $index => $resultado) {
                // Solo en la PRIMERA fila (fila 4): escribir datos del programa
                if ($primeraFila && $index === 0) {
                    $sheet->setCellValue("A$fila", $programa->id_prog);
                    $sheet->setCellValue("B$fila", $programa->nombre);
                    $sheet->setCellValue("C$fila", $programa->nivel);
                    $sheet->setCellValue("D$fila", $programa->version);
                    $primeraFila = false;
                }
                
                // Primera fila de la competencia: escribir datos de competencia
                if ($index === 0) {
                    $sheet->setCellValue("E$fila", $competencia->nombre);
                    $sheet->setCellValue("F$fila", $competencia->cod_comp);
                    $sheet->setCellValue("G$fila", $competencia->duracion_hora);
                }
                
                // Datos del resultado (siempre)
                $sheet->setCellValue("H$fila", $resultado->nombre);
                $sheet->setCellValue("I$fila", $resultado->duracion_hora_max);
                $sheet->setCellValue("J$fila", $resultado->duracion_hora_min);
                $sheet->setCellValue("K$fila", $resultado->trim_prog);
                $sheet->setCellValue("L$fila", $resultado->hora_sema_programar);
                $sheet->setCellValue("M$fila", $resultado->hora_trim_programar);
                
                $fila++;
            }
            
            // Combinar celdas de competencia verticalmente si tiene múltiples resultados
            $filaFin = $fila - 1;
            if ($filaFin > $filaInicio) {
                $sheet->mergeCells("E$filaInicio:E$filaFin");
                $sheet->mergeCells("F$filaInicio:F$filaFin");
                $sheet->mergeCells("G$filaInicio:G$filaFin");
            }
        }
        
        // Ajustar anchos de columna
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(45);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(50);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(15);
        
        // Altura de filas de encabezado
        $sheet->getRowDimension(1)->setRowHeight(50);
        $sheet->getRowDimension(2)->setRowHeight(50);
        $sheet->getRowDimension(3)->setRowHeight(50);
        
        // Bordes en toda la tabla
        $sheet->getStyle("A1:M" . ($fila - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        // Alineación vertical al centro para datos
        $sheet->getStyle("A4:M" . ($fila - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        
        // Descargar con nombre: codigo_nombrePrograma.xlsx
        $nombreLimpio = preg_replace('/[^A-Za-z0-9\-\_]/', '_', $programa->nombre);
        $fileName = $programa->id_prog . '_' . $nombreLimpio . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function updateCompetencia(Request $request, $cod_comp)
    {
        $data = $request->validate([
            'nombre' => 'required|string|min:3',
            'duracion_hora' => 'sometimes|integer|min:0'
        ]);

        $competencia = Competencia::where('cod_comp', $cod_comp)->firstOrFail();
        // No se permite editar el código
        $competencia->nombre = $data['nombre'];
        // Solo actualizar duración si explícitamente se envía (por ahora la UI no la envía)
        if (array_key_exists('duracion_hora', $data)) {
            $competencia->duracion_hora = $data['duracion_hora'];
        }
        $competencia->save();

        return response()->json(['ok' => true, 'competencia' => $competencia]);
    }

    public function updateResultado(Request $request, $id_resu)
    {
        $data = $request->validate([
            'duracion_hora_max' => 'required|integer|min:0',
            'duracion_hora_min' => 'required|integer|min:0',
            'hora_sema_programar' => 'nullable|integer|min:0',
            'hora_trim_programar' => 'nullable|integer|min:0',
            'trim_prog' => 'required|integer|min:1|max:7',
            'id_prog' => 'nullable|integer' // id del programa desde la vista
        ]);

        // Insertar/actualizar horas en tabla duracion por programa
        $usaDuracion = \Illuminate\Support\Facades\Schema::hasTable('duracion');
        if (!$usaDuracion) {
            return response()->json(['ok' => false, 'msg' => 'Tabla duracion no disponible']);
        }

        $idProg = $data['id_prog'] ?? null;
        if (!$idProg) {
            // Si no llega en el request, inferir por matrs_ext (contexto del programa en la vista)
            $idProg = \DB::table('matrs_ext')->where('id_resu_fk', $id_resu)->value('cod_prog_fk');
        }

        $payload = [
            'duracion_hora_max' => $data['duracion_hora_max'],
            'duracion_hora_min' => $data['duracion_hora_min'],
            'trim_prog' => $data['trim_prog'],
            'hora_sema_programar' => $data['hora_sema_programar'] ?? null,
            'hora_trim_programar' => $data['hora_trim_programar'] ?? (($data['hora_sema_programar'] ?? null) !== null ? (($data['hora_sema_programar']) * 11) : null),
            'cod_resu_fk' => $id_resu,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('duracion', 'id_prog_fk') && $idProg) {
            $payload['id_prog_fk'] = $idProg;
            // Insertar un nuevo registro (histórico) para este programa y resultado
            \DB::table('duracion')->insert($payload);
        } else {
            // Esquema legado sin id_prog_fk: insert simple (afectará globalmente)
            \DB::table('duracion')->insert($payload);
        }

        return response()->json(['ok' => true]);
    }
}

