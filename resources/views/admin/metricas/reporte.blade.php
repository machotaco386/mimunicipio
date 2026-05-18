<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ejecutivo - {{ $municipio->nombre }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS CRÍTICO PARA IMPRESIÓN A4 SIN ERRORES */
        @page { size: A4; margin: 15mm; }
        body { background-color: #f8fafc; font-family: ui-sans-serif, system-ui, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .hoja-a4 { background: white; max-width: 210mm; margin: 0 auto; padding: 15mm; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        
        /* Evitar que las cajas se corten a la mitad en el cambio de página */
        .evitar-salto { page-break-inside: avoid; break-inside: avoid; margin-bottom: 20px; }
        
        /* Forzar alturas estrictas en los canvas para que Chart.js no los desborde al imprimir */
        .chart-container { position: relative; width: 100%; height: 250px; display: flex; justify-content: center; }
        canvas { max-height: 250px !important; width: auto !important; }

        @media print {
            body { background-color: white; }
            .hoja-a4 { box-shadow: none; margin: 0; padding: 0; max-width: 100%; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="py-10 text-slate-800">

    <div class="fixed bottom-10 right-10 no-print z-50">
        <button onclick="window.print()" class="bg-[#1A365D] hover:bg-blue-900 text-white px-8 py-4 rounded-full shadow-2xl font-bold flex items-center gap-2 transition-transform transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256"><path d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,51.31,188.69,80H160ZM200,216H56V40h88V88a8,8,0,0,0,8,8h48V216ZM168,152v24a8,8,0,0,1-16,0v-8H136v8a8,8,0,0,1-16,0v-24a8,8,0,0,1,8-8h32A8,8,0,0,1,168,152Zm-16,0H136v8h16Z"></path></svg>
            Guardar como PDF
        </button>
        <p class="text-xs text-center text-slate-500 mt-2 bg-white/80 p-1 rounded">Asegúrate de marcar "Gráficos de fondo" en Ajustes.</p>
    </div>

    <div class="hoja-a4">
        
        <!-- ENCABEZADO INSTITUCIONAL -->
        <div class="border-b-4 border-[#1A365D] pb-4 mb-6 flex justify-between items-end evitar-salto">
            <div>
                <h1 class="text-2xl font-black text-[#1A365D]">Reporte Ejecutivo de Gestión Urbana</h1>
                <p class="text-base text-slate-500 uppercase tracking-widest mt-1 font-bold">{{ $municipio->nombre }}</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-[#84CC16] text-lg">{{ $tituloPeriodo }}</p>
                <p class="text-xs text-slate-400 font-medium">Emisión: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- RESUMEN EJECUTIVO (TEXTOS INTELIGENTES) -->
        <div class="evitar-salto">
            <div class="bg-[#f8fafc] p-5 rounded-xl border border-slate-200 text-sm leading-relaxed text-justify">
                <p class="mb-2">Durante el periodo evaluado (<strong>{{ $tituloPeriodo }}</strong>), el sistema de inteligencia MiMunicipio procesó un total de <strong>{{ $total }} reportes ciudadanos</strong>.</p>
                <p class="mb-2"><strong>Eficiencia Institucional:</strong> {{ $textoEficiencia }}</p>
                <p class="mb-2"><strong>Tiempo de Respuesta:</strong> {{ $textoTiempo }}</p>
                <p><strong>Análisis de Áreas:</strong> {{ $textoCategoria }}</p>
            </div>
        </div>

        <!-- KPIs PRINCIPALES (GRID) -->
        <div class="grid grid-cols-4 gap-4 mt-6 evitar-salto">
            <div class="bg-white border-2 border-slate-100 p-4 rounded-xl text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Total Reportes</p>
                <p class="text-2xl font-black text-[#1A365D] mt-1">{{ $total }}</p>
            </div>
            <div class="bg-white border-2 border-slate-100 p-4 rounded-xl text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Tasa Resolución</p>
                <p class="text-2xl font-black text-[#84CC16] mt-1">{{ $tasaResolucion }}%</p>
            </div>
            <div class="bg-white border-2 border-slate-100 p-4 rounded-xl text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Folios Resueltos</p>
                <p class="text-2xl font-black text-blue-500 mt-1">{{ $resueltos }}</p>
            </div>
            <div class="bg-white border-2 border-slate-100 p-4 rounded-xl text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase">T. Promedio (Días)</p>
                <p class="text-2xl font-black text-amber-500 mt-1">{{ $diasPromedio }}</p>
            </div>
        </div>

        <!-- GRÁFICAS -->
        <div class="grid grid-cols-2 gap-6 mt-6 evitar-salto">
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <h3 class="font-bold text-center text-slate-600 mb-2 uppercase text-xs">Distribución por Área Administrativa</h3>
                <div class="chart-container"><canvas id="pdfChartCat"></canvas></div>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <h3 class="font-bold text-center text-slate-600 mb-4 uppercase text-xs">Estatus Operativo Detallado</h3>
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-slate-200"><th class="py-2 text-[#1A365D]">Estado</th><th class="py-2 text-right">Volumen</th><th class="py-2 text-right">Porcentaje</th></tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-100"><td class="py-2.5 font-medium text-amber-600">Pendientes / En Proceso</td><td class="py-2.5 text-right font-bold">{{ $total - $resueltos }}</td><td class="py-2.5 text-right">{{ $total > 0 ? round((($total - $resueltos)/$total)*100) : 0 }}%</td></tr>
                        <tr class="border-b border-slate-100"><td class="py-2.5 font-bold text-[#84CC16]">Resueltos Oficialmente</td><td class="py-2.5 text-right font-bold text-[#84CC16]">{{ $resueltos }}</td><td class="py-2.5 text-right font-bold text-[#84CC16]">{{ $tasaResolucion }}%</td></tr>
                        <tr class="bg-slate-50"><th class="py-2 px-2 rounded-l-md">Total General</th><th class="py-2 text-right">{{ $total }}</th><th class="py-2 text-right rounded-r-md">100%</th></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FIRMAS DE VALIDACIÓN -->
        <div class="mt-16 flex justify-between px-16 evitar-salto">
            <div class="text-center w-56">
                <hr class="border-slate-800 mb-2 border-t-2">
                <p class="text-[10px] font-bold text-slate-800 uppercase tracking-wide">Presidente Municipal</p>
                <p class="text-[9px] text-slate-400">Revisión y Vo.Bo.</p>
            </div>
            <div class="text-center w-56">
                <hr class="border-slate-800 mb-2 border-t-2">
                <p class="text-[10px] font-bold text-slate-800 uppercase tracking-wide">Dirección de Obras Públicas</p>
                <p class="text-[9px] text-slate-400">Validación Operativa</p>
            </div>
        </div>

        <div class="mt-8 text-center text-[9px] text-slate-400 uppercase tracking-wider evitar-salto border-t border-slate-100 pt-4">
            Documento de Inteligencia Gubernamental generado de forma automatizada por el motor analítico de MiMunicipio.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // DESACTIVAR ANIMACIONES: Crítico para que el gráfico ya esté dibujado al llamar a window.print()
            Chart.defaults.animation = false;
            Chart.defaults.font.family = "'Inter', 'system-ui', sans-serif";

            const labels = {!! $porCategoria->keys()->toJson() !!};
            const data = {!! $porCategoria->values()->toJson() !!};

            new Chart(document.getElementById('pdfChartCat'), {
                type: 'pie', // Cambiado a pie para mejor legibilidad en impresión blanco y negro/color
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#1A365D', '#84CC16', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } 
                    }
                }
            });

            // Retardo de 800ms para asegurar renderizado completo del DOM antes del popup de impresión
            setTimeout(() => { window.print(); }, 800);
        });
    </script>
</body>
</html>