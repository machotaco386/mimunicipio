<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReporteController extends Controller
{
    /**
     * Muestra el formulario público detectando el municipio desde la URL.
     */
    public function create($municipioParam = 'mexticacan')
    {
        $municipios = Municipio::orderBy('nombre')->get();
        
        // 1. Buscamos el municipio ignorando si escriben con mayúsculas en la URL
        $municipioSeleccionado = Municipio::where('nombre', 'LIKE', "%{$municipioParam}%")->first();
        
        // Si escriben una URL falsa (ej. /m/paris), los regresamos a Mexticacán por seguridad
        if (!$municipioSeleccionado) {
            $municipioSeleccionado = Municipio::where('nombre', 'Mexticacán')->first();
        }

        // 2. Diccionario de Coordenadas (Para no tener que tocar tu base de datos)
        $coordenadas = [
            'Mexticacán'  => ['lat' => 21.2667, 'lng' => -102.8167, 'zoom' => 15],
            'Teocaltiche' => ['lat' => 21.4284, 'lng' => -102.5786, 'zoom' => 14],
            'Nochistlán'  => ['lat' => 21.3639, 'lng' => -102.8464, 'zoom' => 14],
        ];

        // 3. Extraemos la configuración del mapa
        $mapaData = $coordenadas[$municipioSeleccionado->nombre] ?? ['lat' => 21.2667, 'lng' => -102.8167, 'zoom' => 14];

        return view('reportes.create', compact('municipios', 'municipioSeleccionado', 'mapaData'));
    }

    /**
     * Guarda el reporte en la base de datos y almacena la foto.
     */
    public function store(Request $request)
    {
        // --- 1. DEFENSA ANTI-SPAM (Límite Diario y Cooldown por IP) ---
        $ip = $request->ip();
        $hoy = now()->format('Y-m-d');
        
        $llaveCooldown = 'cooldown_reporte_' . $ip;
        if (Cache::has($llaveCooldown)) {
            return back()->withErrors(['spam' => 'Por favor, espera al menos 30 minutos antes de enviar otro reporte para evitar saturación del servidor.'])->withInput();
        }

        $llaveDiaria = 'reportes_diarios_' . $ip . '_' . $hoy;
        $reportesHoy = Cache::get($llaveDiaria, 0);
        
        if ($reportesHoy >= 3) {
            return back()->withErrors(['limite' => 'Por seguridad, solo se permiten 3 reportes por día. Gracias por ayudar a mejorar tu municipio, vuelve mañana.'])->withInput();
        }

        // --- 2. VALIDACIÓN ESTRICTA ---
        $validado = $request->validate([
            'municipio_id' => 'required|exists:municipios,id',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'descripcion' => 'required|string|max:1000',
            'categoria' => 'required|in:Bache,Luz,Basura,Fuga de agua,Drenaje,Otro',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', 
            'telefono_contacto' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('foto')) {
            $validado['ruta_foto'] = $request->file('foto')->store('fotos_reportes', 'public');
        }

        $reporte = Reporte::create($validado);

        // --- 3. REGISTRAR GOLPE DE SEGURIDAD ---
        Cache::put($llaveCooldown, true, now()->addMinutes(30));
        Cache::put($llaveDiaria, $reportesHoy + 1, now()->endOfDay());

        // --- 4. DISPARAR NOTIFICACIÓN ---
        $usuariosMunicipio = \App\Models\User::where('municipio_id', $reporte->municipio_id)->get();
        \Illuminate\Support\Facades\Notification::send($usuariosMunicipio, new \App\Notifications\NuevoReporteNotificacion($reporte));

        return redirect()->route('reportes.consulta')->with([
            'success' => '¡Reporte enviado correctamente!',
            'folio_generado' => $reporte->folio
        ]);
    }

    public function consulta()
    {
        return view('reportes.consulta');
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'folio' => 'required|string|max:20'
        ]);

        $reporte = Reporte::with('municipio')->where('folio', $request->folio)->first();

        if (!$reporte) {
            return back()->with('error', 'No se encontró ningún reporte con el folio ingresado.');
        }

        return view('reportes.consulta', compact('reporte'));
    }
}