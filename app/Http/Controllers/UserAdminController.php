<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use App\Models\Notificacion;

class UserAdminController extends Controller
{
    /**
     * Panel principal de gestión de usuarios.
     * Muestra el listado de usuarios y acceso al consolidado de contratistas.
     */
    public function index()
    {
        $usuarios = DB::table('usuario')
            ->leftJoin('rol', 'usuario.id_rol_fk', '=', 'rol.id_rol')
            ->leftJoin('vinculacion', 'usuario.id_vinculacion_fk', '=', 'vinculacion.id_vinculacion')
            ->select(
                'usuario.cc',
                'usuario.nombre',
                'usuario.correo',
                'usuario.id_rol_fk',
                'rol.nombre_rol',
                'vinculacion.tip_vincul',
                'vinculacion.nmr_contrato',
                'vinculacion.nvl_formacion',
                'vinculacion.pregrado',
                'vinculacion.postgrado',
                'vinculacion.coord_pertenece',
                'vinculacion.modalidad',
                'vinculacion.especialidad',
                'vinculacion.fch_inic_contrato',
                'vinculacion.fch_fin_contrato',
                'vinculacion.area',
                'vinculacion.estudios',
                'vinculacion.red'
            )
            ->where('usuario.id_rol_fk', '!=', 1) // ocultar usuarios con rol admin
            ->orderBy('usuario.nombre')
            ->get();

        return view('user.admin_index', compact('usuarios'));
    }

    /**
     * Muestra el panel para cargar el consolidado de contratistas.
     */
    public function showContratistasForm()
    {
        return view('user.contratistas_upload');
    }

    /**
     * Muestra el panel para cargar el consolidado de titulada.
     * (Por ahora solo interfaz; la lógica de procesamiento se definirá después.)
     */
    public function showTituladaForm()
    {
        return view('user.titulada_upload');
    }

    /**
     * Paso 1: recibe el archivo Excel de titulada (planta) y muestra una previsualización.
     */
    public function previewTituladaExcel(Request $request)
    {
        $request->validate([
            'excel_titulada' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('excel_titulada');
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
        $file->move($tempDir, $fileName);

        try {
            $spreadsheet = IOFactory::createReaderForFile($fullPath)->load($fullPath);
        } catch (\Throwable $e) {
            return back()->with('error', 'Archivo Excel inválido o dañado: ' . $e->getMessage());
        }

        $preview = $this->buildTituladaPreview($spreadsheet);

        // Guardar lista de filas sin CC para un posible cancelado
        $sessionKey = 'titulada_missing_' . $fileName;
        session([$sessionKey => $preview['missingNoCc']]);

        return view('user.titulada_preview', [
            'fileName'     => $fileName,
            'rows'         => $preview['rows'],
            'totalRows'    => $preview['totalRows'],
            'validRows'    => $preview['validRows'],
            'skippedNoCc'  => $preview['skippedNoCc'],
        ]);
    }

    /**
     * Paso 2: procesa definitivamente el archivo Excel ya previsualizado
     * e inserta/actualiza en las tablas usuario y vinculacion.
     */
    public function processTituladaExcel(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string',
        ]);

        $fileName = $request->input('file_name');
        $fullPath = storage_path('app/temp/' . $fileName);
        if (!file_exists($fullPath)) {
            return redirect()->route('usuarios.titulada.form')
                ->with('error', 'No se encontró el archivo temporal. Vuelve a cargar el Excel.');
        }

        $resultado = $this->processTituladaFile($fullPath);

        // Notificacion de exito cuando se sube el consolidado
        if (($resultado['insertados'] ?? 0) > 0 || ($resultado['actualizados'] ?? 0) > 0) {
            $this->registrarNotificacion('Planta subida con exito', $resultado['mensaje']);
        }

        return redirect()->route('usuarios.index')
            ->with('success', $resultado['mensaje'])
            ->with('error_details', $resultado['errores']);
    }

    /**
     * Cancelar la carga de titulada y registrar notificacion por filas sin CC.
     */
    public function cancelTituladaExcel(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string',
        ]);

        $fileName = $request->input('file_name');
        $sessionKey = 'titulada_missing_' . $fileName;
        $missing = session($sessionKey, []);

        if (!empty($missing)) {
            $parts = [];
            foreach ($missing as $m) {
                $row = $m['row'] ?? 'N/D';
                $nombre = $m['nombre'] ?? 'N/D';
                $parts[] = "Fila $row: $nombre";
                if (count($parts) >= 10) {
                    $parts[] = '...';
                    break;
                }
            }
            $detalle = implode('; ', $parts);
            $this->registrarNotificacion('Archivo no cargado', 'Archivo no cargado por usuarios sin CC. ' . $detalle);
        } else {
            $this->registrarNotificacion('Archivo no cargado', 'Archivo no cargado por decision del usuario.');
        }

        session()->forget($sessionKey);

        return redirect()->route('usuarios.titulada.form')
            ->with('error', 'Carga cancelada por el usuario.');
    }

    /**
     * Paso 1: recibe el archivo Excel de contratistas y muestra una previsualización.
     */
    public function previewContratistasExcel(Request $request)
    {
        $request->validate([
            'excel_contratistas' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('excel_contratistas');
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
        $file->move($tempDir, $fileName);

        try {
            $spreadsheet = IOFactory::createReaderForFile($fullPath)->load($fullPath);
        } catch (\Throwable $e) {
            return back()->with('error', 'Archivo Excel inválido o dañado: ' . $e->getMessage());
        }

        $preview = $this->buildContratistasPreview($spreadsheet);

        return view('user.contratistas_preview', [
            'fileName'     => $fileName,
            'rows'         => $preview['rows'],
            'totalRows'    => $preview['totalRows'],
            'validRows'    => $preview['validRows'],
            'skippedNoCc'  => $preview['skippedNoCc'],
        ]);
    }

    /**
     * Paso 2: procesa definitivamente el archivo Excel ya previsualizado
     * e inserta/actualiza en las tablas usuario y vinculacion.
     */
    public function processContratistasExcel(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string',
        ]);

        $fileName = $request->input('file_name');
        $fullPath = storage_path('app/temp/' . $fileName);
        if (!file_exists($fullPath)) {
            return redirect()->route('usuarios.contratistas.form')
                ->with('error', 'No se encontró el archivo temporal. Vuelve a cargar el Excel.');
        }

        $resultado = $this->processContratistasFile($fullPath);

        return redirect()->route('usuarios.index')
            ->with('success', $resultado['mensaje'])
            ->with('error_details', $resultado['errores']);
    }

    /**
     * Convierte un valor proveniente de Excel a fecha Y-m-d o null.
     */
    private function parseExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $dt = ExcelDate::excelToDateTimeObject($value);
                return $dt->format('Y-m-d');
            }
            $carbon = Carbon::parse($value);
            return $carbon->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Construye datos de previsualización a partir del Excel.
     */
    private function buildContratistasPreview(Spreadsheet $spreadsheet): array
    {
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $rows = [];
        $totalRows = 0;
        $validRows = 0;
        $skippedNoCc = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $totalRows++;

            $contrato   = trim((string) $sheet->getCell('B' . $row)->getValue());
            $nombre     = trim((string) $sheet->getCell('C' . $row)->getValue());
            $ccRaw      = $sheet->getCell('D' . $row)->getValue();
            $correo     = trim((string) $sheet->getCell('E' . $row)->getValue());
            $nivelForm  = trim((string) $sheet->getCell('F' . $row)->getValue());
            $pregrado   = trim((string) $sheet->getCell('G' . $row)->getValue());
            $postgrado  = trim((string) $sheet->getCell('H' . $row)->getValue());
            $coord      = trim((string) $sheet->getCell('I' . $row)->getValue());
            $modalidad  = trim((string) $sheet->getCell('J' . $row)->getValue());
            $especial   = trim((string) $sheet->getCell('K' . $row)->getValue());
            $fecIniRaw  = $sheet->getCell('L' . $row)->getValue();
            $fecFinRaw  = $sheet->getCell('M' . $row)->getValue();

            $allEmpty = ($contrato === '' && $nombre === '' && ($ccRaw === null || $ccRaw === '') && $correo === ''
                && $nivelForm === '' && $pregrado === '' && $postgrado === '' && $coord === ''
                && $modalidad === '' && $especial === '' && ($fecIniRaw === null || $fecIniRaw === '') && ($fecFinRaw === null || $fecFinRaw === ''));
            if ($allEmpty) {
                $totalRows--;
                continue;
            }

            $cc = $ccRaw !== null && $ccRaw !== '' ? (string) $ccRaw : null;
            if ($cc === null) {
                $skippedNoCc++;
            } else {
                $validRows++;
            }

            $rows[] = [
                'row'        => $row,
                'cc'         => $cc,
                'nombre'     => $nombre,
                'correo'     => $correo,
                'contrato'   => $contrato,
                'nivel'      => $nivelForm,
                'pregrado'   => $pregrado,
                'postgrado'  => $postgrado,
                'coord'      => $coord,
                'modalidad'  => $modalidad,
                'especial'   => $especial,
                'fch_inicio' => $this->parseExcelDate($fecIniRaw),
                'fch_fin'    => $this->parseExcelDate($fecFinRaw),
                'sin_cc'     => $cc === null,
            ];
        }

        return compact('rows', 'totalRows', 'validRows', 'skippedNoCc');
    }

    /**
     * Construye datos de previsualización para titulada (planta).
     * Columnas esperadas: B=Nombre y apellidos, C=Cedula, D=Area, E=Estudios
     */
    private function buildTituladaPreview(Spreadsheet $spreadsheet): array
    {
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $rows = [];
        $totalRows = 0;
        $validRows = 0;
        $skippedNoCc = 0;
        $missingNoCc = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $totalRows++;

            $nombre  = trim((string) $sheet->getCell('B' . $row)->getValue());
            $ccRaw   = $sheet->getCell('C' . $row)->getValue();
            $area    = trim((string) $sheet->getCell('D' . $row)->getValue());
            $estudios= trim((string) $sheet->getCell('E' . $row)->getValue());

            $allEmpty = ($nombre === '' && ($ccRaw === null || $ccRaw === '') && $area === '' && $estudios === '');
            if ($allEmpty) {
                $totalRows--;
                continue;
            }

            $cc = $ccRaw !== null && $ccRaw !== '' ? (string) $ccRaw : null;
            if ($cc === null) {
                $skippedNoCc++;
                $missingNoCc[] = [
                    'row' => $row,
                    'nombre' => $nombre,
                ];
            } else {
                $validRows++;
            }

            $rows[] = [
                'row'      => $row,
                'cc'       => $cc,
                'nombre'   => $nombre,
                'area'     => $area,
                'estudios' => $estudios,
                'sin_cc'   => $cc === null,
            ];
        }

        return compact('rows', 'totalRows', 'validRows', 'skippedNoCc', 'missingNoCc');
    }

    /**
     * Procesa el archivo Excel e inserta/actualiza en la BD.
     */
    private function processContratistasFile(string $fullPath): array
    {
        try {
            $spreadsheet = IOFactory::createReaderForFile($fullPath)->load($fullPath);
        } catch (\Throwable $e) {
            return [
                'mensaje' => 'Error leyendo el archivo: ' . $e->getMessage(),
                'errores' => [],
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $insertados = 0;
        $actualizados = 0;
        $sinCambios = 0;
        $errores = [];

        // Para validar unicidad por CC dentro del mismo archivo
        $seenCc = [];

        DB::beginTransaction();
        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $contrato   = trim((string) $sheet->getCell('B' . $row)->getValue());
                $nombre     = trim((string) $sheet->getCell('C' . $row)->getValue());
                $ccRaw      = $sheet->getCell('D' . $row)->getValue();
                $correo     = trim((string) $sheet->getCell('E' . $row)->getValue());
                $nivelForm  = trim((string) $sheet->getCell('F' . $row)->getValue());
                $pregrado   = trim((string) $sheet->getCell('G' . $row)->getValue());
                $postgrado  = trim((string) $sheet->getCell('H' . $row)->getValue());
                $coord      = trim((string) $sheet->getCell('I' . $row)->getValue());
                $modalidad  = trim((string) $sheet->getCell('J' . $row)->getValue());
                $especial   = trim((string) $sheet->getCell('K' . $row)->getValue());
                $fecIniRaw  = $sheet->getCell('L' . $row)->getValue();
                $fecFinRaw  = $sheet->getCell('M' . $row)->getValue();

                $allEmpty = ($contrato === '' && $nombre === '' && ($ccRaw === null || $ccRaw === '') && $correo === ''
                    && $nivelForm === '' && $pregrado === '' && $postgrado === '' && $coord === ''
                    && $modalidad === '' && $especial === '' && ($fecIniRaw === null || $fecIniRaw === '') && ($fecFinRaw === null || $fecFinRaw === ''));
                if ($allEmpty) {
                    continue;
                }

                if ($ccRaw === null || $ccRaw === '') {
                    $errores[] = "Fila $row: sin número de documento (cc); se omitió.";
                    continue;
                }
                $cc = (string) $ccRaw;

                // Validar que dentro del archivo no haya CC repetidas
                if (isset($seenCc[$cc])) {
                    $errores[] = "Fila $row: número de documento $cc duplicado en el archivo; se omitió.";
                    continue;
                }
                $seenCc[$cc] = true;

                $fecIni = $this->parseExcelDate($fecIniRaw);
                $fecFin = $this->parseExcelDate($fecFinRaw);

                $usuario = DB::table('usuario')->where('cc', $cc)->first();

                $idVinc = null;
                $vincActual = null;
                if ($usuario && !empty($usuario->id_vinculacion_fk)) {
                    $idVinc = (int) $usuario->id_vinculacion_fk;
                    $vincActual = DB::table('vinculacion')->where('id_vinculacion', $idVinc)->first();
                    $dataV = [
                        'tip_vincul'       => $this->limitStr('Contrato'),
                        'nmr_contrato'     => $this->limitStr($contrato),
                        'nvl_formacion'    => $this->limitStr($nivelForm),
                        'pregrado'         => $this->limitStr($pregrado),
                        'postgrado'        => $this->limitStr($postgrado),
                        'coord_pertenece'  => $this->limitStr($coord),
                        'modalidad'        => $this->limitStr($modalidad),
                        'especialidad'     => $this->limitStr($especial),
                        'fch_inic_contrato'=> $fecIni,
                        'fch_fin_contrato' => $fecFin,
                    ];
                } else {
                    $dataV = [
                        'tip_vincul'       => $this->limitStr('Contrato'),
                        'nmr_contrato'     => $this->limitStr($contrato),
                        'nvl_formacion'    => $this->limitStr($nivelForm),
                        'pregrado'         => $this->limitStr($pregrado),
                        'postgrado'        => $this->limitStr($postgrado),
                        'coord_pertenece'  => $this->limitStr($coord),
                        'modalidad'        => $this->limitStr($modalidad),
                        'especialidad'     => $this->limitStr($especial),
                        'fch_inic_contrato'=> $fecIni,
                        'fch_fin_contrato' => $fecFin,
                        'area'             => null,
                        'estudios'         => null,
                        'red'              => null,
                    ];
                    $idVinc = DB::table('vinculacion')->insertGetId($dataV);
                }

                $dataUser = [
                    'id_rol_fk'         => 2,
                    'id_vinculacion_fk' => $idVinc,
                ];
                if ($nombre !== '') {
                    $dataUser['nombre'] = $nombre;
                }
                if ($correo !== '') {
                    $dataUser['correo'] = $correo;
                }

                if ($usuario) {
                    $hayCambiosV = $vincActual ? !$this->rowEqualsArray($vincActual, $dataV) : true;
                    $hayCambiosU = !$this->rowEqualsArray($usuario, $dataUser);

                    if ($hayCambiosV) {
                        DB::table('vinculacion')->where('id_vinculacion', $idVinc)->update($dataV);
                    }

                    if ($hayCambiosU) {
                        DB::table('usuario')->where('cc', $cc)->update($dataUser);
                    }

                    if ($hayCambiosV || $hayCambiosU) {
                        $actualizados++;
                    } else {
                        $sinCambios++;
                    }
                } else {
                    $dataUser['cc'] = $cc;
                    $dataUser['contrasena'] = Hash::make(Str::random(24));
                    DB::table('usuario')->insert($dataUser);
                    $insertados++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'mensaje' => 'Error procesando el consolidado: ' . $e->getMessage(),
                'errores' => $errores,
            ];
        }

        $mensaje = "Consolidado procesado: $insertados usuario(s) creados, $actualizados actualizado(s).";
        if (!empty($errores)) {
            $mensaje .= ' Algunas filas se omitieron: ' . count($errores);
        }

        // Si no hubo altas ni actualizaciones pero sí filas procesadas, todo estaba ya registrado
        if ($insertados === 0 && $actualizados === 0 && $sinCambios > 0) {
            $this->registrarNotificacion('Contratos ya registrados', 'El consolidado cargado no generó cambios: todos los registros ya estaban registrados en el sistema.');
        }

        return compact('mensaje', 'errores');
    }

    /**
     * Procesa el archivo Excel de titulada (planta) e inserta/actualiza en la BD.
     */
    private function processTituladaFile(string $fullPath): array
    {
        try {
            $spreadsheet = IOFactory::createReaderForFile($fullPath)->load($fullPath);
        } catch (\Throwable $e) {
            return [
                'mensaje' => 'Error leyendo el archivo: ' . $e->getMessage(),
                'errores' => [],
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $insertados = 0;
        $actualizados = 0;
        $sinCambios = 0;
        $errores = [];
        $seenCc = [];

        // Asegurar rol planta (id 3) antes de insertar
        $this->ensureRolExists(3, 'planta');

        DB::beginTransaction();
        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $nombre   = trim((string) $sheet->getCell('B' . $row)->getValue());
                $ccRaw    = $sheet->getCell('C' . $row)->getValue();
                $area     = trim((string) $sheet->getCell('D' . $row)->getValue());
                $estudios = trim((string) $sheet->getCell('E' . $row)->getValue());

                $allEmpty = ($nombre === '' && ($ccRaw === null || $ccRaw === '') && $area === '' && $estudios === '');
                if ($allEmpty) {
                    continue;
                }

                if ($ccRaw === null || $ccRaw === '') {
                    $errores[] = "Fila $row: sin número de documento (cc); se omitió.";
                    continue;
                }
                $cc = (string) $ccRaw;

                if (isset($seenCc[$cc])) {
                    $errores[] = "Fila $row: número de documento $cc duplicado en el archivo; se omitió.";
                    continue;
                }
                $seenCc[$cc] = true;

                $usuario = DB::table('usuario')->where('cc', $cc)->first();

                $idVinc = null;
                $vincActual = null;
                if ($usuario && !empty($usuario->id_vinculacion_fk)) {
                    $idVinc = (int) $usuario->id_vinculacion_fk;
                    $vincActual = DB::table('vinculacion')->where('id_vinculacion', $idVinc)->first();
                    $dataV = [
                        'tip_vincul'      => $this->limitStr('Planta'),
                        'area'            => $this->limitStr($area),
                        'estudios'        => $this->limitStr($estudios),
                    ];
                } else {
                    $dataV = [
                        'tip_vincul'      => $this->limitStr('Planta'),
                        'area'            => $this->limitStr($area),
                        'estudios'        => $this->limitStr($estudios),
                        'nmr_contrato'    => null,
                        'nvl_formacion'   => null,
                        'pregrado'        => null,
                        'postgrado'       => null,
                        'coord_pertenece' => null,
                        'modalidad'       => null,
                        'especialidad'    => null,
                        'fch_inic_contrato'=> null,
                        'fch_fin_contrato'=> null,
                        'red'             => null,
                    ];
                    $idVinc = DB::table('vinculacion')->insertGetId($dataV);
                }

                $dataUser = [
                    'id_rol_fk'         => 3,
                    'id_vinculacion_fk' => $idVinc,
                ];
                if ($nombre !== '') {
                    $dataUser['nombre'] = $nombre;
                }

                if ($usuario) {
                    $hayCambiosV = $vincActual ? !$this->rowEqualsArray($vincActual, $dataV) : true;
                    $hayCambiosU = !$this->rowEqualsArray($usuario, $dataUser);

                    if ($hayCambiosV) {
                        DB::table('vinculacion')->where('id_vinculacion', $idVinc)->update($dataV);
                    }

                    if ($hayCambiosU) {
                        DB::table('usuario')->where('cc', $cc)->update($dataUser);
                    }

                    if ($hayCambiosV || $hayCambiosU) {
                        $actualizados++;
                    } else {
                        $sinCambios++;
                    }
                } else {
                    $dataUser['cc'] = $cc;
                    $dataUser['contrasena'] = Hash::make(Str::random(24));
                    DB::table('usuario')->insert($dataUser);
                    $insertados++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'mensaje' => 'Error procesando el consolidado: ' . $e->getMessage(),
                'errores' => $errores,
            ];
        }

        $mensaje = "Consolidado procesado: $insertados usuario(s) creados, $actualizados actualizado(s).";
        if (!empty($errores)) {
            $mensaje .= ' Algunas filas se omitieron: ' . count($errores);
        }

        if ($insertados === 0 && $actualizados === 0 && $sinCambios > 0) {
            $this->registrarNotificacion('Planta ya registrada', 'El consolidado cargado no generó cambios: todos los registros ya estaban registrados en el sistema.');
        }

        return compact('mensaje', 'errores', 'insertados', 'actualizados', 'sinCambios');
    }

    /**
     * Verifica que el rol exista y lo crea si falta.
     */
    private function ensureRolExists(int $idRol, string $nombre): void
    {
        try {
            $exists = DB::table('rol')->where('id_rol', $idRol)->exists();
            if ($exists) {
                return;
            }
            $data = ['id_rol' => $idRol];
            if (Schema::hasColumn('rol', 'nombre_rol')) {
                $data['nombre_rol'] = $nombre;
            }
            DB::table('rol')->insert($data);
        } catch (\Throwable $e) {
            // Si falla, se detectara por la FK al insertar usuario.
        }
    }

    /**
     * Normaliza una cadena: trim y null si queda vacía (sin recortar longitud).
     */
    private function limitStr(?string $value, int $max = 0): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        // No recortamos contenido; se deja completo.
        return $trimmed;
    }

    /**
     * Compara un registro stdClass de la BD con un arreglo clave => valor.
     */
    private function rowEqualsArray($row, array $data): bool
    {
        if (!$row) {
            return false;
        }
        foreach ($data as $key => $value) {
            $current = $row->$key ?? null;
            $currNorm = ($current === null || $current === '') ? null : (string) $current;
            $valueNorm = ($value === null || $value === '') ? null : (string) $value;
            if ($currNorm !== $valueNorm) {
                return false;
            }
        }
        return true;
    }

    /**
     * Registra una notificación para el usuario autenticado (esquema similar a ExcelController).
     */
    private function registrarNotificacion(string $titulo, string $descripcion): void
    {
        $cc = null;
        $appAuth = session('app_auth', []);
        if (!empty($appAuth['usuario_id']) || !empty($appAuth['email'])) {
            $query = DB::table('usuario');
            $uid = $appAuth['usuario_id'] ?? null;
            $email = $appAuth['email'] ?? null;

            $hasIdUsuario = Schema::hasColumn('usuario', 'id_usuario');
            $hasId = Schema::hasColumn('usuario', 'id');
            $hasCc = Schema::hasColumn('usuario', 'cc');
            $hasCorreo = Schema::hasColumn('usuario', 'correo');
            $hasEmail = Schema::hasColumn('usuario', 'email');

            $clauses = 0;
            if ($uid !== null) {
                if ($hasIdUsuario) { $query = $query->where('id_usuario', $uid); $clauses++; }
                if ($hasId) { $query = ($clauses ? $query->orWhere('id', $uid) : $query->where('id', $uid)); $clauses++; }
                if ($hasCc) { $query = ($clauses ? $query->orWhere('cc', $uid) : $query->where('cc', $uid)); $clauses++; }
            }
            if ($email !== null) {
                if ($hasCorreo) { $query = ($clauses ? $query->orWhere('correo', $email) : $query->where('correo', $email)); $clauses++; }
                if ($hasEmail) { $query = ($clauses ? $query->orWhere('email', $email) : $query->where('email', $email)); $clauses++; }
            }
            if ($clauses > 0) {
                $u = $query->first();
                if ($u) { $cc = $u->cc ?? null; }
            }
        }

        try {
            $tz = config('app.timezone');
            if (empty($tz) || strtolower($tz) === 'utc') {
                $tz = 'America/Bogota';
            }
            $now = Carbon::now($tz);
            Notificacion::create([
                'cc_usuario_fk' => $cc,
                'fch_noti'      => $now->toDateString(),
                'hora_noti'     => $now->format('H:i:s'),
                'titulo'        => $titulo,
                'descripcion'   => $descripcion,
                'estado'        => 1,
            ]);
        } catch (\Throwable $e) {
            // En caso de error, no romper el flujo; opcionalmente se podría loguear.
        }
    }
}
