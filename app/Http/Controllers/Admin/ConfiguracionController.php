<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Reporte;
use App\Models\User;

class ConfiguracionController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $usuario */
        $usuario = auth()->user();
        $municipio = $usuario->municipio;
        
        // Extraemos un par de métricas globales para la tarjeta de información del Tenant
        $totalReportes = Reporte::where('municipio_id', $municipio->id)->count();
        $totalUsuarios = User::where('municipio_id', $municipio->id)->count();

        return view('admin.configuracion.index', compact('usuario', 'municipio', 'totalReportes', 'totalUsuarios'));
    }

    public function updatePerfil(Request $request)
    {
        /** @var \App\Models\User $usuario */
        $usuario = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $usuario->id,
            // confirmed exige que haya un campo 'password_confirmation' en el formulario
            'password' => 'nullable|string|min:6|confirmed', 
        ]);

        $usuario->name = $request->name;
        $usuario->email = $request->email;
        
        // Solo actualizamos la contraseña si el usuario escribió una nueva
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return back()->with('success', 'Tus datos de seguridad han sido actualizados correctamente.');
    }
}