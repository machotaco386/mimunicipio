@extends('layouts.admin')

@section('title', 'Mapa Analítico - MiMunicipio')
@section('header_title', 'Centro de Monitoreo')

@section('content')
<!-- Motor WebGL MapLibre CSS -->
<link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet" />

<style>
    /* Efecto de cristal para los paneles del Dashboard */
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.6);
    }
    .dark .glass-panel {
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Popups estilo nativo profesional */
    .maplibregl-popup-content {
        padding: 0;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .dark .maplibregl-popup-content { background: #1e293b; border-color: #334155; color: #f8fafc; }
    .maplibregl-popup-close-button { top: 10px; right: 10px; color: #94a3b8; font-size: 20px; z-index: 10; }
    
    /* Scrollbar invisible global */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Scrollbar custom y delgada para las Categorías */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }

    /* Estilo del slider de inclinación */
    input[type=range].slider-inclinacion {
        -webkit-appearance: none;
        background: transparent;
    }
    input[type=range].slider-inclinacion::-webkit-slider-runnable-track {
        width: 100%;
        height: 6px;
        background: #cbd5e1;
        border-radius: 4px;
    }
    .dark input[type=range].slider-inclinacion::-webkit-slider-runnable-track { background: #475569; }
    input[type=range].slider-inclinacion::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #3b82f6;
        cursor: pointer;
        margin-top: -5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
</style>

<div id="contenedor-pantalla-completa" class="bg-slate-100 dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative transition-all duration-300" style="height: calc(100vh - 180px);">
    
    <!-- BARRA SUPERIOR DE HERRAMIENTAS (Top Bar) -->
    <div class="absolute top-4 left-4 right-4 z-[400] flex flex-wrap justify-between items-start gap-3 pointer-events-none">
        
        <div class="flex flex-wrap items-center gap-3">
            <!-- Controles Visuales (Modo Normal y Calor) -->
            <div class="glass-panel p-1.5 rounded-2xl shadow-lg flex flex-wrap pointer-events-auto items-center gap-1">
                <button onclick="cambiarModoVisual('puntos')" id="btn-modo-puntos" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-institucional text-white shadow-md transition-all flex items-center gap-2">
                    <i class="ph-bold ph-map-pin text-lg"></i> <span class="hidden sm:inline">Puntos</span>
                </button>
                <button onclick="cambiarModoVisual('calor')" id="btn-modo-calor" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:text-orange-600 hover:bg-orange-50 transition-all flex items-center gap-2 dark:text-slate-300 dark:hover:bg-slate-800">
                    <i class="ph-bold ph-fire text-lg"></i> <span class="hidden sm:inline">Mapa de Calor</span>
                </button>
                
                <div class="w-px h-6 bg-slate-200 mx-2 dark:bg-slate-600 hidden sm:block"></div>
                
                <!-- Interruptor 3D Moderno -->
                <div class="flex items-center gap-3 px-3 py-1.5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="toggle-3d" onchange="toggle3DModo(this.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-institucional"></div>
                        <span class="ml-2 text-sm font-bold text-slate-700 dark:text-slate-200 hidden sm:block">Activar 3D</span>
                    </label>
                </div>

                <!-- Control Deslizante de Inclinación (Oculto hasta activar 3D) -->
                <div id="contenedor-slider-3d" class="hidden items-center gap-2 px-3 py-1 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 ml-1 transition-all animate-fade-in">
                    <i class="ph-bold ph-caret-up text-slate-400 text-lg"></i>
                    <div class="flex flex-col justify-center">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1">Inclinación</span>
                        <div class="flex items-center gap-2">
                            <input type="range" id="slider-pitch" min="0" max="75" value="0" class="slider-inclinacion w-20 sm:w-28">
                            <span id="label-pitch" class="text-xs font-black text-institucional dark:text-blue-400 w-6">0°</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Novedad: Pastilla de Reportes Activos en el Mapa -->
            <div class="glass-panel px-4 py-2.5 rounded-2xl shadow-lg flex items-center gap-2.5 pointer-events-auto">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accion opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-accion"></span>
                </span>
                <div class="flex items-baseline gap-1.5">
                    <span id="kpi-total" class="font-black text-lg text-institucional dark:text-white leading-none">0</span>
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">En Vista</span>
                </div>
            </div>
        </div>

        <!-- Selector de Mapa Base (Derecha) -->
        <div class="flex items-center gap-3 pointer-events-auto">
            <div class="relative group">
                <button class="glass-panel px-5 py-2.5 rounded-xl shadow-lg text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-institucional transition-colors flex items-center gap-2">
                    <i class="ph-bold ph-stack text-lg text-accion"></i> <span class="hidden md:inline">Fondo del Mapa</span>
                </button>
                <div class="absolute top-full right-0 mt-2 p-2 w-56 glass-panel rounded-2xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right scale-95 group-hover:scale-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 py-1">Estilos Visuales</p>
                    <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="map_style" value="calles" checked onchange="cambiarMapaBase('calles')" class="text-institucional focus:ring-institucional w-4 h-4">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><i class="ph-bold ph-map-trifold text-blue-500"></i> Calles y Avenidas</span>
                    </label>
                    <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="map_style" value="oscuro" onchange="cambiarMapaBase('oscuro')" class="text-institucional focus:ring-institucional w-4 h-4">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><i class="ph-bold ph-moon text-indigo-500"></i> Vista Oscura</span>
                    </label>
                    <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="map_style" value="satelite" onchange="cambiarMapaBase('satelite')" class="text-institucional focus:ring-institucional w-4 h-4">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><i class="ph-bold ph-globe-hemisphere-west text-emerald-500"></i> Satélite Real</span>
                    </label>
                </div>
            </div>

            <!-- Botón Filtros (Solo visible en Móvil) -->
            <button onclick="document.getElementById('panel-filtros').classList.toggle('translate-x-full')" class="lg:hidden glass-panel px-4 py-2.5 rounded-xl shadow-lg text-sm font-bold text-institucional flex items-center gap-2">
                <i class="ph-bold ph-faders text-lg"></i>
            </button>
        </div>
    </div>

    <!-- PANEL LATERAL DE FILTROS -->
    <div id="panel-filtros" class="absolute top-20 right-4 lg:top-4 lg:left-4 lg:right-auto bottom-4 w-80 glass-panel rounded-3xl shadow-2xl z-[500] transform transition-transform duration-300 flex flex-col pointer-events-auto translate-x-full lg:translate-x-0 lg:mt-[65px]">
        
        <div class="p-6 border-b border-slate-200/50 dark:border-slate-700/50 flex justify-between items-center bg-white/60 dark:bg-slate-800/60 rounded-t-3xl">
            <div>
                <h3 class="font-black text-institucional dark:text-blue-400 text-xl leading-none mb-1">Filtros de Mapa</h3>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Ajusta la información visible</p>
            </div>
            <button onclick="document.getElementById('panel-filtros').classList.add('translate-x-full')" class="lg:hidden w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-300 transition">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-8">
            <!-- Filtro de Estatus -->
            <div>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2 uppercase tracking-wide"><i class="ph-bold ph-traffic-signal text-accion text-lg"></i> Estatus del Reporte</p>
                <div class="space-y-3">
                    
                    <label class="block cursor-pointer group">
                        <input type="checkbox" class="filtro-estado peer hidden" value="Pendiente" checked onchange="aplicarFiltros()">
                        <div class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 peer-checked:bg-white dark:peer-checked:bg-slate-800 peer-checked:border-amber-500 dark:peer-checked:border-amber-400 transition-all shadow-sm opacity-50 grayscale peer-checked:opacity-100 peer-checked:grayscale-0 hover:opacity-80 hover:grayscale-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="ph-bold ph-clock text-xl"></i></div>
                                <span class="font-bold text-slate-700 dark:text-slate-200 peer-checked:text-amber-700 dark:peer-checked:text-amber-400 transition-colors text-sm">Pendientes</span>
                            </div>
                            <span id="count-pendientes" class="font-black text-amber-600 dark:text-amber-400 text-lg">0</span>
                        </div>
                    </label>

                    <label class="block cursor-pointer group">
                        <input type="checkbox" class="filtro-estado peer hidden" value="En progreso" checked onchange="aplicarFiltros()">
                        <div class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 peer-checked:bg-white dark:peer-checked:bg-slate-800 peer-checked:border-blue-500 dark:peer-checked:border-blue-400 transition-all shadow-sm opacity-50 grayscale peer-checked:opacity-100 peer-checked:grayscale-0 hover:opacity-80 hover:grayscale-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="ph-bold ph-gear text-xl"></i></div>
                                <span class="font-bold text-slate-700 dark:text-slate-200 peer-checked:text-blue-700 dark:peer-checked:text-blue-400 transition-colors text-sm">En Progreso</span>
                            </div>
                            <span id="count-proceso" class="font-black text-blue-600 dark:text-blue-400 text-lg">0</span>
                        </div>
                    </label>

                    <label class="block cursor-pointer group">
                        <input type="checkbox" class="filtro-estado peer hidden" value="Resuelto" onchange="aplicarFiltros()">
                        <div class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 peer-checked:bg-white dark:peer-checked:bg-slate-800 peer-checked:border-green-500 dark:peer-checked:border-green-400 transition-all shadow-sm opacity-50 grayscale peer-checked:opacity-100 peer-checked:grayscale-0 hover:opacity-80 hover:grayscale-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="ph-bold ph-check-circle text-xl"></i></div>
                                <span class="font-bold text-slate-700 dark:text-slate-200 peer-checked:text-green-700 dark:peer-checked:text-green-400 transition-colors text-sm">Resueltos</span>
                            </div>
                            <span id="count-resueltos" class="font-black text-green-600 dark:text-green-400 text-lg">0</span>
                        </div>
                    </label>

                </div>
            </div>

            <hr class="border-slate-200/60 dark:border-slate-700/60">

            <!-- Filtro de Categorías Mosaico de Iconos -->
            <div>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2 uppercase tracking-wide"><i class="ph-bold ph-tag text-accion text-lg"></i> Categoría de Incidencia</p>
                <div class="grid grid-cols-3 gap-3 max-h-[30vh] overflow-y-auto p-1 custom-scrollbar" id="contenedor-categorias">
                    <!-- Dinámico JS -->
                </div>
            </div>
        </div>

        <!-- Footer Simplificado -->
        <div class="p-4 bg-slate-50/90 dark:bg-slate-800/90 rounded-b-3xl border-t border-slate-200/50 dark:border-slate-700/50 flex justify-center">
            <button onclick="resetearFiltros()" class="w-full py-3 bg-institucional hover:bg-blue-900 active:scale-95 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-md flex justify-center items-center gap-2">
                <i class="ph-bold ph-arrows-counter-clockwise text-lg"></i> Restablecer Filtros
            </button>
        </div>
    </div>

    <!-- MAPA -->
    <div id="full-map" class="w-full h-full z-10 bg-slate-100 dark:bg-slate-900"></div>
    
    <div id="loading-map" class="absolute inset-0 bg-slate-100 dark:bg-slate-900 z-50 flex flex-col items-center justify-center transition-opacity duration-500">
        <i class="ph-bold ph-spinner-gap text-5xl text-institucional animate-spin mb-4"></i>
        <p class="font-bold text-slate-500 tracking-wide animate-pulse">Preparando entorno visual...</p>
    </div>

    <div id="toast-zoom" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-slate-800/95 backdrop-blur-md text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none z-[5000] border border-slate-600">
        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
            <i class="ph-bold ph-magnifying-glass-minus text-blue-400 text-2xl"></i>
        </div>
        <div>
            <span class="font-bold text-sm block">Zoom máximo satelital</span>
            <span class="text-xs text-slate-300 font-medium">No es posible acercar más la imagen.</span>
        </div>
    </div>
</div>

@endsection

@stack('scripts')
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

<script>
    const rawReportes = @json($reportes);
    const categoriasUnicas = [...new Set(rawReportes.map(r => r.categoria))].filter(Boolean);
    
    function getCategoryIcon(catName) {
        const c = catName.toLowerCase();
        if (c.includes('bache') || c.includes('pavimento') || c.includes('calle')) return 'ph-car';
        if (c.includes('agua') || c.includes('fuga')) return 'ph-drop';
        if (c.includes('luz') || c.includes('alumbrado')) return 'ph-lightbulb';
        if (c.includes('drenaje') || c.includes('alcantarilla')) return 'ph-toilet';
        if (c.includes('basura') || c.includes('limpieza')) return 'ph-trash';
        return 'ph-warning-circle';
    }

    function generarGeoJSON(datos) {
        return {
            type: 'FeatureCollection',
            features: datos.map(r => ({
                type: 'Feature',
                id: r.id, 
                geometry: { type: 'Point', coordinates: [parseFloat(r.longitud), parseFloat(r.latitud)] },
                properties: {
                    reporte_id: r.id, 
                    folio: r.folio, 
                    estado: r.estado, 
                    categoria: r.categoria, 
                    descripcion: r.descripcion,
                    telefono_contacto: r.telefono_contacto || '', 
                    fecha: r.created_at ? new Date(r.created_at).toLocaleDateString() : 'N/A'
                }
            }))
        };
    }

    let mapa;
    let datosActuales = generarGeoJSON(rawReportes);
    let currentMapStyle = 'calles';
    let timeoutZoom = null;

    const colores = { 'Pendiente': '#f59e0b', 'En progreso': '#3b82f6', 'Resuelto': '#10b981' };

    document.addEventListener('DOMContentLoaded', function () {
        
        // Creación UI Categorías (Iconos Puros estilo Botón App)
        const contCat = document.getElementById('contenedor-categorias');
        categoriasUnicas.forEach(cat => {
            const iconClass = getCategoryIcon(cat);
            contCat.innerHTML += `
                <label class="cursor-pointer group flex flex-col items-center gap-1.5">
                    <input type="checkbox" class="filtro-cat peer hidden" value="${cat}" checked onchange="aplicarFiltros()">
                    <div class="w-full aspect-square rounded-2xl flex items-center justify-center transition-all bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 text-slate-400 peer-checked:bg-institucional dark:peer-checked:bg-blue-600 peer-checked:border-institucional dark:peer-checked:border-blue-600 peer-checked:text-white shadow-sm opacity-60 grayscale peer-checked:opacity-100 peer-checked:grayscale-0 hover:opacity-100 hover:grayscale-0 hover:scale-105">
                        <i class="ph-fill ${iconClass} text-3xl"></i>
                    </div>
                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 peer-checked:text-institucional dark:peer-checked:text-blue-400 text-center leading-tight w-full truncate px-1" title="${cat}">${cat}</span>
                </label>
            `;
        });

        mapa = new maplibregl.Map({
            container: 'full-map',
            center: [-102.8167, 21.2667], 
            zoom: 13,
            pitch: 0,
            antialias: true,
            style: {
                version: 8,
                sources: {
                    'base-calles': { type: 'raster', tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'], tileSize: 256 },
                    'base-oscuro': { type: 'raster', tiles: ['https://a.basemaps.cartocdn.com/rastertiles/dark_all/{z}/{x}/{y}@2x.png'], tileSize: 256 },
                    'base-satelite': { type: 'raster', tiles: ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'], tileSize: 256 },
                    'base-etiquetas': { type: 'raster', tiles: ['https://a.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}@2x.png'], tileSize: 256 },
                    'terrain-source': { type: 'raster-dem', tiles: ['https://s3.amazonaws.com/elevation-tiles-prod/terrarium/{z}/{x}/{y}.png'], encoding: 'terrarium', tileSize: 256, maxzoom: 14 },
                    
                    // REPORTE DE DATOS
                    'reportes-datos': { 
                        type: 'geojson', 
                        data: datosActuales 
                    }
                },
                layers: [
                    { id: 'layer-satelite', type: 'raster', source: 'base-satelite', layout: { visibility: 'none' } },
                    { id: 'layer-oscuro', type: 'raster', source: 'base-oscuro', layout: { visibility: 'none' } },
                    { id: 'layer-calles', type: 'raster', source: 'base-calles', layout: { visibility: 'visible' } },
                    { id: 'layer-etiquetas', type: 'raster', source: 'base-etiquetas', layout: { visibility: 'none' } }
                ]
            }
        });

        mapa.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'bottom-right');

        mapa.on('style.load', () => {
            
            mapa.addLayer({
                id: 'capa-calor', type: 'heatmap', source: 'reportes-datos', layout: { visibility: 'none' },
                paint: {
                    'heatmap-weight': 1, 'heatmap-intensity': ['interpolate', ['linear'], ['zoom'], 11, 1, 15, 3],
                    'heatmap-color': ['interpolate', ['linear'], ['heatmap-density'], 0, 'rgba(33,102,172,0)', 0.2, 'rgb(103,169,207)', 0.4, 'rgb(209,229,240)', 0.6, 'rgb(253,219,199)', 0.8, 'rgb(239,138,98)', 1, 'rgb(178,24,43)'],
                    'heatmap-radius': ['interpolate', ['linear'], ['zoom'], 11, 15, 15, 40], 'heatmap-opacity': 0.8
                }
            });

            // PUNTOS FLOTANTES 3D: Se remueve circle-pitch-alignment: 'map'
            // Ahora las esferas de WebGL siempre apuntarán hacia la cámara,
            // logrando el efecto holográfico / orbe flotante en 3D
            mapa.addLayer({
                id: 'capa-puntos', 
                type: 'circle', 
                source: 'reportes-datos', 
                layout: { visibility: 'visible' },
                paint: { 
                    'circle-color': ['match', ['get', 'estado'], 'Pendiente', colores['Pendiente'], 'En progreso', colores['En progreso'], 'Resuelto', colores['Resuelto'], '#94a3b8'], 
                    'circle-radius': 9, 
                    'circle-stroke-width': 3, 
                    'circle-stroke-color': '#ffffff'
                }
            });

            // GESTIÓN DEL CLIC AL PUNTO INDIVIDUAL
            mapa.on('click', 'capa-puntos', (e) => {
                const props = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();
                
                const bgBadge = props.estado === 'Pendiente' ? 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' : 
                               (props.estado === 'Resuelto' ? 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800' : 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800');

                let rawPhone = props.telefono_contacto;
                if (rawPhone === 'null' || rawPhone === null || rawPhone === undefined || String(rawPhone).trim() === '') {
                    rawPhone = null;
                }
                
                const contactoHtml = rawPhone ? `
                    <div class="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/50 p-4 rounded-xl flex justify-between items-center mt-3 mb-1">
                        <div>
                            <p class="text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider mb-1">Contacto Ciudadano</p>
                            <p class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                <i class="ph-fill ph-whatsapp-logo text-green-500 text-lg"></i>
                                <span>+52 ${rawPhone}</span>
                            </p>
                        </div>
                        <a href="https://wa.me/52${rawPhone}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white p-2.5 rounded-lg shadow-sm transition" title="Abrir WhatsApp Web">
                            <i class="ph-bold ph-chat-teardrop-text text-lg"></i>
                        </a>
                    </div>
                ` : ``; 

                // REDIRECCIÓN ASEGURADA USANDO EL FOLIO
                const reporteFolioAsegurado = props.folio;
                const urlKanban = "{{ route('admin.reportes.index') }}?highlight=" + reporteFolioAsegurado;

                const popupHTML = `
                    <div class="p-6 min-w-[280px] bg-white dark:bg-slate-800">
                        <div class="flex justify-between items-start mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-widest">Folio Asignado</span>
                                <span class="font-mono font-black text-institucional dark:text-blue-400 text-xl leading-none">${props.folio}</span>
                            </div>
                            <span class="text-[10px] uppercase font-black px-2.5 py-1 rounded-md border ${bgBadge}">${props.estado}</span>
                        </div>
                        <p class="text-sm font-black text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                            <i class="ph-fill ${getCategoryIcon(props.categoria)} text-accion text-lg"></i> ${props.categoria}
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mb-3 line-clamp-3 italic">"${props.descripcion}"</p>
                        
                        ${contactoHtml}
                        
                        <div class="flex justify-between items-center mt-3 bg-slate-50 dark:bg-slate-900 p-2 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-[10px] font-bold text-slate-400 px-2 flex items-center gap-1"><i class="ph-bold ph-calendar-blank"></i> ${props.fecha}</span>
                            
                            <!-- ENLACE NATIVO PARA SEGURIDAD DE RUTA -->
                            <a href="${urlKanban}" class="text-xs font-bold text-white bg-institucional hover:bg-blue-900 dark:bg-blue-600 dark:hover:bg-blue-500 px-4 py-2 rounded-lg transition shadow-sm border border-transparent cursor-pointer">
                                Abrir Detalle
                            </a>
                        </div>
                    </div>
                `;

                new maplibregl.Popup({ closeButton: true, closeOnClick: true, maxWidth: '340px' }).setLngLat(coords).setHTML(popupHTML).addTo(mapa);
            });

            mapa.on('mouseenter', 'capa-puntos', () => mapa.getCanvas().style.cursor = 'pointer');
            mapa.on('mouseleave', 'capa-puntos', () => mapa.getCanvas().style.cursor = '');

            mapa.on('zoom', () => { if (currentMapStyle === 'satelite' && mapa.getZoom() >= 16.9) mostrarAlertaZoom(); });

            mapa.on('pitch', () => {
                const currentPitch = Math.round(mapa.getPitch());
                document.getElementById('slider-pitch').value = currentPitch;
                document.getElementById('label-pitch').innerText = currentPitch + '°';
            });

            document.getElementById('slider-pitch').addEventListener('input', function(e) {
                const pitch = parseInt(e.target.value);
                document.getElementById('label-pitch').innerText = pitch + '°';
                mapa.setPitch(pitch);
            });

            document.getElementById('loading-map').classList.add('opacity-0', 'pointer-events-none');
            aplicarFiltros();
        });
    });

    // ==========================================
    // LÓGICA DE FILTRADOS
    // ==========================================
    function aplicarFiltros() {
        const estadosSeleccionados = Array.from(document.querySelectorAll('.filtro-estado:checked')).map(cb => cb.value);
        const categoriasSeleccionadas = Array.from(document.querySelectorAll('.filtro-cat:checked')).map(cb => cb.value);

        const reportesFiltrados = rawReportes.filter(r => {
            return estadosSeleccionados.includes(r.estado) && categoriasSeleccionadas.includes(r.categoria);
        });

        datosActuales = generarGeoJSON(reportesFiltrados);
        if(mapa.getSource('reportes-datos')) mapa.getSource('reportes-datos').setData(datosActuales);
        
        actualizarKPIs(reportesFiltrados);
        encuadrarMapa(datosActuales);
    }

    function resetearFiltros() {
        document.querySelectorAll('.filtro-estado').forEach(cb => { cb.checked = (cb.value !== 'Resuelto'); });
        document.querySelectorAll('.filtro-cat').forEach(cb => cb.checked = true);
        aplicarFiltros();
    }

    function actualizarKPIs(datosFiltrados) {
        document.getElementById('kpi-total').innerText = datosFiltrados.length;
        
        // Obtenemos las categorías activas para que los contadores de estado reflejen la realidad
        const categoriasSeleccionadas = Array.from(document.querySelectorAll('.filtro-cat:checked')).map(cb => cb.value);
        const baseParaEstados = rawReportes.filter(r => categoriasSeleccionadas.includes(r.categoria));

        // Actualizamos los números en la interfaz
        document.getElementById('count-pendientes').innerText = baseParaEstados.filter(r => r.estado === 'Pendiente').length;
        document.getElementById('count-proceso').innerText = baseParaEstados.filter(r => r.estado === 'En progreso').length;
        document.getElementById('count-resueltos').innerText = baseParaEstados.filter(r => r.estado === 'Resuelto').length;
    }

    function encuadrarMapa(geoJsonData) {
        if (!geoJsonData.features.length) return; 
        const bounds = new maplibregl.LngLatBounds();
        geoJsonData.features.forEach(feature => { bounds.extend(feature.geometry.coordinates); });
        mapa.fitBounds(bounds, { padding: {top: 80, bottom:80, left: 350, right: 80}, maxZoom: 15, duration: 1500 });
    }

    function cambiarModoVisual(modo) {
        const btns = [document.getElementById('btn-modo-puntos'), document.getElementById('btn-modo-calor')];
        btns.forEach(b => { b.classList.remove('bg-institucional', 'text-white', 'shadow-md'); b.classList.add('text-slate-500', 'dark:text-slate-300'); });
        const btnActivo = document.getElementById(`btn-modo-${modo}`);
        btnActivo.classList.remove('text-slate-500', 'dark:text-slate-300');
        btnActivo.classList.add('bg-institucional', 'text-white', 'shadow-md');

        if(modo === 'calor') {
            mapa.setLayoutProperty('capa-calor', 'visibility', 'visible');
            mapa.setLayoutProperty('capa-puntos', 'visibility', 'none');
        } else {
            mapa.setLayoutProperty('capa-calor', 'visibility', 'none');
            mapa.setLayoutProperty('capa-puntos', 'visibility', 'visible');
        }
    }

    function cambiarMapaBase(estilo) {
        currentMapStyle = estilo;
        mapa.setLayoutProperty('layer-calles', 'visibility', 'none');
        mapa.setLayoutProperty('layer-oscuro', 'visibility', 'none');
        mapa.setLayoutProperty('layer-satelite', 'visibility', 'none');
        mapa.setLayoutProperty('layer-etiquetas', 'visibility', 'none');

        mapa.setLayoutProperty(`layer-${estilo}`, 'visibility', 'visible');
        
        if(estilo === 'satelite') {
            mapa.setLayoutProperty('layer-etiquetas', 'visibility', 'visible');
            mapa.setMaxZoom(17);
        } else { mapa.setMaxZoom(20); }
        if(estilo === 'oscuro') cambiarModoVisual('calor');
    }

    function toggle3DModo(activar) {
        const sliderContenedor = document.getElementById('contenedor-slider-3d');
        if (activar) {
            sliderContenedor.classList.remove('hidden'); sliderContenedor.classList.add('flex');
            mapa.setTerrain({ source: 'terrain-source', exaggeration: 1.5 });
            mapa.setFog({ 'color': 'rgba(255, 255, 255, 0.8)', 'high-color': 'rgba(36, 144, 235, 0.2)', 'space-color': '#e2e8f0' });
            mapa.flyTo({ pitch: 60, duration: 1500 });
            document.getElementById('slider-pitch').value = 60; document.getElementById('label-pitch').innerText = '60°';
        } else {
            sliderContenedor.classList.add('hidden'); sliderContenedor.classList.remove('flex');
            mapa.setTerrain(null); mapa.setFog(null);
            mapa.flyTo({ pitch: 0, duration: 1500 });
        }
    }

    function mostrarAlertaZoom() {
        const toast = document.getElementById('toast-zoom');
        toast.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
        if (timeoutZoom) clearTimeout(timeoutZoom);
        timeoutZoom = setTimeout(() => { toast.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none'); }, 3500);
    }
</script>