<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricaController extends Controller
{
    public function index(Request $request)
    {
        $municipio_id = auth()->user()->municipio_id;
        $periodo = $request->get('periodo', 'este_mes'); // Por defecto este mes

        $query = Reporte::where('municipio_id', $municipio_id);

        // --- MOTOR DE FILTRADO TEMPORAL ---
        $tituloPeriodo = 'Este Mes';
        switch ($periodo) {
            case 'mes_anterior':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                      ->whereYear('created_at', Carbon::now()->subMonth()->year);
                $tituloPeriodo = 'Mes Anterior';
                break;
            case 'trimestre':
                $query->whereBetween('created_at', [Carbon::now()->subMonths(3)->startOfDay(), Carbon::now()->endOfDay()]);
                $tituloPeriodo = 'Último Trimestre';
                break;
            case 'este_anio':
                $query->whereYear('created_at', Carbon::now()->year);
                $tituloPeriodo = 'Año en Curso (' . Carbon::now()->year . ')';
                break;
            case 'historico':
                $tituloPeriodo = 'Histórico Total';
                break;
            default: // este_mes
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
        }

        $reportes = $query->get();

        // --- CÁLCULO DE KPIs ---
        $total = $reportes->count();
        $resueltos = $reportes->where('estado', 'Resuelto')->count();
        $tasaResolucion = $total > 0 ? round(($resueltos / $total) * 100) : 0;

        // Tiempo promedio de atención (Solo reportes resueltos)
        $diasPromedio = 0;
        if ($resueltos > 0) {
            $totalHoras = 0;
            foreach ($reportes->where('estado', 'Resuelto') as $rep) {
                $totalHoras += $rep->created_at->diffInHours($rep->updated_at);
            }
            $diasPromedio = round(($totalHoras / $resueltos) / 24, 1);
        }

        // --- PREPARACIÓN DE DATOS PARA GRÁFICAS ---
        // 1. Categorías
        $porCategoria = $reportes->groupBy('categoria')->map->count();
        $labelsCategoria = $porCategoria->keys()->toJson();
        $dataCategoria = $porCategoria->values()->toJson();

        // 2. Estados
        $porEstado = $reportes->groupBy('estado')->map->count();
        $labelsEstado = $porEstado->keys()->toJson();
        $dataEstado = $porEstado->values()->toJson();

        // 3. Tendencia temporal (Reportes por día/mes dependiendo del filtro)
        $formatoFecha = $periodo == 'este_anio' || $periodo == 'historico' ? 'Y-m' : 'Y-m-d';
        $tendencia = $reportes->groupBy(function($date) use ($formatoFecha) {
            return Carbon::parse($date->created_at)->format($formatoFecha);
        })->map->count()->sortKeys();
        
        $labelsTendencia = $tendencia->keys()->toJson();
        $dataTendencia = $tendencia->values()->toJson();

        return view('admin.metricas.index', compact(
            'periodo', 'tituloPeriodo', 'total', 'resueltos', 'tasaResolucion', 'diasPromedio',
            'labelsCategoria', 'dataCategoria', 'labelsEstado', 'dataEstado', 'labelsTendencia', 'dataTendencia'
        ));
    }

    public function generarReporte(Request $request)
    {
        $municipio = auth()->user()->municipio;
        $periodo = $request->get('periodo', 'este_mes');
        
        $query = Reporte::where('municipio_id', $municipio->id);

        $tituloPeriodo = 'Este Mes';
        switch ($periodo) {
            case 'mes_anterior':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year);
                $tituloPeriodo = 'Mes Anterior (' . Carbon::now()->subMonth()->locale('es')->monthName . ')';
                break;
            case 'trimestre':
                $query->whereBetween('created_at', [Carbon::now()->subMonths(3)->startOfDay(), Carbon::now()->endOfDay()]);
                $tituloPeriodo = 'Último Trimestre';
                break;
            case 'este_anio':
                $query->whereYear('created_at', Carbon::now()->year);
                $tituloPeriodo = 'Año en Curso (' . Carbon::now()->year . ')';
                break;
            default:
                $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
                $tituloPeriodo = 'Mes Actual (' . Carbon::now()->locale('es')->monthName . ' ' . Carbon::now()->year . ')';
                break;
        }

        $reportes = $query->get();
        $total = $reportes->count();
        $resueltos = $reportes->where('estado', 'Resuelto')->count();
        $tasaResolucion = $total > 0 ? round(($resueltos / $total) * 100) : 0;
        
        $diasPromedio = 0;
        if ($resueltos > 0) {
            $totalHoras = 0;
            foreach ($reportes->where('estado', 'Resuelto') as $rep) {
                $totalHoras += $rep->created_at->diffInHours($rep->updated_at);
            }
            $diasPromedio = round(($totalHoras / $resueltos) / 24, 1);
        }

        $porCategoria = $reportes->groupBy('categoria')->map->count()->sortDesc();
        $categoriaCritica = $porCategoria->keys()->first() ?? 'Ninguna';
        
        // Insights
        $textoEficiencia = $tasaResolucion >= 70 
            ? "El municipio mantiene una alta eficiencia resolviendo el {$tasaResolucion}% de las incidencias." 
            : "Existe un área de oportunidad, con una tasa de resolución del {$tasaResolucion}%. Se sugiere optimizar los recursos en campo.";

        $textoTiempo = $diasPromedio > 0 
            ? "El tiempo promedio de respuesta institucional es de {$diasPromedio} días desde la captura hasta su resolución."
            : "No hay suficientes reportes resueltos para calcular el tiempo promedio de respuesta.";

        $textoCategoria = $total > 0 
            ? "La categoría que demanda mayor atención es '{$categoriaCritica}'. Se sugiere programar mantenimiento preventivo en esta área." 
            : "Sin datos críticos en este periodo.";

        return view('admin.metricas.reporte', compact(
            'municipio', 'tituloPeriodo', 'total', 'resueltos', 'tasaResolucion', 'diasPromedio',
            'porCategoria', 'textoEficiencia', 'textoTiempo', 'textoCategoria', 'periodo'
        ));
    }
}