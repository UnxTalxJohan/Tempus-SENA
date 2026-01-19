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
	Route::post('/excel/process', [ExcelController::class, 'process'])->name('excel.process');

	// Rutas para Matriz Extendida
	Route::get('/matriz', [MatrizController::class, 'index'])->name('matriz.index');
	Route::get('/matriz/{id_prog}', [MatrizController::class, 'show'])->name('matriz.show');
	Route::get('/matriz/exportar/{id_prog}', [MatrizController::class, 'exportar'])->name('matriz.exportar');

	// Actualizaciones inline (AJAX)
	Route::put('/matriz/competencia/{cod_comp}', [MatrizController::class, 'updateCompetencia'])->name('matriz.competencia.update');
	Route::put('/matriz/resultado/{id_resu}', [MatrizController::class, 'updateResultado'])->name('matriz.resultado.update');
});
