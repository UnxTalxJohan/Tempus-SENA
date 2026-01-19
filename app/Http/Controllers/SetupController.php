<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupController extends Controller
{
    // GET /setup/create-admin?token=...&user=admin&email=a@b.com&password=Secret123
    public function createAdmin(Request $request)
    {
        $token = $request->query('token');
        $expected = env('SETUP_TOKEN', 'TEMPUS_SETUP');
        if (!$token || !hash_equals($expected, $token)) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $user = trim((string) $request->query('user', 'admin'));
        $email = trim((string) $request->query('email', ''));
        $password = (string) $request->query('password', 'Admin123*');

        if ($user === '' && $email === '') {
            return response()->json(['ok' => false, 'error' => 'Falta user o email'], 422);
        }

        $connection = config('database.default');
        $database = config("database.connections.$connection.database");
        $columns = collect(DB::select(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, 'usuario']
        ))->pluck('COLUMN_NAME')->map(fn($c) => strtolower($c))->all();

        if (!$columns) {
            return response()->json(['ok' => false, 'error' => 'Tabla usuario no encontrada'], 404);
        }

        $identityFields = array_values(array_intersect(['email','correo','usuario'], $columns));
        if (empty($identityFields)) {
            return response()->json(['ok' => false, 'error' => 'No hay columnas de identidad (email/correo/usuario)'], 422);
        }

        // Buscar existente
        $q = DB::table('usuario');
        $first = true;
        foreach ($identityFields as $f) {
            $value = in_array($f, ['email','correo']) ? $email : $user;
            if (!$value) { continue; }
            $q = $first ? $q->where($f, $value) : $q->orWhere($f, $value);
            $first = false;
        }
        $existing = $q->first();

        // Determinar columnas destino
        $passField = null; foreach (['password','contrasena','clave'] as $pf) { if (in_array($pf, $columns, true)) { $passField = $pf; break; } }
        $rolField = null; foreach (['id_rol_fk','rol_id','id_rol','rol'] as $rf) { if (in_array($rf, $columns, true)) { $rolField = $rf; break; } }
        $userField = null; foreach (['usuario','username','nombre_usuario'] as $uf) { if (in_array($uf, $columns, true)) { $userField = $uf; break; } }
        $emailField = null; foreach (['email','correo'] as $ef) { if (in_array($ef, $columns, true)) { $emailField = $ef; break; } }
        $idField = null; foreach (['id_usuario','id'] as $idf) { if (in_array($idf, $columns, true)) { $idField = $idf; break; } }

        if (!$passField || !$rolField) {
            return response()->json(['ok' => false, 'error' => 'Faltan columnas password/rol'], 422);
        }

        $now = now();
        $data = [ $passField => Hash::make($password), $rolField => 1 ];
        if ($userField && $user) { $data[$userField] = $user; }
        if ($emailField && $email) { $data[$emailField] = $email; }
        if (in_array('created_at', $columns, true)) { $data['created_at'] = $now; }
        if (in_array('updated_at', $columns, true)) { $data['updated_at'] = $now; }

        if ($existing) {
            // update
            $uq = DB::table('usuario');
            $first = true;
            foreach ($identityFields as $f) {
                $value = $existing->{$f} ?? null;
                if ($value === null) { continue; }
                $uq = $first ? $uq->where($f, $value) : $uq->orWhere($f, $value);
                $first = false;
            }
            $uq->update($data);
            return response()->json(['ok' => true, 'action' => 'updated', 'user' => $user, 'email' => $email]);
        }

        // insert
        if ($idField && !isset($data[$idField])) {
            // no generamos id manual; dejar que DB lo asigne si es autoincremental
        }
        DB::table('usuario')->insert($data);
        return response()->json(['ok' => true, 'action' => 'inserted', 'user' => $user, 'email' => $email]);
    }
}
