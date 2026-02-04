<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificacionController extends Controller
{
    private function resolveUserCc(): ?int
    {
        $appAuth = session('app_auth', []);
        $cc = null;
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
                if ($hasId) { $query = ($clauses? $query->orWhere('id', $uid) : $query->where('id', $uid)); $clauses++; }
                if ($hasCc) { $query = ($clauses? $query->orWhere('cc', $uid) : $query->where('cc', $uid)); $clauses++; }
            }
            if ($email !== null) {
                if ($hasCorreo) { $query = ($clauses? $query->orWhere('correo', $email) : $query->where('correo', $email)); $clauses++; }
                if ($hasEmail) { $query = ($clauses? $query->orWhere('email', $email) : $query->where('email', $email)); $clauses++; }
            }
            if ($clauses > 0) {
                $u = $query->first();
                if ($u) $cc = $u->cc ?? null;
            }
        }
        return $cc;
    }

    // Listar notificaciones del usuario logueado
    public function index()
    {
        $cc = $this->resolveUserCc();
        if (!$cc) {
            return response()->json([]);
        }
        $notificaciones = Notificacion::where('cc_usuario_fk', $cc)
            ->orderByDesc('fch_noti')
            ->orderByDesc('hora_noti')
            ->get();
        return response()->json($notificaciones);
    }

    // Ver detalle y marcar como leída
    public function show($id)
    {
        $noti = Notificacion::findOrFail($id);
        if ($noti->estado != 2) {
            $noti->estado = 2;
            $noti->save();
        }
        return response()->json($noti);
    }

    // Eliminar notificación
    public function destroy($id)
    {
        $noti = Notificacion::findOrFail($id);
        $noti->delete();
        return response()->json(['success' => true]);
    }

    // Marcar todas como leídas
    public function markAllRead()
    {
        $cc = $this->resolveUserCc();
        if (!$cc) {
            return response()->json(['success' => false], 401);
        }
        $updated = Notificacion::where('cc_usuario_fk', $cc)
            ->where('estado', 1)
            ->update(['estado' => 2]);
        return response()->json(['success' => true, 'updated' => $updated]);
    }
}
