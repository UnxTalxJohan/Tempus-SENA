<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MatrizController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\SetupController;

// Login (raíz muestra el formulario)
Route::get('/', [WebLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [WebLoginController::class, 'logout'])->name('logout');

// Setup: crear/actualizar admin (protegido por token de entorno)
Route::get('/setup/create-admin', [SetupController::class, 'createAdmin'])->name('setup.create-admin');

// Dashboard protegido (contendrá lo que antes estaba en "/")
Route::middleware('app.auth')->group(function () {
	Route::get('/dashboard', [ProgramaController::class, 'index'])->name('dashboard');

	// Rutas para Excel
	Route::get('/excel/upload', [ExcelController::class, 'showUploadForm'])->name('excel.upload');
	Route::post('/excel/preview', [ExcelController::class, 'preview'])->name('excel.preview');
	Route::post('/excel/preview-multi', [ExcelController::class, 'previewMulti'])->name('excel.preview.multi');
	Route::get('/excel/preview-multi-view', [ExcelController::class, 'previewMultiView'])->name('excel.preview.multi_view');
	Route::post('/excel/preview-file', [ExcelController::class, 'previewFile'])->name('excel.preview.file');
	Route::post('/excel/process', [ExcelController::class, 'process'])->name('excel.process');
	Route::post('/excel/process-multi', [ExcelController::class, 'processMulti'])->name('excel.process.multi');

	// Rutas para Matriz Extendida
	Route::get('/matriz', [MatrizController::class, 'index'])->name('matriz.index');
	Route::get('/matriz/{hash}', [MatrizController::class, 'show'])->name('matriz.show');
	Route::get('/matriz/exportar/{hash}', [MatrizController::class, 'exportar'])->name('matriz.exportar');
	Route::delete('/matriz/{id_prog}', [MatrizController::class, 'destroy'])->name('matriz.destroy');

	// Gestión de usuarios (solo admin)
	Route::get('/usuarios/gestion', [UserAdminController::class, 'index'])->name('usuarios.index');
	Route::get('/usuarios/contratistas/consolidado', [UserAdminController::class, 'showContratistasForm'])->name('usuarios.contratistas.form');
	Route::post('/usuarios/contratistas/consolidado', [UserAdminController::class, 'previewContratistasExcel'])->name('usuarios.contratistas.preview');
	Route::post('/usuarios/contratistas/consolidado/process', [UserAdminController::class, 'processContratistasExcel'])->name('usuarios.contratistas.process');
	Route::get('/usuarios/titulada/consolidado', [UserAdminController::class, 'showTituladaForm'])->name('usuarios.titulada.form');
	Route::post('/usuarios/titulada/consolidado', [UserAdminController::class, 'previewTituladaExcel'])->name('usuarios.titulada.preview');
	Route::post('/usuarios/titulada/consolidado/process', [UserAdminController::class, 'processTituladaExcel'])->name('usuarios.titulada.process');
	Route::post('/usuarios/titulada/consolidado/cancel', [UserAdminController::class, 'cancelTituladaExcel'])->name('usuarios.titulada.cancel');

	// Panel de Usuario
	Route::get('/usuario/panel', [\App\Http\Controllers\UserPanelController::class, 'index'])->name('user.panel');
	Route::post('/usuario/avatar', [\App\Http\Controllers\UserPanelController::class, 'uploadAvatar'])->name('user.avatar.upload');

	// Actualizaciones inline (AJAX)
	Route::put('/matriz/competencia/{cod_comp}', [MatrizController::class, 'updateCompetencia'])->name('matriz.competencia.update');
	Route::put('/matriz/resultado/{id_resu}', [MatrizController::class, 'updateResultado'])->name('matriz.resultado.update');

	// Rutas de notificaciones (API/AJAX)
	Route::get('/notificaciones', [\App\Http\Controllers\NotificacionController::class, 'index'])->name('notificaciones.index');
	Route::post('/notificaciones/marcar-todas', [\App\Http\Controllers\NotificacionController::class, 'markAllRead'])->name('notificaciones.markAllRead');
	Route::get('/notificaciones/{id}', [\App\Http\Controllers\NotificacionController::class, 'show'])->name('notificaciones.show');
	Route::delete('/notificaciones/{id}', [\App\Http\Controllers\NotificacionController::class, 'destroy'])->name('notificaciones.destroy');

	// API sencilla para limpiar logs de carga -> borra notificaciones de carga en BD (usa nombres totalmente calificados)
	Route::post('/api/clear-upload-logs', function() {
		$appAuth = session('app_auth', []);
		$cc = null;
		if (!empty($appAuth['usuario_id'])) {
			$uid = $appAuth['usuario_id'];
			$u = \DB::table('usuario')
				->where('id_usuario', $uid)
				->orWhere('id', $uid)
				->orWhere('cc', $uid)
				->first();
			if ($u) $cc = $u->cc ?? null;
		}
		if (!$cc && !empty($appAuth['email'])) {
			$u = \DB::table('usuario')
				->where('correo', $appAuth['email'])
				->orWhere('email', $appAuth['email'])
				->first();
			if ($u) $cc = $u->cc ?? null;
		}
		if (!$cc) return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
		// Borrar notificaciones con títulos generados por el flujo de upload
		\App\Models\Notificacion::where('cc_usuario_fk', $cc)
			->where(function($q){
				$q->where('titulo', 'like', 'Advertencia:%')
				  ->orWhere('titulo', 'like', 'Error%');
			})->delete();
		return response()->json(['ok' => true]);
	})->name('api.clear-upload-logs');
});
