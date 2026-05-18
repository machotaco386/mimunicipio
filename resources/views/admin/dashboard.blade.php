@extends('layouts.admin')

@section('title', 'Dashboard - MiMunicipio')
@section('header_title', 'Visión General Operativa')

@section('content')
<!-- Tarjetas de Métricas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Total Reportes</p>
            <h3 class="text-3xl font-bold text-institucional">{{ $metricas['total'] }}</h3>
        </div>
        <div class="w-14 h-14 bg-blue-50 text-institucional rounded-full flex items-center justify-center text-2xl">
            <i class="ph-fill ph-files"></i>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Pendientes</p>
            <h3 class="text-3xl font-bold text-amber-500">{{ $metricas['pendientes'] }}</h3>
        </div>
        <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center text-2xl">
            <i class="ph-fill ph-warning-circle"></i>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">En Progreso</p>
            <h3 class="text-3xl font-bold text-blue-500">{{ $metricas['en_progreso'] }}</h3>
        </div>
        <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl">
            <i class="ph-fill ph-gear"></i>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Resueltos</p>
            <h3 class="text-3xl font-bold text-accion">{{ $metricas['resueltos'] }}</h3>
        </div>
        <div class="w-14 h-14 bg-[#84cc1620] text-accion rounded-full flex items-center justify-center text-2xl">
            <i class="ph-fill ph-check-circle"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8 relative">
    <!-- Tabla de Reportes Recientes -->
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                <i class="ph-bold ph-list-bullets text-accion"></i> Últimos Ingresos
            </h2>
            <a href="{{ route('admin.reportes.index') }}" class="text-sm font-bold text-accion hover:text-[#65a30d] transition">Ver todos &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4 font-bold">Folio</th>
                        <th class="p-4 font-bold">Categoría</th>
                        <th class="p-4 font-bold">Fecha</th>
                        <th class="p-4 font-bold">Estado</th>
                        <th class="p-4 font-bold text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($reportesRecientes as $reporte)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="p-4 font-mono font-bold text-institucional">{{ $reporte->folio }}</td>
                        <td class="p-4 text-slate-700 font-medium flex items-center gap-2">
                            <i class="ph-fill ph-tag text-slate-400 group-hover:text-accion transition"></i> {{ $reporte->categoria }}
                        </td>
                        <td class="p-4 text-slate-500">{{ $reporte->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border
                                @if($reporte->estado == 'Pendiente') bg-amber-50 text-amber-600 border-amber-200
                                @elseif($reporte->estado == 'En progreso') bg-blue-50 text-blue-600 border-blue-200
                                @else bg-[#84cc1615] text-accion border-[#84cc1640] @endif">
                                {{ $reporte->estado }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <button type="button" onclick="abrirModalReporte(this)" data-reporte="{{ json_encode($reporte) }}" class="text-slate-400 hover:text-institucional transition bg-white border border-slate-200 shadow-sm p-1.5 rounded-lg hover:shadow-md">
                                <i class="ph-bold ph-eye text-lg"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <i class="ph-light ph-folder-open text-4xl mb-2"></i>
                            <p>No hay reportes registrados aún.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mapa en Vivo Mini (Convertible a Fullscreen) -->
    <div id="mapa-tarjeta" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full min-h-[450px] transition-all duration-300">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center z-20">
            <div>
                <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                    <i class="ph-bold ph-map-pin-line text-accion"></i> Mapa Activo
                </h2>
                <p class="text-xs text-slate-500 mt-1 hidden sm:block">Reportes Pendientes y En Progreso.</p>
            </div>
            
            <button onclick="toggleFullscreenMap()" class="text-slate-400 hover:text-institucional transition bg-white border border-slate-200 shadow-sm px-3 py-1.5 rounded-lg hover:shadow-md flex items-center gap-2 text-sm font-bold">
                <span id="map-fs-text" class="hidden sm:inline">Expandir</span> <i id="map-fs-icon" class="ph-bold ph-arrows-out text-lg"></i>
            </button>
        </div>
        
        <div class="relative flex-grow w-full h-full min-h-[400px]">
            
            <!-- CONTROLADOR DE CAPAS CUSTOM -->
            <div class="absolute top-4 right-4 z-[400]">
                <button id="btn-capas-dash" onclick="document.getElementById('menu-capas-dash').classList.toggle('hidden')" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm px-3 py-2 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-institucional transition flex items-center gap-2">
                    <i class="ph-bold ph-stack text-lg text-accion"></i> Capas
                </button>
                
                <div id="menu-capas-dash" class="hidden absolute top-full right-0 mt-2 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 p-2 w-52 flex flex-col gap-1 z-[400]">
                    <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer transition">
                        <input type="radio" name="capa_dash" value="calles" checked onchange="cambiarCapaDash('calles')" class="text-accion focus:ring-accion w-4 h-4">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200"><i class="ph-fill ph-map-trifold text-blue-500"></i> Calles (Por defecto)</span>
                    </label>
                    <hr class="border-slate-100 dark:border-slate-700 my-1">
                    <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer transition">
                        <input type="radio" name="capa_dash" value="limpio" onchange="cambiarCapaDash('limpio')" class="text-accion focus:ring-accion w-4 h-4">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200"><i class="ph-fill ph-magic-wand text-slate-400"></i> Mapa Limpio</span>
                    </label>
                    <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer transition">
                        <input type="radio" name="capa_dash" value="satelite" onchange="cambiarCapaDash('satelite')" class="text-accion focus:ring-accion w-4 h-4">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200"><i class="ph-fill ph-globe-hemisphere-west text-green-600"></i> Satélite Real</span>
                    </label>
                </div>
            </div>

            <!-- EL MAPA -->
            <div id="admin-map" class="w-full h-full absolute inset-0 z-10"></div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL DE DETALLE DE REPORTE                -->
<!-- ========================================== -->
<div id="modal-detalle-reporte" class="fixed inset-0 z-[2000] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="cerrarModalReporte()"></div>
    
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-institucional text-xl font-mono tracking-wide" id="modal-folio">MX-00000</h3>
                <span id="modal-badge-estado" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-slate-100">Estado</span>
            </div>
            <button onclick="cerrarModalReporte()" class="text-slate-400 hover:text-red-500 transition p-2 bg-white rounded-full border border-slate-200 shadow-sm">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Categoría</p>
                            <p class="font-bold text-institucional flex items-center gap-1.5" id="modal-categoria">
                                <i class="ph-fill ph-tag text-accion"></i> Cargando...
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha de Ingreso</p>
                            <p class="font-semibold text-slate-700" id="modal-fecha">Cargando...</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Descripción del Ciudadano</p>
                        <div class="bg-white border border-slate-200 p-4 rounded-xl text-sm text-slate-700 leading-relaxed shadow-inner" id="modal-descripcion">Cargando...</div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Información de Contacto</p>
                        <p class="font-medium text-slate-700 flex items-center gap-2">
                            <i class="ph-fill ph-whatsapp-logo text-green-500 text-lg"></i>
                            <span id="modal-telefono">+52 0000000000</span>
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ubicación Geoespacial</p>
                        
                        <div class="relative">
                            <div id="modal-map" class="w-full h-48 rounded-xl border-2 border-slate-200 z-10 overflow-hidden shadow-sm"></div>
                            
                            <!-- Menú de Capas del Modal -->
                            <div class="absolute top-2 right-2 z-[400]">
                                <button id="btn-capas-modal" onclick="document.getElementById('menu-capas-modal').classList.toggle('hidden')" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm px-2 py-1.5 rounded-lg shadow border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-institucional transition flex items-center gap-1">
                                    <i class="ph-bold ph-stack text-accion"></i> Capas
                                </button>
                                
                                <div id="menu-capas-modal" class="hidden absolute top-full right-0 mt-1 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 p-2 w-48 flex flex-col gap-1 transition-colors">
                                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer transition">
                                        <input type="radio" name="capa_modal" value="calles" checked onchange="cambiarCapaModal('calles')" class="text-accion focus:ring-accion w-3 h-3">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200"><i class="ph-fill ph-map-trifold text-blue-500"></i> Calles (Por defecto)</span>
                                    </label>
                                    <hr class="border-slate-100 dark:border-slate-700 my-0.5">
                                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer transition">
                                        <input type="radio" name="capa_modal" value="limpio" onchange="cambiarCapaModal('limpio')" class="text-accion focus:ring-accion w-3 h-3">
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200"><i class="ph-fill ph-magic-wand text-slate-400"></i> Mapa Limpio</span>
                                    </label>
                                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer transition">
                                        <input type="radio" name="capa_modal" value="satelite" onchange="cambiarCapaModal('satelite')" class="text-accion focus:ring-accion w-3 h-3">
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200"><i class="ph-fill ph-globe-hemisphere-west text-green-600"></i> Satélite Real</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400 mt-1 text-right font-mono" id="modal-coords">Lat: 0, Lng: 0</p>
                    </div>

                    <div id="modal-foto-container" class="hidden">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Evidencia Fotográfica</p>
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100 flex justify-center items-center h-48">
                            <img id="modal-foto" src="" alt="Evidencia" class="max-h-full max-w-full object-contain cursor-pointer" onclick="window.open(this.src, '_blank')">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 text-right">Clic en la imagen para ampliar</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-5 border-t border-slate-100 bg-slate-50 flex justify-end">
            <a href="{{ route('admin.reportes.index') }}" class="bg-institucional hover:bg-blue-900 text-white font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center gap-2 text-sm shadow-md">
                Ir a gestionar <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection

@stack('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ==========================================
    // LÓGICA DEL MAPA PRINCIPAL DEL DASHBOARD
    // ==========================================
    let dashMap;
    let capaDashCalles, capaDashLimpia, capaDashSatelite;

    document.addEventListener('DOMContentLoaded', function () {
        dashMap = L.map('admin-map', { zoomControl: false }).setView([21.2667, -102.8167], 13);
        L.control.zoom({ position: 'bottomright' }).addTo(dashMap);

        // DEFINIMOS LAS CAPAS (Siempre claras o satélite, sin modo oscuro automático)
        capaDashCalles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OSM' });
        capaDashLimpia = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19, attribution: '&copy; CartoDB' });
        capaDashSatelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: '&copy; Esri' });

        // Cargamos OSM (Calles) por defecto
        capaDashCalles.addTo(dashMap);

        // Pines
        var pines = @json($pinesMapa);
        var customIcon = L.divIcon({
            className: 'custom-pin',
            html: `<div style="color: #1A365D; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 256 256"><path fill="currentColor" d="M128,16a88.1,88.1,0,0,0-88,88c0,75.3,80,132.17,83.41,134.55a8,8,0,0,0,9.18,0C136,236.17,216,179.3,216,104A88.1,88.1,0,0,0,128,16Zm0,56a32,32,0,1,1-32,32A32,32,0,0,1,128,72Z"></path></svg></div>`,
            iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32]
        });

        pines.forEach(function(pin) {
            var badgeColor = pin.estado === 'Pendiente' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-blue-100 text-blue-700 border-blue-200';
            L.marker([pin.latitud, pin.longitud], {icon: customIcon}).addTo(dashMap).bindPopup(`
                <div class="text-center p-2 min-w-[120px]">
                    <p class="font-mono font-bold text-institucional text-sm mb-1">${pin.folio}</p>
                    <p class="text-xs font-medium text-slate-600 mb-2">${pin.categoria}</p>
                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md border ${badgeColor}">${pin.estado}</span>
                </div>
            `);
        });

        setTimeout(function(){ dashMap.invalidateSize(); }, 400);

        // Cerrar menús al hacer clic fuera
        document.addEventListener('click', function(e) {
            ['dash', 'modal'].forEach(tipo => {
                const btn = document.getElementById('btn-capas-' + tipo);
                const menu = document.getElementById('menu-capas-' + tipo);
                if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        });
    });

    function cambiarCapaDash(tipo) {
        dashMap.removeLayer(capaDashCalles);
        dashMap.removeLayer(capaDashLimpia);
        dashMap.removeLayer(capaDashSatelite);

        if (tipo === 'limpio') capaDashLimpia.addTo(dashMap);
        else if (tipo === 'satelite') capaDashSatelite.addTo(dashMap);
        else capaDashCalles.addTo(dashMap);

        document.getElementById('menu-capas-dash').classList.add('hidden');
    }

    function toggleFullscreenMap() {
        const tarjeta = document.getElementById('mapa-tarjeta');
        const icon = document.getElementById('map-fs-icon');
        const texto = document.getElementById('map-fs-text');
        
        if (tarjeta.classList.contains('fixed')) {
            tarjeta.classList.remove('fixed', 'inset-4', 'sm:inset-8', 'z-[1000]', 'shadow-2xl');
            tarjeta.classList.add('relative', 'h-full', 'min-h-[450px]');
            icon.classList.replace('ph-arrows-in', 'ph-arrows-out');
            texto.innerText = 'Expandir';
        } else {
            tarjeta.classList.remove('relative', 'h-full', 'min-h-[450px]');
            tarjeta.classList.add('fixed', 'inset-4', 'sm:inset-8', 'z-[1000]', 'shadow-2xl');
            icon.classList.replace('ph-arrows-out', 'ph-arrows-in');
            texto.innerText = 'Reducir';
        }
        setTimeout(() => { dashMap.invalidateSize(); }, 300);
    }

    // ==========================================
    // LÓGICA DEL MODAL DE DETALLES
    // ==========================================
    let modalMapInstance = null;
    let modalMarkerInstance = null;
    let modalCapaCalles, modalCapaLimpia, modalCapaSatelite;

    function abrirModalReporte(boton) {
        const reporte = JSON.parse(boton.getAttribute('data-reporte'));
        
        document.getElementById('modal-folio').innerText = reporte.folio;
        document.getElementById('modal-categoria').innerHTML = `<i class="ph-fill ph-tag text-accion"></i> ${reporte.categoria}`;
        
        const fecha = new Date(reporte.created_at);
        document.getElementById('modal-fecha').innerText = fecha.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' });
        
        document.getElementById('modal-descripcion').innerText = reporte.descripcion;
        document.getElementById('modal-telefono').innerText = '+52 ' + reporte.telefono_contacto;
        document.getElementById('modal-coords').innerText = `Lat: ${reporte.latitud}, Lng: ${reporte.longitud}`;

        const badge = document.getElementById('modal-badge-estado');
        badge.innerText = reporte.estado;
        badge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border ';
        if (reporte.estado === 'Pendiente') badge.classList.add('bg-amber-100', 'text-amber-700', 'border-amber-200');
        else if (reporte.estado === 'En progreso') badge.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
        else badge.classList.add('bg-[#84cc1620]', 'text-accion', 'border-[#84cc1640]');

        const fotoContainer = document.getElementById('modal-foto-container');
        if (reporte.ruta_foto) {
            fotoContainer.classList.remove('hidden');
            document.getElementById('modal-foto').src = '/storage/' + reporte.ruta_foto;
        } else {
            fotoContainer.classList.add('hidden');
            document.getElementById('modal-foto').src = '';
        }

        document.getElementById('modal-detalle-reporte').classList.remove('hidden');

        setTimeout(() => {
            if (!modalMapInstance) {
                modalMapInstance = L.map('modal-map', { zoomControl: false }).setView([reporte.latitud, reporte.longitud], 16);
                L.control.zoom({ position: 'bottomleft' }).addTo(modalMapInstance);
                
                modalCapaCalles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OSM' });
                modalCapaLimpia = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19, attribution: '&copy; CartoDB' });
                modalCapaSatelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: '&copy; Esri' });

                modalCapaCalles.addTo(modalMapInstance);
                
                modalMarkerInstance = L.marker([reporte.latitud, reporte.longitud]).addTo(modalMapInstance);
            } else {
                modalMapInstance.setView([reporte.latitud, reporte.longitud], 16);
                modalMarkerInstance.setLatLng([reporte.latitud, reporte.longitud]);
            }
            modalMapInstance.invalidateSize();
        }, 150);
    }

    function cambiarCapaModal(tipo) {
        modalMapInstance.removeLayer(modalCapaCalles);
        modalMapInstance.removeLayer(modalCapaLimpia);
        modalMapInstance.removeLayer(modalCapaSatelite);

        if (tipo === 'limpio') modalCapaLimpia.addTo(modalMapInstance);
        else if (tipo === 'satelite') modalCapaSatelite.addTo(modalMapInstance);
        else modalCapaCalles.addTo(modalMapInstance);

        document.getElementById('menu-capas-modal').classList.add('hidden');
    }

    function cerrarModalReporte() {
        document.getElementById('modal-detalle-reporte').classList.add('hidden');
    }
</script>