<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = User::with('area')->where('municipio_id', $user->municipio_id);
        $areas = collect();
        
        if ($user->rol === 'coordinador') {
            // El coordinador solo puede ver y gestionar a los "trabajadores" de su propio departamento
            $query->where('area_id', $user->area_id)
                  ->where('rol', 'trabajador');
            $areas = Area::where('id', $user->area_id)->get();
        } else {
            // El Super Admin ve a todos
            $areas = Area::where('municipio_id', $user->municipio_id)->get();
        }
        
        $usuarios = $query->get();
        
        return view('admin.usuarios.index', compact('usuarios', 'areas'));
    }

    public function store(Request $request)
    {
        $userAuth = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|in:super_admin,coordinador,trabajador',
            'area_id' => 'nullable|exists:areas,id'
        ]);

        // REGLAS DE SEGURIDAD ESTRICTA
        if ($userAuth->rol === 'coordinador') {
            // Un coordinador no puede crear a otro coordinador ni a un super_admin, y no puede meter gente a otras áreas
            if ($request->rol !== 'trabajador' || $request->area_id != $userAuth->area_id) {
                abort(403, 'Alerta de Seguridad: No tienes permisos para esta acción.');
            }
        }

        User::create([
            'municipio_id' => $userAuth->municipio_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'area_id' => $request->area_id,
        ]);

        return back()->with('success', 'Personal registrado correctamente.');
    }

    public function destroy(User $usuario)
    {
        $userAuth = auth()->user();

        // Seguridad para borrado
        if ($usuario->id === $userAuth->id) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }
        
        if ($userAuth->rol === 'coordinador') {
            if ($usuario->rol !== 'trabajador' || $usuario->area_id !== $userAuth->area_id) {
                abort(403);
            }
        }

        // Si está asignado a cuadrillas, lo desvinculamos primero
        $usuario->cuadrillas()->detach();
        $usuario->delete();

        return back()->with('success', 'Usuario dado de baja del sistema.');
    }
}