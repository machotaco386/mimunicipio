<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Models\Cuadrilla;
use Illuminate\Http\Request;

class ReporteAdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = Reporte::with(['municipio', 'area', 'cuadrilla'])
                        ->where('municipio_id', $user->municipio_id);
        
        // Seguridad: El coordinador solo ve los reportes de su área
        if ($user->rol === 'coordinador' && $user->area_id) {
            $query->where('area_id', $user->area_id);
        }

        $reportes = $query->get();

        $pendientes = $reportes->where('estado', 'Pendiente')->sortBy('created_at');
        $enProgreso = $reportes->where('estado', 'En progreso')->sortBy('created_at');
        $resueltos = $reportes->where('estado', 'Resuelto')->sortByDesc('updated_at')->take(50);
        
        // CORRECCIÓN: Agregamos ->with('trabajadores') para que viajen en el JSON
        $cuadrillasQuery = Cuadrilla::with('trabajadores')->where('activa', true)
            ->whereHas('area', function($q) use ($user) {
                $q->where('municipio_id', $user->municipio_id);
            });

        if ($user->rol === 'coordinador' && $user->area_id) {
            $cuadrillasQuery->where('area_id', $user->area_id);
        }
        
        $cuadrillas = $cuadrillasQuery->get();

        // Enviamos la variable $cuadrillas a la vista
        return view('admin.reportes.index', compact('pendientes', 'enProgreso', 'resueltos', 'cuadrillas'));
    }

    public function actualizarEstado(Request $request, Reporte $reporte)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,En progreso,Resuelto',
            'cuadrilla_id' => 'nullable|exists:cuadrillas,id'
        ]);

        $user = auth()->user();
        if ($user->rol === 'coordinador' && $reporte->area_id !== $user->area_id) {
            abort(403, 'Acceso denegado: Este reporte pertenece a otra área.');
        }

        $reporte->update([
            'estado' => $request->estado,
            'cuadrilla_id' => $request->cuadrilla_id
        ]);

        return back()->with('success', 'El reporte ' . $reporte->folio . ' ha sido actualizado.');
    }
}