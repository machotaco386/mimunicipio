<?php
// Archivo: app/Http/Controllers/Master/MasterController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Municipio;
use App\Models\User;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterController extends Controller
{
    public function index()
    {
        $municipios = Municipio::withCount(['usuarios', 'reportes'])->orderByDesc('id')->get();
        
        $totalClientes = $municipios->count();
        $totalUsuariosSaaS = User::where('rol', '!=', 'master_root')->count();
        $totalReportesPais = Reporte::count();

        return view('master.index', compact('municipios', 'totalClientes', 'totalUsuariosSaaS', 'totalReportesPais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_municipio' => 'required|string|max:255|unique:municipios,nombre',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
        ]);

        // 1. SEGURIDAD: Generamos una contraseña criptográfica aleatoria de 12 caracteres
        $passwordPlana = Str::random(12);

        DB::transaction(function() use ($request, $passwordPlana) {
            $municipio = Municipio::create([
                'nombre' => $request->nombre_municipio
            ]);

            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($passwordPlana), // Se encripta instantáneamente
                'rol' => 'super_admin',
                'municipio_id' => $municipio->id,
                'area_id' => null 
            ]);
        });

        // 2. Pasamos la contraseña plana SOLO UNA VEZ a la vista usando variables de sesión flash
        return back()
            ->with('success', 'Entorno de cliente desplegado exitosamente.')
            ->with('credenciales', [
                'municipio' => $request->nombre_municipio,
                'admin' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => $passwordPlana
            ]);
    }
}