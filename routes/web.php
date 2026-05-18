<?php
// Archivo: routes/web.php

use Illuminate\Support\Facades\Route;

// Controladores Públicos y de Autenticación
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AuthController;

// Controladores del Panel Administrativo (Para los Alcaldes)
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReporteAdminController;
use App\Http\Controllers\Admin\MapaController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\MetricaController;
use App\Http\Controllers\Admin\NotificacionController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\CuadrillaController;

// Controladores del Nivel 0 (Para ti, el dueño del SaaS)
use App\Http\Controllers\Master\MasterController;

// ==========================================
// 1. FLUJO PÚBLICO CIUDADANO (URLs Dinámicas por Municipio)
// ==========================================
// Si alguien entra a la raíz "/", lo mandamos por defecto a Mexticacán
Route::get('/', function() {
    return redirect()->route('reportes.create', ['municipio' => 'mexticacan']);
});

// Ruta dinámica (Ej: /m/teocaltiche)
Route::get('/m/{municipio}', [ReporteController::class, 'create'])->name('reportes.create');
Route::post('/reportes', [ReporteController::class, 'store'])->name('reportes.store');

Route::get('/consulta', [ReporteController::class, 'consulta'])->name('reportes.consulta');
Route::post('/consulta', [ReporteController::class, 'buscar'])->name('reportes.buscar');

// ==========================================
// 2. FLUJO DE AUTENTICACIÓN
// ==========================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// 3. PANEL MAESTRO (NIVEL 0 - DUEÑO DEL SAAS)
// ==========================================
Route::prefix('master')->middleware(['auth', \App\Http\Middleware\IsMaster::class])->name('master.')->group(function () {
    
    Route::get('/dashboard', [MasterController::class, 'index'])->name('dashboard');
    Route::post('/municipios', [MasterController::class, 'store'])->name('municipios.store');
    
});

// ==========================================
// 4. PANEL DE CONTROL (NIVELES 1 y 2 - GOBIERNO)
// ==========================================
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mapa', [MapaController::class, 'index'])->name('mapa');
    
    Route::get('/reportes', [ReporteAdminController::class, 'index'])->name('reportes.index');
    Route::patch('/reportes/{reporte}/estado', [ReporteAdminController::class, 'actualizarEstado'])->name('reportes.estado');
    
    Route::resource('areas', AreaController::class)->only(['index', 'store', 'destroy']);
    Route::resource('cuadrillas', CuadrillaController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/cuadrillas/{cuadrilla}/asignar', [CuadrillaController::class, 'asignarTrabajadores'])->name('cuadrillas.asignar');

    Route::resource('usuarios', UsuarioController::class)->only(['index', 'store', 'destroy']);

    Route::get('/metricas', [MetricaController::class, 'index'])->name('metricas.index');
    Route::get('/metricas/reporte-pdf', [MetricaController::class, 'generarReporte'])->name('metricas.reporte');

    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion/perfil', [ConfiguracionController::class, 'updatePerfil'])->name('configuracion.perfil');

    Route::get('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/limpiar', [NotificacionController::class, 'limpiarTodas'])->name('notificaciones.limpiar');
});