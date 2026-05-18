@extends('layouts.admin')

@section('title', 'Gestión de Reportes - MiMunicipio')
@section('header_title', 'Centro de Gestión Operativa')

@section('content')

@php
    $todosLosReportes = collect()->concat($pendientes)->concat($enProgreso)->concat($resueltos);
    $contadorOriginal = 0; 
@endphp

<!-- INYECCIÓN JSON PARA EL MODAL DE CUADRILLAS -->
<script>
    const globalCuadrillas = @json($cuadrillas);
</script>

<!-- Estilos para Drag & Drop y Animación de Resaltado (Aureola) -->
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Animaciones de Arrastre */
    .dragging { opacity: 0.6; transform: scale(0.95) rotate(2deg); z-index: 50; }
    .drag-over { background-color: rgba(132, 204, 22, 0.05) !important; border-color: #84CC16 !important; border-style: dashed !important; border-width: 2px !important; }
    .dark .drag-over { background-color: rgba(132, 204, 22, 0.1) !important; }

    /* Efecto Aureola Fosforescente Infinita (Enterprise UX) */
    .highlight-card { 
        animation: pulse-glow-infinite 2s infinite !important; 
        border-color: #84CC16 !important; 
        z-index: 40; 
    }
    @keyframes pulse-glow-infinite {
        0% { box-shadow: 0 0 0 2px rgba(132,204,22,0.8), 0 0 15px rgba(132,204,22,0.4); transform: scale(1.01); }
        50% { box-shadow: 0 0 0 4px rgba(132,204,22,1), 0 0 30px rgba(132,204,22,0.9); transform: scale(1.03); }
        100% { box-shadow: 0 0 0 2px rgba(132,204,22,0.8), 0 0 15px rgba(132,204,22,0.4); transform: scale(1.01); }
    }
</style>

@if (session('success'))
    <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-xl mb-6 flex items-center gap-3 border border-green-200 dark:border-green-800 shadow-sm">
        <i class="ph-fill ph-check-circle text-xl"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif

<div class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6 flex flex-col lg:flex-row justify-between items-center gap-4">
    <div class="flex items-center bg-slate-100 dark:bg-slate-900 p-1 rounded-lg border border-slate-200 dark:border-slate-700 w-full lg:w-auto overflow-x-auto">
        <button onclick="cambiarVista('kanban')" id="btn-kanban" class="px-4 py-2 rounded-md text-sm font-bold flex items-center gap-2 bg-white dark:bg-slate-700 text-institucional dark:text-blue-400 shadow-sm transition-all whitespace-nowrap">
            <i class="ph-bold ph-kanban"></i> Tablero Interactivo
        </button>
        <button onclick="cambiarVista('lista')" id="btn-lista" class="px-4 py-2 rounded-md text-sm font-bold flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-institucional transition-all whitespace-nowrap">
            <i class="ph-bold ph-list-dashes"></i> Lista Compacta
        </button>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto flex-grow justify-end">
        <div class="relative w-full sm:w-64 lg:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ph-bold ph-magnifying-glass text-slate-400"></i>
            </div>
            <input type="text" id="input-buscador" onkeyup="filtrarReportes()" placeholder="Buscar folio, descripción..." 
                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-accion outline-none transition text-slate-700 dark:text-slate-200">
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- VISTA KANBAN (CON DRAG & DROP HTML5)       -->
<!-- ========================================== -->
<div id="vista-kanban" class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start transition-opacity duration-300">
    
    <!-- COLUMNA 1: RECIBIDOS -->
    <div class="kanban-column bg-slate-100 dark:bg-[#151e2e] rounded-2xl p-3 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col h-[calc(100vh-220px)] min-h-[500px] transition-all"
         ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="dropReporte(event, 'Pendiente')">
        <div class="flex items-center justify-between px-2 mb-3 pointer-events-none">
            <h2 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm"></span> Recibidos</h2>
            <span class="bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black px-2 py-0.5 rounded-md" id="count-pendientes">{{ $pendientes->count() }}</span>
        </div>
        <div class="col-content flex-grow overflow-y-auto scrollbar-hide px-1 pb-4" id="col-pendientes">
            @forelse($pendientes as $reporte)
                @include('admin.reportes._tarjeta_inmersiva', ['reporte' => $reporte, 'color' => 'amber', 'index' => ++$contadorOriginal, 'cuadrillas' => $cuadrillas])
            @empty
                <p class="text-xs font-bold text-slate-400 dark:text-slate-600 text-center py-8 italic pointer-events-none empty-msg">Arrastra folios aquí</p>
            @endforelse
        </div>
    </div>

    <!-- COLUMNA 2: EN PROCESO -->
    <div class="kanban-column bg-slate-100 dark:bg-[#151e2e] rounded-2xl p-3 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col h-[calc(100vh-220px)] min-h-[500px] transition-all"
         ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="dropReporte(event, 'En progreso')">
        <div class="flex items-center justify-between px-2 mb-3 pointer-events-none">
            <h2 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm"></span> En Proceso</h2>
            <span class="bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black px-2 py-0.5 rounded-md" id="count-en-progreso">{{ $enProgreso->count() }}</span>
        </div>
        <div class="col-content flex-grow overflow-y-auto scrollbar-hide px-1 pb-4" id="col-en-progreso">
            @forelse($enProgreso as $reporte)
                @include('admin.reportes._tarjeta_inmersiva', ['reporte' => $reporte, 'color' => 'blue', 'index' => ++$contadorOriginal, 'cuadrillas' => $cuadrillas])
            @empty
                <p class="text-xs font-bold text-slate-400 dark:text-slate-600 text-center py-8 italic pointer-events-none empty-msg">Suelta un reporte para trabajarlo</p>
            @endforelse
        </div>
    </div>

    <!-- COLUMNA 3: RESUELTOS -->
    <div class="kanban-column bg-slate-100 dark:bg-[#151e2e] rounded-2xl p-3 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col h-[calc(100vh-220px)] min-h-[500px] transition-all"
         ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="dropReporte(event, 'Resuelto')">
        <div class="flex items-center justify-between px-2 mb-3 pointer-events-none">
            <h2 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-accion shadow-sm"></span> Resueltos</h2>
            <span class="bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black px-2 py-0.5 rounded-md" id="count-resueltos">{{ $resueltos->count() }}</span>
        </div>
        <div class="col-content flex-grow overflow-y-auto scrollbar-hide px-1 pb-4" id="col-resueltos">
            @forelse($resueltos as $reporte)
                @include('admin.reportes._tarjeta_inmersiva', ['reporte' => $reporte, 'color' => 'green', 'index' => ++$contadorOriginal, 'cuadrillas' => $cuadrillas])
            @empty
                <p class="text-xs font-bold text-slate-400 dark:text-slate-600 text-center py-8 italic pointer-events-none empty-msg">Arrastra folios solucionados aquí</p>
            @endforelse
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: ASIGNACIÓN VISUAL DE CUADRILLAS     -->
<!-- ========================================== -->
<div id="modal-asignar-cuadrilla" class="fixed inset-0 z-[800] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModalAsignacion()"></div>
    
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h3 class="text-xl font-black text-institucional dark:text-blue-400 flex items-center gap-2"><i class="ph-bold ph-truck text-accion"></i> Despacho Operativo</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Asignando brigada al Folio: <span id="modal-asig-folio" class="font-mono font-bold text-slate-700 dark:text-slate-200">MX-00</span></p>
            </div>
            <button onclick="cerrarModalAsignacion()" class="text-slate-400 hover:text-red-500 transition p-2 bg-white dark:bg-slate-800 rounded-full border border-slate-200 dark:border-slate-700 shadow-sm"><i class="ph-bold ph-x text-lg"></i></button>
        </div>

        <form id="form-modal-cuadrilla" method="POST" class="flex flex-col flex-grow overflow-hidden">
            @csrf @method('PATCH')
            <input type="hidden" name="estado" id="modal-hidden-estado">
            
            <div class="p-6 overflow-y-auto bg-slate-50/30 dark:bg-slate-900/20">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Selecciona una Unidad Disponible</p>
                <div id="lista-cuadrillas-modal" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Dinámico -->
                </div>
            </div>
            
            <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalAsignacion()" class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition">Cancelar</button>
                <button type="submit" class="px-8 py-2.5 rounded-xl bg-institucional hover:bg-blue-900 text-white font-bold transition shadow-lg shadow-institucional/20 flex items-center gap-2">
                    <i class="ph-bold ph-paper-plane-tilt"></i> Confirmar Despacho
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL DE DETALLE INMERSIVO AL MÁXIMO       -->
<!-- ========================================== -->
<div id="modal-detalle-reporte" class="fixed inset-0 z-[600] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModalReporte()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Cabecera -->
        <div class="flex justify-between items-center p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-institucional dark:text-blue-400 text-xl font-mono tracking-wide" id="modal-folio">MX-00000</h3>
                <span id="modal-badge-estado" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border bg-white dark:bg-slate-800">Estado</span>
            </div>
            <button onclick="cerrarModalReporte()" class="text-slate-400 hover:text-red-500 transition p-2 bg-white dark:bg-slate-800 rounded-full border border-slate-200 dark:border-slate-700 shadow-sm"><i class="ph-bold ph-x text-lg"></i></button>
        </div>
        
        <!-- Contenido Súper Detallado -->
        <div class="p-6 overflow-y-auto">
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 shadow-inner mb-6">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Municipio Creador</p>
                    <p class="font-bold text-slate-700 dark:text-slate-300 text-sm truncate" id="modal-municipio">...</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Departamento</p>
                    <p class="font-bold text-institucional dark:text-blue-400 text-sm truncate" id="modal-departamento">...</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Brigada / Unidad</p>
                    <p class="font-bold text-slate-700 dark:text-slate-300 text-sm truncate" id="modal-cuadrilla">...</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Última Actualización</p>
                    <p class="font-semibold text-slate-600 dark:text-slate-400 text-sm truncate" id="modal-actualizacion">...</p>
                </div>
            </div>

            <!-- Panel de Contenido Central -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-xl border border-blue-100 dark:border-blue-800 flex-grow">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Categoría del Reporte</p>
                            <p class="font-bold text-institucional dark:text-blue-400 text-sm flex items-center gap-1.5" id="modal-categoria"><i class="ph-fill ph-tag text-accion"></i> Cargando...</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700 flex-grow">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha de Ingreso</p>
                            <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm" id="modal-fecha">Cargando...</p>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Descripción del Ciudadano</p>
                        <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 p-4 rounded-xl text-sm text-slate-700 dark:text-slate-300 leading-relaxed shadow-inner" id="modal-descripcion"></div>
                    </div>
                    
                    <!-- Foto -->
                    <div id="modal-foto-container" class="hidden">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Evidencia Fotográfica</p>
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100 flex justify-center items-center h-48 relative group">
                            <img id="modal-foto" src="" alt="Evidencia" class="max-h-full max-w-full object-contain cursor-pointer transition group-hover:scale-105" onclick="window.open(this.src, '_blank')">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity">
                                <span class="text-white font-bold text-sm flex items-center gap-2"><i class="ph-bold ph-arrows-out"></i> Ampliar</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 flex items-center justify-between">Ubicación Geoespacial</p>
                        <div id="modal-map" class="w-full h-56 rounded-xl border-2 border-slate-200 dark:border-slate-600 z-10 overflow-hidden shadow-sm"></div>
                        
                        <div class="bg-green-50/50 dark:bg-green-900/10 border border-green-100 dark:border-green-800/50 p-4 rounded-xl mt-4">
                            <p class="font-bold text-slate-700 dark:text-slate-300 flex items-start gap-2 text-sm">
                                <i class="ph-fill ph-map-pin text-blue-500 text-lg mt-0.5"></i> 
                                <span id="modal-direccion" class="leading-tight">Cargando ubicación del servidor cartográfico...</span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Contenedor Dinámico para Contacto Telefónico (Se inyecta JS aquí si hay teléfono) -->
                    <div id="modal-telefono-container"></div>
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end">
            <button onclick="cerrarModalReporte()" class="bg-institucional hover:bg-blue-900 text-white font-bold py-2.5 px-8 rounded-xl shadow-md transition-colors">Cerrar Detalle</button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- VISTA LISTA COMPACTA                       -->
<!-- ========================================== -->
<div id="vista-lista" class="hidden bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-opacity duration-300">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-400 dark:text-slate-500 text-[10px] uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <th class="p-4 font-bold">Registro / Área</th>
                    <th class="p-4 font-bold w-1/3">Descripción Ciudadana</th>
                    <th class="p-4 font-bold text-center">Tiempo (SLA)</th>
                    <th class="p-4 font-bold">Cuadrilla / Unidad</th>
                    <th class="p-4 font-bold">Gestión de Estatus</th>
                    <th class="p-4 font-bold text-center">Detalles</th>
                </tr>
            </thead>
            <tbody id="lista-body" class="text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
                @php $contadorOriginalLista = 0; @endphp
                @forelse($todosLosReportes as $reporte)
                @php
                    $diasActivo = $reporte->created_at->diffInDays(now());
                    $urgenciaClass = 'bg-slate-100 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300';
                    $urgenciaIcon = 'ph-clock';
                    $urgenciaText = $reporte->created_at->diffForHumans();

                    if ($reporte->estado !== 'Resuelto') {
                        if ($diasActivo >= 5) {
                            $urgenciaClass = 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-400 font-bold shadow-sm';
                            $urgenciaIcon = 'ph-warning-octagon text-red-600 dark:text-red-400 animate-pulse';
                            $urgenciaText = $diasActivo . ' días (Crítico)';
                        } elseif ($diasActivo >= 3) {
                            $urgenciaClass = 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800/50 text-orange-700 dark:text-orange-400 font-bold';
                            $urgenciaIcon = 'ph-warning text-orange-500 dark:text-orange-400';
                            $urgenciaText = $diasActivo . ' días (Urgente)';
                        }
                    }
                    $areaColor = $reporte->area ? $reporte->area->color : '#64748b'; 
                @endphp
                <tr id="reporte-row-{{ $reporte->id }}" class="reporte-lista-item hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all duration-500 group"
                    data-id="{{ $reporte->id }}" data-index="{{ ++$contadorOriginalLista }}" data-timestamp="{{ $reporte->created_at->timestamp }}"
                    data-categoria="{{ strtolower($reporte->categoria) }}" data-folio="{{ strtolower($reporte->folio) }}"
                    data-descripcion="{{ strtolower($reporte->descripcion) }}" data-fecha="{{ $reporte->created_at->format('d/m/Y') }}"
                    data-estado="{{ $reporte->estado }}">
                    <td class="p-4">
                        <div class="font-mono font-bold text-institucional dark:text-blue-300 text-sm">{{ $reporte->folio }}</div>
                        <span class="inline-block text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-widest border mt-1" style="background-color: {{ $areaColor }}15; color: {{ $areaColor }}; border-color: {{ $areaColor }}40;">
                            {{ $reporte->area ? $reporte->area->nombre : $reporte->categoria }}
                        </span>
                    </td>
                    <td class="p-4"><p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed" title="{{ $reporte->descripcion }}">{{ $reporte->descripcion }}</p></td>
                    <td class="p-4 text-center">
                        <div class="inline-flex items-center justify-center gap-1.5 border px-2.5 py-1.5 rounded-md text-[10px] {{ $urgenciaClass }}" title="Ingresado: {{ $reporte->created_at }}">
                            <i class="ph-fill {{ $urgenciaIcon }} text-sm"></i> <span>{{ $urgenciaText }}</span>
                        </div>
                    </td>
                    <td class="p-4">
                        <button type="button" onclick="abrirModalAsignacion({{ $reporte->id }}, '{{ $reporte->folio }}', {{ $reporte->area_id ?? 'null' }}, {{ $reporte->cuadrilla_id ?? 'null' }}, '{{ $reporte->estado }}')" 
                                class="w-full flex items-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 hover:border-institucional dark:hover:border-blue-500 transition px-2 py-1.5 rounded-lg text-left shadow-sm">
                            <div class="flex items-center gap-2 overflow-hidden w-full">
                                <div class="w-6 h-6 rounded flex-shrink-0 flex items-center justify-center {{ $reporte->cuadrilla ? 'bg-institucional text-white dark:bg-blue-600' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }}">
                                    <i class="ph-fill {{ $reporte->cuadrilla ? ($reporte->cuadrilla->icono ?? 'ph-truck') : 'ph-users' }} text-[11px]"></i>
                                </div>
                                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 truncate w-full">{{ $reporte->cuadrilla ? $reporte->cuadrilla->nombre : 'Sin asignar' }}</p>
                            </div>
                        </button>
                    </td>
                    <td class="p-4">
                        <form action="{{ route('admin.reportes.estado', $reporte) }}" method="POST" class="m-0 relative">
                            @csrf @method('PATCH')
                            <input type="hidden" name="cuadrilla_id" value="{{ $reporte->cuadrilla_id }}">
                            <select name="estado" class="w-full text-[11px] font-bold rounded-lg px-2 py-2 border outline-none cursor-pointer focus:ring-2 transition appearance-none shadow-sm
                                {{ $reporte->estado == 'Pendiente' ? 'bg-amber-50 dark:bg-amber-900/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/50' : '' }}
                                {{ $reporte->estado == 'En progreso' ? 'bg-blue-50 dark:bg-blue-900/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/50' : '' }}
                                {{ $reporte->estado == 'Resuelto' ? 'bg-[#84cc1615] dark:bg-[#84cc1610] text-accion border-[#84cc1640] dark:border-[#84cc1630]' : '' }}"
                                onchange="guardarEstadoModificado({{ $reporte->id }}, this.form)">
                                <option value="Pendiente" {{ $reporte->estado == 'Pendiente' ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white">Pendiente</option>
                                <option value="En progreso" {{ $reporte->estado == 'En progreso' ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white">En Proceso</option>
                                <option value="Resuelto" {{ $reporte->estado == 'Resuelto' ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white">Resuelto</option>
                            </select>
                        </form>
                    </td>
                    <td class="p-4 text-center">
                        <button type="button" onclick="abrirModalReporte(this)" data-reporte="{{ json_encode($reporte->load('municipio', 'area', 'cuadrilla')) }}" class="text-slate-400 hover:text-institucional dark:hover:text-blue-400 transition bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 shadow-sm p-2 rounded-lg hover:shadow-md" title="Detalles">
                            <i class="ph-bold ph-eye text-lg"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-12 text-center text-slate-400 dark:text-slate-500 ignore-sort"><i class="ph-light ph-folder-open text-4xl mb-2"></i><p>No hay reportes en la base de datos.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@stack('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ========================================================
    // LÓGICA DE RESALTADO INTELIGENTE INSTANTÁNEO
    // ========================================================
    function guardarEstadoModificado(reporteId, form) {
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const vistaPreferida = localStorage.getItem('preferenciaVistaReportes') || 'kanban';
        cambiarVista(vistaPreferida);

        // LEER URL PARAMETER (Es 100% confiable entre páginas)
        const urlParams = new URLSearchParams(window.location.search);
        const highlightId = urlParams.get('highlight');

        if (highlightId) {
            console.log("Activando modo redirección ultra-rápida para el reporte Folio: " + highlightId);
            
            let intentos = 0;
            const folioBuscado = highlightId.toLowerCase();

            // Buscador asíncrono ultra-rápido (30ms en lugar de 100ms)
            const intervaloBuscador = setInterval(() => {
                const tarjeta = document.querySelector(`.reporte-item[data-folio="${folioBuscado}"]`);
                const fila = document.querySelector(`.reporte-lista-item[data-folio="${folioBuscado}"]`);
                const elementoA_Resaltar = tarjeta || fila;

                if (elementoA_Resaltar) {
                    clearInterval(intervaloBuscador); // Detener sabueso inmediatamente
                    
                    if (!tarjeta) cambiarVista('lista');
                    else cambiarVista('kanban');

                    // 1. Encender Aureola (Que durará hasta que se cierre el modal)
                    elementoA_Resaltar.classList.add('highlight-card');
                    
                    // 2. Salto Cuántico (Instantáneo, sin animación de scroll lento)
                    elementoA_Resaltar.scrollIntoView({ behavior: 'auto', block: 'center' });
                    
                    // 3. Auto-Click Inmediato (0 milisegundos de espera)
                    const btnDetalle = elementoA_Resaltar.querySelector('button[onclick^="abrirModalReporte"]');
                    if (btnDetalle) {
                        btnDetalle.click();
                        // 4. Limpiar la URL para que no interfiera en recargas
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                    // NOTA: Se ha removido el listener que apagaba la luz al darle click, 
                    // la luz ahora solo se apaga cuando se cierra el modal explícitamente.
                }
                
                intentos++;
                if(intentos >= 60) clearInterval(intervaloBuscador); // Límite de ~2 segundos de búsqueda
            }, 30); 
        }
    });

    // ========================================================
    // MOTOR DE DRAG & DROP HTML5 PARA EL KANBAN
    // ========================================================

    function dragReporte(ev) {
        ev.dataTransfer.setData("reporte_id", ev.currentTarget.dataset.id);
        ev.dataTransfer.setData("estado_actual", ev.currentTarget.dataset.estado);
        setTimeout(() => ev.target.classList.add('dragging'), 10);
    }

    function dragEndReporte(ev) {
        ev.target.classList.remove('dragging');
    }

    function allowDrop(ev) {
        ev.preventDefault();
        const col = ev.currentTarget.closest('.kanban-column');
        col.classList.add('drag-over');
    }

    function dragLeave(ev) {
        const col = ev.currentTarget.closest('.kanban-column');
        col.classList.remove('drag-over');
    }

    function dropReporte(ev, nuevoEstado) {
        ev.preventDefault();
        const col = ev.currentTarget.closest('.kanban-column');
        col.classList.remove('drag-over');

        let reporteId = ev.dataTransfer.getData("reporte_id");
        let estadoActual = ev.dataTransfer.getData("estado_actual");

        if (estadoActual === nuevoEstado) return;

        let form = document.getElementById('form-estado-' + reporteId);
        if(form) {
            form.querySelector('.input-estado-secreto').value = nuevoEstado;
            form.submit(); 
        }
    }

    // ========================================================
    // LÓGICA DEL MODAL VISUAL DE CUADRILLAS
    // ========================================================
    function abrirModalAsignacion(reporteId, folio, areaId, cuadrillaActual, estadoActual) {
        document.getElementById('modal-asig-folio').innerText = folio;
        
        const formAsig = document.getElementById('form-modal-cuadrilla');
        formAsig.action = `/admin/reportes/${reporteId}/estado`;
        
        document.getElementById('modal-hidden-estado').value = estadoActual;

        const container = document.getElementById('lista-cuadrillas-modal');
        container.innerHTML = '';

        let cuadrillasArea = [];
        if (areaId) {
            cuadrillasArea = globalCuadrillas.filter(c => c.area_id == areaId);
        }
        if (cuadrillasArea.length === 0) {
            cuadrillasArea = globalCuadrillas;
        }

        container.innerHTML += `
            <label class="cursor-pointer relative block group">
                <input type="radio" name="cuadrilla_id" value="" class="peer sr-only" ${!cuadrillaActual ? 'checked' : ''}>
                <div class="border-2 rounded-2xl p-4 flex items-center gap-4 transition-all shadow-sm
                            border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600
                            peer-checked:border-red-500 peer-checked:ring-2 peer-checked:ring-red-500/20 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors 
                                bg-slate-100 dark:bg-slate-700 text-slate-400 border border-slate-200 dark:border-slate-600
                                peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-500">
                        <i class="ph-bold ph-prohibit text-2xl"></i>
                    </div>
                    <div>
                        <p class="font-black text-slate-700 dark:text-slate-200 leading-none mb-1">Sin Asignar</p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Retirar Brigada</p>
                    </div>
                </div>
            </label>
        `;

        if(cuadrillasArea.length > 0) {
            cuadrillasArea.forEach(c => {
                const numTrabajadores = c.trabajadores ? c.trabajadores.length : 0;
                
                container.innerHTML += `
                    <label class="cursor-pointer relative block group">
                        <input type="radio" name="cuadrilla_id" value="${c.id}" class="peer sr-only" ${c.id == cuadrillaActual ? 'checked' : ''}>
                        <div class="border-2 rounded-2xl p-4 flex items-center gap-4 transition-all shadow-sm
                                    border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-institucional/50 dark:hover:border-blue-500/50
                                    peer-checked:border-institucional peer-checked:ring-2 peer-checked:ring-institucional/20 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors 
                                        bg-slate-50 dark:bg-slate-700 text-institucional dark:text-blue-400 border border-slate-200 dark:border-slate-600
                                        peer-checked:bg-institucional peer-checked:text-white dark:peer-checked:bg-blue-600">
                                <i class="ph-fill ${c.icono || 'ph-truck'} text-2xl"></i>
                            </div>
                            <div>
                                <p class="font-black text-slate-700 dark:text-slate-200 leading-none mb-1">${c.nombre}</p>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1">
                                    <i class="ph-bold ph-users"></i> ${numTrabajadores} Elementos
                                </p>
                            </div>
                        </div>
                    </label>
                `;
            });
        }
        document.getElementById('modal-asignar-cuadrilla').classList.remove('hidden');
    }

    function cerrarModalAsignacion() {
        document.getElementById('modal-asignar-cuadrilla').classList.add('hidden');
    }

    // ========================================================
    // MODAL DE DETALLES Y CONTACTO DINÁMICO
    // ========================================================
    let modalMapInstance = null;
    let modalMarkerInstance = null;

    function getCategoryIcon(catName) {
        const c = catName.toLowerCase();
        if (c.includes('bache') || c.includes('pavimento') || c.includes('calle')) return 'ph-car';
        if (c.includes('agua') || c.includes('fuga')) return 'ph-drop';
        if (c.includes('luz') || c.includes('alumbrado')) return 'ph-lightbulb';
        if (c.includes('drenaje') || c.includes('alcantarilla')) return 'ph-toilet';
        if (c.includes('basura') || c.includes('limpieza')) return 'ph-trash';
        return 'ph-warning-circle';
    }

    function abrirModalReporte(boton) {
        const reporte = JSON.parse(boton.getAttribute('data-reporte'));
        
        document.getElementById('modal-folio').innerText = reporte.folio;
        const badge = document.getElementById('modal-badge-estado');
        badge.innerText = reporte.estado;
        badge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border ';
        if (reporte.estado === 'Pendiente') badge.classList.add('bg-amber-100', 'text-amber-700', 'border-amber-200');
        else if (reporte.estado === 'En progreso') badge.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
        else badge.classList.add('bg-[#84cc1620]', 'text-accion', 'border-[#84cc1640]');

        document.getElementById('modal-municipio').innerText = reporte.municipio ? reporte.municipio.nombre : 'No especificado';
        
        if(reporte.area) {
            document.getElementById('modal-departamento').innerHTML = `<span style="color: ${reporte.area.color}"><i class="ph-fill ph-buildings"></i> ${reporte.area.nombre}</span>`;
        } else {
            document.getElementById('modal-departamento').innerHTML = '<span class="italic text-slate-400">Sin asignar</span>';
        }

        if(reporte.cuadrilla) {
            document.getElementById('modal-cuadrilla').innerHTML = `<i class="ph-fill ${reporte.cuadrilla.icono || 'ph-truck'} text-accion"></i> ${reporte.cuadrilla.nombre}`;
        } else {
            document.getElementById('modal-cuadrilla').innerHTML = '<span class="italic text-slate-400">Ninguna</span>';
        }

        const fechaAct = new Date(reporte.updated_at);
        document.getElementById('modal-actualizacion').innerText = fechaAct.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute:'2-digit' });

        const iconClass = getCategoryIcon(reporte.categoria);
        document.getElementById('modal-categoria').innerHTML = `<i class="ph-fill ${iconClass} text-accion"></i> ${reporte.categoria}`;
        const fechaCreacion = new Date(reporte.created_at);
        document.getElementById('modal-fecha').innerText = fechaCreacion.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' });
        
        document.getElementById('modal-descripcion').innerText = reporte.descripcion;
        
        const containerTelefono = document.getElementById('modal-telefono-container');
        if (reporte.telefono_contacto && String(reporte.telefono_contacto).trim() !== "" && String(reporte.telefono_contacto).trim() !== "null") {
            containerTelefono.innerHTML = `
                <div class="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/50 p-4 rounded-xl flex justify-between items-center mt-4">
                    <div>
                        <p class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider mb-1">Contacto Ciudadano</p>
                        <p class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <i class="ph-fill ph-whatsapp-logo text-green-500 text-xl"></i>
                            <span>+52 ${reporte.telefono_contacto}</span>
                        </p>
                    </div>
                    <a href="https://wa.me/52${reporte.telefono_contacto}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white p-2.5 rounded-lg shadow-sm transition" title="Abrir WhatsApp Web">
                        <i class="ph-bold ph-chat-teardrop-text text-lg"></i>
                    </a>
                </div>
            `;
        } else {
            containerTelefono.innerHTML = '';
        }

        const fotoContainer = document.getElementById('modal-foto-container');
        if (reporte.ruta_foto) {
            fotoContainer.classList.remove('hidden');
            document.getElementById('modal-foto').src = '/storage/' + reporte.ruta_foto;
        } else {
            fotoContainer.classList.add('hidden');
        }

        document.getElementById('modal-detalle-reporte').classList.remove('hidden');

        setTimeout(() => {
            if (!modalMapInstance) {
                modalMapInstance = L.map('modal-map', { zoomControl: false }).setView([reporte.latitud, reporte.longitud], 17);
                L.control.zoom({ position: 'bottomleft' }).addTo(modalMapInstance);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(modalMapInstance);
                modalMarkerInstance = L.marker([reporte.latitud, reporte.longitud]).addTo(modalMapInstance);
            } else {
                modalMapInstance.setView([reporte.latitud, reporte.longitud], 17);
                modalMarkerInstance.setLatLng([reporte.latitud, reporte.longitud]);
            }
            modalMapInstance.invalidateSize();
            
            document.getElementById('modal-direccion').innerText = 'Buscando coordenadas exactas...';
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${reporte.latitud}&lon=${reporte.longitud}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if(data && data.address) {
                        let calle = data.address.road || data.address.pedestrian || '';
                        let colonia = data.address.neighbourhood || data.address.suburb || '';
                        let num = data.address.house_number || '';
                        let direccionFinal = calle + (num ? ' #'+num : '') + (colonia ? ', Col. '+colonia : '');
                        document.getElementById('modal-direccion').innerText = direccionFinal || data.display_name || 'Dirección sin nombre.';
                    }
                })
                .catch(() => document.getElementById('modal-direccion').innerText = 'No se pudo conectar con el satélite.');
                
        }, 150);
    }

    function cerrarModalReporte() {
        // Ocultar modal
        document.getElementById('modal-detalle-reporte').classList.add('hidden');
        
        // APAGAR AUREOLA DE NEÓN GLOBALMENTE CUANDO EL ADMINISTRADOR TERMINA
        document.querySelectorAll('.highlight-card').forEach(elemento => {
            elemento.classList.remove('highlight-card');
        });
    }

    function cambiarVista(vista) {
        const kanban = document.getElementById('vista-kanban');
        const lista = document.getElementById('vista-lista');
        const btnKanban = document.getElementById('btn-kanban');
        const btnLista = document.getElementById('btn-lista');

        if (vista === 'kanban') { 
            kanban.classList.remove('hidden'); 
            lista.classList.add('hidden'); 
            
            if (btnKanban) btnKanban.className = 'px-4 py-2 rounded-md text-sm font-bold flex items-center gap-2 bg-white dark:bg-slate-700 text-institucional dark:text-blue-400 shadow-sm transition-all whitespace-nowrap border border-slate-200/50 dark:border-slate-600';
            if (btnLista) btnLista.className = 'px-4 py-2 rounded-md text-sm font-bold flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-institucional dark:hover:text-blue-400 transition-all whitespace-nowrap border border-transparent';
        } else { 
            lista.classList.remove('hidden'); 
            kanban.classList.add('hidden'); 
            
            if (btnLista) btnLista.className = 'px-4 py-2 rounded-md text-sm font-bold flex items-center gap-2 bg-white dark:bg-slate-700 text-institucional dark:text-blue-400 shadow-sm transition-all whitespace-nowrap border border-slate-200/50 dark:border-slate-600';
            if (btnKanban) btnKanban.className = 'px-4 py-2 rounded-md text-sm font-bold flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-institucional dark:hover:text-blue-400 transition-all whitespace-nowrap border border-transparent';
        }
        
        localStorage.setItem('preferenciaVistaReportes', vista);
    }
    
    function filtrarReportes() {
        const query = document.getElementById('input-buscador').value.toLowerCase();
        const allItems = [...document.querySelectorAll('.reporte-item'), ...document.querySelectorAll('.reporte-lista-item')];
        allItems.forEach(item => {
            const f = item.getAttribute('data-folio')||'', d = item.getAttribute('data-descripcion')||'', c = item.getAttribute('data-categoria')||'', fe = item.getAttribute('data-fecha')||'';
            item.style.display = (f.includes(query) || d.includes(query) || c.includes(query) || fe.includes(query)) ? '' : 'none';
        });
        ['col-pendientes', 'col-en-progreso', 'col-resueltos'].forEach(id => {
            const c = document.getElementById(id);
            if(c) {
                let v = 0; c.querySelectorAll('.reporte-item').forEach(i => { if (i.style.display !== 'none') v++; });
                document.getElementById('count-'+id.split('-')[1]+(id.split('-')[2]?'-'+id.split('-')[2]:'')).innerText = v;
            }
        });
    }
</script>