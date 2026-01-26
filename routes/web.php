<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MatrizController;
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

	// Panel de Usuario
	Route::get('/usuario/panel', [\App\Http\Controllers\UserPanelController::class, 'index'])->name('user.panel');
	Route::post('/usuario/avatar', [\App\Http\Controllers\UserPanelController::class, 'uploadAvatar'])->name('user.avatar.upload');

	// Actualizaciones inline (AJAX)
	Route::put('/matriz/competencia/{cod_comp}', [MatrizController::class, 'updateCompetencia'])->name('matriz.competencia.update');
	Route::put('/matriz/resultado/{id_resu}', [MatrizController::class, 'updateResultado'])->name('matriz.resultado.update');

	// API sencilla para limpiar logs de carga
	Route::post('/api/clear-upload-logs', function() {
		session(['upload_logs' => []]);
		return response()->json(['ok' => true]);
	})->name('api.clear-upload-logs');
});
