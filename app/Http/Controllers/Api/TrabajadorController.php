<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Reporte;

class TrabajadorController extends Controller
{
    // 1. LOGIN DESDE LA APP MÓVIL
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('cuadrillas')->where('email', $request->email)->first();

        // Verificamos credenciales y rol
        if (!$user || !Hash::check($request->password, $user->password) || $user->rol !== 'trabajador') {
            return response()->json(['message' => 'Credenciales incorrectas o acceso no autorizado.'], 401);
        }

        // Generamos el Token de acceso
        $token = $user->createToken('app-movil')->plainTextToken;

        // Obtenemos los nombres de todas sus cuadrillas para mostrarlos en la app si se requiere
        $nombresCuadrillas = $user->cuadrillas->pluck('nombre')->implode(', ');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'cuadrillas_asignadas' => $nombresCuadrillas ?: 'Sin cuadrillas asignadas'
            ]
        ]);
    }

    // 2. CERRAR SESIÓN EN EL CELULAR
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente.']);
    }

    // 3. OBTENER LA RUTA DE HOY (FOLIOS DE TODAS SUS CUADRILLAS)
    public function misReportes(Request $request)
    {
        $user = $request->user();
        
        // Extraemos TODOS los IDs de las cuadrillas a las que pertenece el trabajador
        $cuadrillaIds = $user->cuadrillas()->pluck('cuadrillas.id')->toArray();

        if (empty($cuadrillaIds)) {
            return response()->json([]); // Si no está en ninguna cuadrilla, devolvemos vacío
        }

        // Buscamos los reportes que estén asignados a CUALQUIERA de sus cuadrillas
        // y adjuntamos la información completa de relaciones (Eager Loading)
        $reportes = Reporte::with(['municipio', 'area', 'cuadrilla'])
            ->whereIn('cuadrilla_id', $cuadrillaIds)
            ->whereIn('estado', ['Pendiente', 'En progreso'])
            ->get();

        return response()->json($reportes);
    }

    // 4. RESOLVER EL FOLIO Y SUBIR LA FOTO DESDE LA CALLE
    public function resolverReporte(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120', 
        ]);

        $user = $request->user();
        $cuadrillaIds = $user->cuadrillas()->pluck('cuadrillas.id')->toArray();
        
        $reporte = Reporte::findOrFail($id);

        // Seguridad: Verificar que el reporte pertenezca a alguna de sus cuadrillas actuales
        if (!in_array($reporte->cuadrilla_id, $cuadrillaIds)) {
            return response()->json(['message' => 'Este folio no pertenece a ninguna de tus cuadrillas asignadas.'], 403);
        }

        // Guardar la foto
        $ruta = $request->file('foto')->store('fotos_evidencia', 'public');

        // Actualizar el estado
        $reporte->update([
            'estado' => 'Resuelto',
            'ruta_foto' => $ruta 
        ]);

        return response()->json(['message' => 'Folio resuelto exitosamente.']);
    }
}