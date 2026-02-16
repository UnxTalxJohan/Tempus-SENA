<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WebLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','string'], // admite correo o usuario
            'password' => ['required','string'],
        ]);

        $inputEmailOrUser = $data['email'];
        $inputPassword = $data['password'];

        // Detectar columnas existentes en tabla usuario para evitar errores 1054
        $connection = config('database.default');
        $database = config("database.connections.$connection.database");
        $columns = collect(DB::select(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, 'usuario']
        ))->pluck('COLUMN_NAME')->map(fn($c) => strtolower($c))->all();

        // Campos posibles para identificar usuario
        $candidatosIdentidad = ['email','correo','usuario'];
        $identFields = array_values(array_intersect($candidatosIdentidad, $columns));

        $query = DB::table('usuario');
        if (count($identFields) > 0) {
            $primero = true;
            foreach ($identFields as $f) {
                $query = $primero ? $query->where($f, $inputEmailOrUser) : $query->orWhere($f, $inputEmailOrUser);
                $primero = false;
            }
        } else {
            // Si no hay ninguno de esos campos, retornar como no encontrado
            return back()->withErrors([
                'email' => 'Usuario no encontrado (tabla usuario sin columnas esperadas).',
            ])->withInput($request->only('email'));
        }

        $usuario = $query->first();

        if (!$usuario) {
            return back()->withErrors([
                'email' => 'Usuario no encontrado.',
            ])->withInput($request->only('email'));
        }

        // Detectar campo de contraseña: password, contrasena, clave
        $candidatosPass = ['password','contrasena','clave'];
        $passField = null;
        foreach ($candidatosPass as $pf) {
            if (in_array($pf, $columns, true)) { $passField = $pf; break; }
        }
        $storedPassword = $passField ? ($usuario->{$passField} ?? null) : null;
        $passwordOk = false;
        if (is_string($storedPassword) && strlen($storedPassword) > 0) {
            // Intentar hash (bcrypt/argon2) con manejo de excepciones de formato
            try {
                $passwordOk = Hash::check($inputPassword, $storedPassword);
            } catch (\Throwable $e) {
                $passwordOk = false;
            }
            // Fallback texto plano
            if (!$passwordOk) { $passwordOk = ($storedPassword === $inputPassword); }
            // Fallback MD5 si parece un hash md5
            if (!$passwordOk && preg_match('/^[a-f0-9]{32}$/i', $storedPassword)) {
                $passwordOk = (md5($inputPassword) === strtolower($storedPassword));
            }
        }

        if (!$passwordOk) {
            return back()->withErrors([
                'email' => 'Contraseña incorrecta.',
            ])->withInput($request->only('email'));
        }

        // Obtener rol: probar id_rol_fk, rol_id, id_rol, rol
        $candidatosRol = ['id_rol_fk','rol_id','id_rol','rol'];
        $rolId = 0;
        foreach ($candidatosRol as $rf) {
            if (in_array($rf, $columns, true)) { $rolId = (int)($usuario->{$rf} ?? 0); break; }
        }
        if ($rolId !== 1) {
            return back()->withErrors([
                'email' => 'Sin permiso de entrar. Requiere rol admin.',
            ])->withInput($request->only('email'));
        }

        // Construir datos mínimos de sesión propia
        $usuarioId = $usuario->id_usuario ?? ($usuario->id ?? null);
        $nombre = $usuario->nombre ?? ($usuario->name ?? null);
        $correo = $usuario->email ?? ($usuario->correo ?? null);

        // Intentar recuperar avatar persistido si existe columna correspondiente
        $avatarPath = null;
        if (in_array('avatar_path', $columns, true)) {
            $avatarPath = $usuario->avatar_path ?? null;
        }

        $request->session()->put('app_auth', [
            'usuario_id' => $usuarioId,
            'rol_id' => $rolId,
            'nombre' => $nombre,
            'email' => $correo,
            'avatar' => $avatarPath,
        ]);

        // Regenerar sesión por seguridad
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        // Cerrar sesión propia
        $request->session()->forget('app_auth');
        // Cerrar guard de Laravel si estuviera activo (no afecta si no lo está)
        try { Auth::logout(); } catch (\Throwable $e) {}
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
