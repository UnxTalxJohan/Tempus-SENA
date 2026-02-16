<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserPanelController extends Controller
{
    public function index()
    {
        return view('user.panel');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        // Actualizar sesión de la app para que el cambio se vea de inmediato
        $appAuth = session('app_auth', []);
        $appAuth['avatar'] = $path;
        session(['app_auth' => $appAuth]);

        // Persistir avatar en la tabla `usuario` si existe y se puede resolver el usuario actual
        try {
            if (!empty($appAuth['usuario_id']) || !empty($appAuth['email'])) {
                if (Schema::hasTable('usuario')) {
                    $query = DB::table('usuario');

                    $uid   = $appAuth['usuario_id'] ?? null;
                    $email = $appAuth['email'] ?? null;

                    $hasIdUsuario = Schema::hasColumn('usuario', 'id_usuario');
                    $hasId        = Schema::hasColumn('usuario', 'id');
                    $hasCc        = Schema::hasColumn('usuario', 'cc');
                    $hasCorreo    = Schema::hasColumn('usuario', 'correo');
                    $hasEmail     = Schema::hasColumn('usuario', 'email');

                    $clauses = 0;
                    if ($uid !== null) {
                        if ($hasIdUsuario) { $query = $query->where('id_usuario', $uid); $clauses++; }
                        if ($hasId)        { $query = $clauses ? $query->orWhere('id', $uid) : $query->where('id', $uid); $clauses++; }
                        if ($hasCc)        { $query = $clauses ? $query->orWhere('cc', $uid) : $query->where('cc', $uid); $clauses++; }
                    }
                    if ($email !== null) {
                        if ($hasCorreo) { $query = $clauses ? $query->orWhere('correo', $email) : $query->where('correo', $email); $clauses++; }
                        if ($hasEmail)  { $query = $clauses ? $query->orWhere('email', $email) : $query->where('email', $email); $clauses++; }
                    }

                    if ($clauses > 0) {
                        $update = [];
                        if (Schema::hasColumn('usuario', 'avatar_path')) {
                            $update['avatar_path'] = $path;
                        }
                        if (Schema::hasColumn('usuario', 'avatar_mime')) {
                            $update['avatar_mime'] = $file->getClientMimeType();
                        }
                        if (Schema::hasColumn('usuario', 'avatar_size')) {
                            $update['avatar_size'] = $file->getSize();
                        }
                        if (Schema::hasColumn('usuario', 'avatar_uploaded_at')) {
                            $update['avatar_uploaded_at'] = now();
                        }

                        if (!empty($update)) {
                            $query->update($update);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Si algo falla al persistir, no romper la experiencia de usuario.
        }

        return redirect()->route('user.panel')->with('status', 'Avatar actualizado');
    }
}
