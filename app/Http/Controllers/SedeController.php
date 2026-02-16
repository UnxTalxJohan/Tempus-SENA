<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SedeController extends Controller
{
    public function index()
    {
        $sedes = DB::table('sede')
            ->leftJoin('ambiente', 'sede.cod_sede', '=', 'ambiente.cod_sede_fk')
            ->select('sede.cod_sede', 'sede.nom_sede', DB::raw('COUNT(ambiente.cod_amb) as total_ambientes'))
            ->groupBy('sede.cod_sede', 'sede.nom_sede')
            ->orderBy('sede.nom_sede')
            ->get();

        $detalle = DB::table('sede')
            ->leftJoin('ambiente', 'sede.cod_sede', '=', 'ambiente.cod_sede_fk')
            ->select('sede.cod_sede', 'sede.nom_sede', 'ambiente.cod_amb', 'ambiente.denominacion')
            ->orderBy('sede.nom_sede')
            ->orderBy('ambiente.denominacion')
            ->get()
            ->groupBy('cod_sede');

        return view('sede.index', [
            'sedes' => $sedes,
            'detalle' => $detalle,
        ]);
    }

    public function uploadForm()
    {
        return view('sede.upload');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_sedes' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('excel_sedes');
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
        $file->move($tempDir, $fileName);

        $spreadsheet = IOFactory::createReaderForFile($fullPath)->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();

        // Fila 2: nombres de sedes, filas 3+ ambientes
        $headerRow = 2;
        $firstDataRow = 3;

        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $highestRow = $sheet->getHighestRow();

        $sedes = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $headerRow;
            $name = trim((string) $sheet->getCell($cellAddress)->getValue());
            if ($name === '') {
                continue;
            }
            $sedes[$col] = [
                'nombre' => $name,
                'ambientes' => [],
            ];
        }

        for ($row = $firstDataRow; $row <= $highestRow; $row++) {
            $emptyRow = true;
            foreach ($sedes as $col => &$info) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $value = trim((string) $sheet->getCell($cellAddress)->getValue());
                if ($value !== '') {
                    $emptyRow = false;
                    $info['ambientes'][] = $value;
                }
            }
            unset($info);
            if ($emptyRow) {
                // Si toda la fila está vacía, paramos para no leer hasta el final de la hoja
                break;
            }
        }

        // Marcar sedes sin ambientes para mostrar "Sin ambiente por el momento"
        foreach ($sedes as $col => &$info) {
            if (count($info['ambientes']) === 0) {
                $info['ambientes'][] = null; // se mostrará como "Sin ambiente por el momento"
            }
        }
        unset($info);

        return view('sede.preview', [
            'fileName' => $fileName,
            'sedes' => $sedes,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string',
        ]);

        $fileName = $request->input('file_name');
        $fullPath = storage_path('app/temp/' . $fileName);
        if (!file_exists($fullPath)) {
            return redirect()->route('sede.upload')->with('error', 'No se encontró el archivo temporal. Vuelve a cargar el Excel.');
        }

        $spreadsheet = IOFactory::createReaderForFile($fullPath)->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();

        $headerRow = 2;
        $firstDataRow = 3;

        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $highestRow = $sheet->getHighestRow();

        DB::beginTransaction();
        try {
            $createdSedes = 0;
            $createdAmbientes = 0;

            $sedeMap = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $headerRow;
                $name = trim((string) $sheet->getCell($cellAddress)->getValue());
                if ($name === '') {
                    continue;
                }

                $existing = DB::table('sede')->where('nom_sede', $name)->first();
                if ($existing) {
                    $codSede = $existing->cod_sede;
                } else {
                    $codSede = DB::table('sede')->insertGetId([
                        'nom_sede' => $name,
                    ]);
                    $createdSedes++;
                }
                $sedeMap[$col] = $codSede;
            }

            for ($row = $firstDataRow; $row <= $highestRow; $row++) {
                $emptyRow = true;
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    if (!isset($sedeMap[$col])) {
                        continue;
                    }
                    $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                    $value = trim((string) $sheet->getCell($cellAddress)->getValue());
                    if ($value === '') {
                        continue;
                    }
                    $emptyRow = false;

                    DB::table('ambiente')->insert([
                        'denominacion' => $value,
                        'cod_sede_fk' => $sedeMap[$col],
                    ]);
                    $createdAmbientes++;
                }
                if ($emptyRow) {
                    break;
                }
            }

            DB::commit();

            return redirect()->route('sede.index')
                ->with('success', "Sedes creadas: $createdSedes, ambientes creados: $createdAmbientes");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('sede.upload')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function storeLugar(Request $request)
    {
        $request->validate([
            'nombre_sede' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'ambientes' => 'array',
            'ambientes.*' => 'nullable|string|max:255',
        ]);

        $nombreBase = trim($request->input('nombre_sede'));
        $direccion = trim($request->input('direccion', ''));
        $nomSede = $nombreBase;
        if ($direccion !== '') {
            $nomSede .= ' - ' . $direccion;
        }

        DB::beginTransaction();
        try {
            $codSede = DB::table('sede')->insertGetId([
                'nom_sede' => $nomSede,
            ]);

            $ambientes = $request->input('ambientes', []);
            $creados = 0;
            foreach ($ambientes as $amb) {
                $amb = trim($amb ?? '');
                if ($amb === '') {
                    continue;
                }
                DB::table('ambiente')->insert([
                    'denominacion' => $amb,
                    'cod_sede_fk' => $codSede,
                ]);
                $creados++;
            }

            DB::commit();
            return redirect()->route('sede.index')
                ->with('success', 'Sede registrada correctamente. Ambientes creados: ' . $creados);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('sede.index')
                ->with('error', 'Error al registrar la sede: ' . $e->getMessage());
        }
    }
}
