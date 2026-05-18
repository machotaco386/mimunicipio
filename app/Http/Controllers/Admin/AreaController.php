<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        // Solo el Super Admin debería gestionar las áreas
        if (auth()->user()->rol !== 'super_admin') {
            abort(403, 'Acceso exclusivo para el Administrador del Municipio.');
        }

        $areas = Area::withCount(['usuarios', 'cuadrillas'])
                    ->where('municipio_id', auth()->user()->municipio_id)
                    ->get();

        return view('admin.areas.index', compact('areas'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->rol !== 'super_admin') abort(403);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        Area::create([
            'municipio_id' => auth()->user()->municipio_id,
            'nombre' => $request->nombre,
            'color' => $request->color,
        ]);

        return back()->with('success', 'Departamento creado correctamente.');
    }

    public function destroy(Area $area)
    {
        if (auth()->user()->rol !== 'super_admin' || $area->municipio_id !== auth()->user()->municipio_id) abort(403);
        
        if ($area->usuarios()->count() > 0 || $area->reportes()->count() > 0) {
            return back()->with('error', 'No puedes eliminar un área que ya tiene usuarios o reportes asignados.');
        }

        $area->delete();
        return back()->with('success', 'Departamento eliminado.');
    }
}