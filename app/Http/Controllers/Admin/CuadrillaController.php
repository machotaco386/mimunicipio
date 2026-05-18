<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuadrilla;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;

class CuadrillaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $queryCuadrillas = Cuadrilla::with(['area', 'trabajadores']);
        $queryTrabajadores = User::where('municipio_id', $user->municipio_id)->where('rol', 'trabajador');
        $areas = collect();

        if ($user->rol === 'coordinador') {
            $queryCuadrillas->where('area_id', $user->area_id);
            $queryTrabajadores->where('area_id', $user->area_id);
            $areas = Area::where('id', $user->area_id)->get();
        } else {
            $queryCuadrillas->whereHas('area', function($q) use ($user) {
                $q->where('municipio_id', $user->municipio_id);
            });
            $areas = Area::where('municipio_id', $user->municipio_id)->get();
        }

        $cuadrillas = $queryCuadrillas->get();
        $trabajadoresDisponibles = $queryTrabajadores->get();

        return view('admin.cuadrillas.index', compact('cuadrillas', 'trabajadoresDisponibles', 'areas'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'icono' => 'required|string|max:50',
            'area_id' => $user->rol === 'super_admin' ? 'required|exists:areas,id' : 'nullable'
        ]);

        Cuadrilla::create([
            'nombre' => $request->nombre,
            'icono' => $request->icono,
            'area_id' => $user->rol === 'coordinador' ? $user->area_id : $request->area_id,
            'activa' => true
        ]);

        return back()->with('success', 'Cuadrilla operativa creada.');
    }

    public function update(Request $request, Cuadrilla $cuadrilla)
    {
        $user = auth()->user();
        if ($user->rol === 'coordinador' && $cuadrilla->area_id !== $user->area_id) abort(403);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'icono' => 'required|string|max:50',
        ]);

        $cuadrilla->update([
            'nombre' => $request->nombre,
            'icono' => $request->icono,
        ]);

        return back()->with('success', 'Datos de la cuadrilla actualizados.');
    }

    public function asignarTrabajadores(Request $request, Cuadrilla $cuadrilla)
    {
        $request->validate([
            'trabajadores' => 'nullable|array',
            'trabajadores.*' => 'exists:users,id'
        ]);

        $user = auth()->user();
        if ($user->rol === 'coordinador' && $cuadrilla->area_id !== $user->area_id) abort(403);

        $cuadrilla->trabajadores()->sync($request->trabajadores ?? []);

        return back()->with('success', 'Trabajadores asignados a la cuadrilla exitosamente.');
    }

    public function destroy(Cuadrilla $cuadrilla)
    {
        $user = auth()->user();
        if ($user->rol === 'coordinador' && $cuadrilla->area_id !== $user->area_id) abort(403);

        if ($cuadrilla->reportes()->whereIn('estado', ['Pendiente', 'En progreso'])->count() > 0) {
            return back()->with('error', 'No puedes eliminar una cuadrilla con reportes activos.');
        }

        $cuadrilla->delete();
        return back()->with('success', 'Cuadrilla eliminada.');
    }
}