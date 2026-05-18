<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Cálculo de métricas rápidas
        $metricas = [
            'total' => Reporte::count(),
            'pendientes' => Reporte::where('estado', 'Pendiente')->count(),
            'en_progreso' => Reporte::where('estado', 'En progreso')->count(),
            'resueltos' => Reporte::where('estado', 'Resuelto')->count(),
        ];
        
        // 2. Últimos 5 reportes para la tabla de gestión
        $reportesRecientes = Reporte::with('municipio')->latest()->take(5)->get();
        
        // 3. Coordenadas de reportes activos para el mapa Leaflet
        $pinesMapa = Reporte::whereIn('estado', ['Pendiente', 'En progreso'])
            ->select('folio', 'latitud', 'longitud', 'categoria', 'estado')
            ->get();

        return view('admin.dashboard', compact('metricas', 'reportesRecientes', 'pinesMapa'));
    }
}
