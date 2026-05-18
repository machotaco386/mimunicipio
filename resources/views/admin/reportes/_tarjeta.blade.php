<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition group">
    <div class="flex justify-between items-start mb-2">
        <span class="text-xs font-bold px-2 py-1 rounded bg-slate-100 text-slate-600 uppercase tracking-wide">{{ $reporte->categoria }}</span>
        <span class="text-xs text-slate-400 font-medium" title="{{ $reporte->created_at }}">{{ $reporte->created_at->diffForHumans() }}</span>
    </div>
    
    <h3 class="font-mono font-bold text-institucional text-lg mb-1">{{ $reporte->folio }}</h3>
    <p class="text-sm text-slate-600 line-clamp-2 mb-4" title="{{ $reporte->descripcion }}">{{ $reporte->descripcion }}</p>
    
    <!-- Evidencia fotográfica si existe -->
    @if($reporte->ruta_foto)
        <a href="{{ asset('storage/' . $reporte->ruta_foto) }}" target="_blank" class="text-xs text-accion font-bold flex items-center gap-1 mb-4 hover:underline">
            <i class="ph-bold ph-image"></i> Ver Evidencia
        </a>
    @endif
    
    <hr class="border-slate-100 mb-3">
    
    <!-- Formulario rápido para cambiar de estado -->
    <form action="{{ route('admin.reportes.estado', $reporte) }}" method="POST" class="flex gap-2">
        @csrf
        @method('PATCH')
        <select name="estado" class="text-xs rounded-lg border-slate-200 bg-slate-50 text-slate-700 py-1.5 px-2 flex-grow focus:ring-accion focus:border-accion outline-none" onchange="this.form.submit()">
            <option value="Pendiente" {{ $reporte->estado == 'Pendiente' ? 'selected' : '' }}>Marcar Pendiente</option>
            <option value="En progreso" {{ $reporte->estado == 'En progreso' ? 'selected' : '' }}>Marcar En Proceso</option>
            <option value="Resuelto" {{ $reporte->estado == 'Resuelto' ? 'selected' : '' }}>Marcar Resuelto</option>
        </select>
    </form>
</div>
