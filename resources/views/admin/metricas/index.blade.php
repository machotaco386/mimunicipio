@extends('layouts.admin')

@section('title', 'Análisis y Métricas - MiMunicipio')
@section('header_title', 'Inteligencia Gubernamental')

@section('content')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-institucional">Tablero de Rendimiento</h2>
        <p class="text-sm text-slate-500">Métricas clave del periodo: <strong class="text-institucional">{{ $tituloPeriodo }}</strong></p>
    </div>
    
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Motor de Filtro -->
        <form action="{{ route('admin.metricas.index') }}" method="GET" class="flex bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <select name="periodo" class="text-sm border-none bg-transparent py-2.5 pl-4 pr-8 focus:ring-0 outline-none text-slate-700 font-medium cursor-pointer" onchange="this.form.submit()">
                <option value="este_mes" {{ $periodo == 'este_mes' ? 'selected' : '' }}>Este Mes</option>
                <option value="mes_anterior" {{ $periodo == 'mes_anterior' ? 'selected' : '' }}>Mes Anterior</option>
                <option value="trimestre" {{ $periodo == 'trimestre' ? 'selected' : '' }}>Último Trimestre</option>
                <option value="este_anio" {{ $periodo == 'este_anio' ? 'selected' : '' }}>Año en Curso</option>
                <option value="historico" {{ $periodo == 'historico' ? 'selected' : '' }}>Histórico Completo</option>
            </select>
        </form>

        <!-- Botón Generar PDF (pasa el filtro actual por URL) -->
        <a href="{{ route('admin.metricas.reporte', ['periodo' => $periodo]) }}" target="_blank" class="bg-institucional hover:bg-blue-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2 text-sm whitespace-nowrap">
            <i class="ph-bold ph-file-pdf text-lg"></i> Exportar PDF
        </a>
    </div>
</div>

<!-- TARJETAS KPI SUPERIORES -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Ingresos</p>
        <div class="flex items-end gap-3"><h3 class="text-3xl font-bold text-institucional">{{ $total }}</h3><i class="ph-fill ph-files text-slate-300 text-2xl mb-1"></i></div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tasa de Resolución</p>
        <div class="flex items-end gap-3"><h3 class="text-3xl font-bold text-accion">{{ $tasaResolucion }}%</h3><i class="ph-fill ph-check-circle text-slate-300 text-2xl mb-1"></i></div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Reportes Resueltos</p>
        <div class="flex items-end gap-3"><h3 class="text-3xl font-bold text-blue-500">{{ $resueltos }}</h3><i class="ph-fill ph-wrench text-slate-300 text-2xl mb-1"></i></div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tiempo Promedio</p>
        <div class="flex items-end gap-3"><h3 class="text-3xl font-bold text-amber-500">{{ $diasPromedio }} <span class="text-lg">días</span></h3><i class="ph-fill ph-clock-countdown text-slate-300 text-2xl mb-1"></i></div>
    </div>
</div>

<!-- SECCIÓN DE GRÁFICAS AVANZADAS -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Evolución Temporal (Ocupa 2 columnas) -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-institucional mb-4 flex items-center gap-2">
            <i class="ph-fill ph-trend-up text-accion"></i> Evolución de Incidencias
        </h3>
        <div class="relative h-72 w-full">
            <canvas id="chartTendencia"></canvas>
        </div>
    </div>

    <!-- Estatus de Atención -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-institucional mb-4 flex items-center gap-2">
            <i class="ph-fill ph-chart-bar text-accion"></i> Estatus Operativo
        </h3>
        <div class="relative h-72 w-full">
            <canvas id="chartEstado"></canvas>
        </div>
    </div>

    <!-- Incidencias por Categoría -->
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-institucional mb-4 flex items-center gap-2">
            <i class="ph-fill ph-chart-pie-slice text-accion"></i> Distribución por Área / Categoría
        </h3>
        <div class="relative h-80 w-full flex justify-center">
            <canvas id="chartCategoria"></canvas>
        </div>
    </div>

</div>
@endsection

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Configuraciones globales de Chart.js para estilo SaaS
        Chart.defaults.font.family = "'Inter', 'system-ui', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 32, 56, 0.9)';
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        const labelsCat = {!! $labelsCategoria !!};
        const dataCat = {!! $dataCategoria !!};
        const labelsEst = {!! $labelsEstado !!};
        const dataEst = {!! $dataEstado !!};
        const labelsTen = {!! $labelsTendencia !!};
        const dataTen = {!! $dataTendencia !!};

        // 1. Gráfica de Tendencia (Línea)
        new Chart(document.getElementById('chartTendencia'), {
            type: 'line',
            data: {
                labels: labelsTen,
                datasets: [{
                    label: 'Reportes Recibidos',
                    data: dataTen,
                    borderColor: '#1A365D',
                    backgroundColor: 'rgba(26, 54, 93, 0.1)',
                    borderWidth: 3,
                    tension: 0.4, // Curvas suaves
                    fill: true,
                    pointBackgroundColor: '#84CC16',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });

        // 2. Gráfica de Estados (Barras)
        new Chart(document.getElementById('chartEstado'), {
            type: 'bar',
            data: {
                labels: labelsEst,
                datasets: [{
                    data: dataEst,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#84CC16'], // Ambar, Azul, Verde
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });

        // 3. Gráfica Circular (Categorías)
        new Chart(document.getElementById('chartCategoria'), {
            type: 'doughnut',
            data: {
                labels: labelsCat,
                datasets: [{
                    data: dataCat,
                    backgroundColor: ['#1A365D', '#84CC16', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'right' } }
            }
        });
    });
</script>