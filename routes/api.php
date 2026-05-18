<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrabajadorController;

// Rutas Públicas de la API Móvil
Route::post('/login', [TrabajadorController::class, 'login']);

// Rutas Protegidas (Solo celulares con Token válido)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [TrabajadorController::class, 'logout']);
    Route::get('/mis-reportes', [TrabajadorController::class, 'misReportes']);
    Route::post('/reportes/{id}/resolver', [TrabajadorController::class, 'resolverReporte']);
});