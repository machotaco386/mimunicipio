<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use Illuminate\Http\Request;

class MapaController extends Controller
{
    public function index()
    {
        // Traemos todos los reportes activos para ubicarlos geográficamente
        $reportes = Reporte::with('municipio')
            ->whereIn('estado', ['Pendiente', 'En progreso'])
            ->select('folio', 'latitud', 'longitud', 'categoria', 'estado', 'descripcion', 'created_at')
            ->get();

        return view('admin.mapa.index', compact('reportes'));
    }
}