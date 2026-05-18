<!-- Archivo: resources/views/admin/reportes/_tarjeta_inmersiva.blade.php -->
@php
    $diasActivo = $reporte->created_at->diffInDays(now());
    $urgenciaClass = 'text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-700/50';
    $urgenciaIcon = 'ph-clock';
    $urgenciaText = $reporte->created_at->diffForHumans(null, true, true); 
    $bordeTarjeta = 'border-slate-200 dark:border-slate-700/80';

    if ($reporte->estado !== 'Resuelto') {
        if ($diasActivo >= 5) {
            $urgenciaClass = 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 font-bold';
            $urgenciaIcon = 'ph-warning-octagon animate-pulse';
            $urgenciaText = $diasActivo . ' d';
            $bordeTarjeta = 'border-red-300 dark:border-red-700/50 shadow-red-100 dark:shadow-none shadow-sm';
        } elseif ($diasActivo >= 3) {
            $urgenciaClass = 'text-orange-700 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 font-bold';
            $urgenciaIcon = 'ph-warning';
            $urgenciaText = $diasActivo . ' d';
            $bordeTarjeta = 'border-orange-300 dark:border-orange-700/50 shadow-orange-100 dark:shadow-none shadow-sm';
        }
    }
    
    $areaColor = $reporte->area ? $reporte->area->color : '#64748b'; 
@endphp

<!-- Contenedor Draggable Tamaño Balanceado -->
<div id="reporte-card-{{ $reporte->id }}" draggable="true" ondragstart="dragReporte(event)" ondragend="dragEndReporte(event)"
     class="reporte-item bg-white dark:bg-slate-800 rounded-xl shadow-sm border {{ $bordeTarjeta }} p-4 hover:shadow-md hover:border-institucional/50 dark:hover:border-blue-500/50 cursor-grab active:cursor-grabbing transition-all group flex flex-col gap-3 mb-3 relative"
     data-id="{{ $reporte->id }}" data-estado="{{ $reporte->estado }}" data-index="{{ $index }}" data-timestamp="{{ $reporte->created_at->timestamp }}" data-categoria="{{ strtolower($reporte->categoria) }}" data-folio="{{ strtolower($reporte->folio) }}" data-descripcion="{{ strtolower($reporte->descripcion) }}" data-fecha="{{ $reporte->created_at->format('d/m/Y') }}">

    <!-- HEADER: Badges Claros -->
    <div class="flex justify-between items-center pointer-events-none">
        <span class="text-[10px] font-black px-2.5 py-1 rounded uppercase tracking-widest border flex items-center gap-1.5 shadow-sm" 
              style="background-color: {{ $areaColor }}10; color: {{ $areaColor }}; border-color: {{ $areaColor }}30;">
            <i class="ph-fill ph-buildings"></i> {{ $reporte->area ? $reporte->area->nombre : $reporte->categoria }}
        </span>
        <span class="text-[10px] flex items-center gap-1 border border-slate-100 dark:border-slate-600 px-2 py-1 rounded shadow-sm {{ $urgenciaClass }}" title="Ingresado: {{ $reporte->created_at }}">
            <i class="ph-bold {{ $urgenciaIcon }}"></i> {{ $urgenciaText }}
        </span>
    </div>
    
    <!-- FOLIO Y DESCRIPCIÓN: Legibilidad y 2 líneas -->
    <div class="pointer-events-none">
        <div class="flex items-center justify-between mb-1.5">
            <h3 class="font-mono font-black text-institucional dark:text-blue-300 text-base leading-none tracking-tight">{{ $reporte->folio }}</h3>
            @if($reporte->ruta_foto)
                <i class="ph-fill ph-image text-slate-300 dark:text-slate-500 text-sm" title="Contiene evidencia"></i>
            @endif
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed" title="{{ $reporte->descripcion }}">{{ $reporte->descripcion }}</p>
    </div>
    
    <!-- ASIGNACIÓN DE CUADRILLA -->
    <button type="button" onclick="abrirModalAsignacion({{ $reporte->id }}, '{{ $reporte->folio }}', {{ $reporte->area_id ?? 'null' }}, {{ $reporte->cuadrilla_id ?? 'null' }}, '{{ $reporte->estado }}')" 
            class="w-full flex items-center justify-between bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 hover:border-institucional dark:hover:border-blue-500 transition px-3 py-2 rounded-lg text-left shadow-sm group-hover:bg-blue-50/30 dark:group-hover:bg-slate-700/30">
        <div class="flex items-center gap-2 overflow-hidden w-full">
            <div class="w-6 h-6 rounded flex-shrink-0 flex items-center justify-center {{ $reporte->cuadrilla ? 'bg-institucional text-white dark:bg-blue-600' : 'bg-white dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-600' }}">
                <i class="ph-fill {{ $reporte->cuadrilla ? ($reporte->cuadrilla->icono ?? 'ph-truck') : 'ph-user-plus' }} text-xs"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none mb-0.5">Brigada Asignada</p>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate w-full">{{ $reporte->cuadrilla ? $reporte->cuadrilla->nombre : 'Sin asignar (Clic)' }}</p>
            </div>
        </div>
        <i class="ph-bold ph-caret-down text-slate-400 text-[10px]"></i>
    </button>

    <div class="flex items-center gap-2 mt-1 pt-3 border-t border-slate-100 dark:border-slate-700/80">
        
        <!-- SELECTOR DE ESTADO (Reemplaza las flechas anteriores) -->
        <form id="form-estado-{{ $reporte->id }}" action="{{ route('admin.reportes.estado', $reporte) }}" method="POST" class="flex-grow m-0 relative">
            @csrf @method('PATCH')
            <input type="hidden" name="cuadrilla_id" value="{{ $reporte->cuadrilla_id }}">
            
            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                @if($reporte->estado == 'Pendiente') <i class="ph-fill ph-warning-circle text-amber-500"></i>
                @elseif($reporte->estado == 'En progreso') <i class="ph-fill ph-gear text-blue-500 animate-spin-slow"></i>
                @else <i class="ph-fill ph-check-circle text-accion"></i> @endif
            </div>
            
            <select name="estado" class="input-estado-secreto w-full text-xs font-bold rounded-lg border py-2 pl-8 pr-6 outline-none cursor-pointer transition appearance-none shadow-sm
                {{ $reporte->estado == 'Pendiente' ? 'bg-amber-50 dark:bg-amber-900/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/50' : '' }}
                {{ $reporte->estado == 'En progreso' ? 'bg-blue-50 dark:bg-blue-900/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/50' : '' }}
                {{ $reporte->estado == 'Resuelto' ? 'bg-[#84cc1615] dark:bg-[#84cc1610] text-accion border-[#84cc1640] dark:border-[#84cc1630]' : '' }}"
                onchange="guardarEstadoModificado({{ $reporte->id }}, this.form)">
                <option value="Pendiente" {{ $reporte->estado == 'Pendiente' ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white">Pendiente</option>
                <option value="En progreso" {{ $reporte->estado == 'En progreso' ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white">En Proceso</option>
                <option value="Resuelto" {{ $reporte->estado == 'Resuelto' ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white">Resuelto</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 {{ $reporte->estado == 'Pendiente' ? 'text-amber-500' : ($reporte->estado == 'En progreso' ? 'text-blue-500' : 'text-accion') }}"><i class="ph-bold ph-caret-down text-[10px]"></i></div>
        </form>

        <!-- Botón Detalles (Ojo) -->
        <button type="button" onclick="abrirModalReporte(this)" data-reporte="{{ json_encode($reporte->load('municipio', 'area', 'cuadrilla')) }}" 
                class="flex-shrink-0 text-slate-400 hover:text-institucional dark:hover:text-blue-400 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-500 transition px-3 py-2 rounded-lg shadow-sm" title="Detalles">
            <i class="ph-bold ph-eye text-lg"></i>
        </button>
    </div>
</div>