@extends('layouts.admin')

@section('title', 'Gestión de Cuadrillas - MiMunicipio')
@section('header_title', 'Vehículos y Brigadas Operativas')

@section('content')

@if (session('success'))
    <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-xl mb-6 flex items-center gap-3 border border-green-200 dark:border-green-800 shadow-sm">
        <i class="ph-fill ph-check-circle text-xl"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 p-4 rounded-xl mb-6 flex items-center gap-3 border border-red-200 dark:border-red-800 shadow-sm">
        <i class="ph-fill ph-warning-circle text-xl"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- COLUMNA IZQUIERDA: FLOTILLA -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-institucional dark:text-blue-400 flex items-center gap-2">
                <i class="ph-bold ph-users-three text-accion"></i> Brigadas Activas
            </h2>
        </div>
        
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @forelse($cuadrillas as $cuadrilla)
                <!-- TARJETA DE CUADRILLA -->
                <div class="border border-slate-200 dark:border-slate-700 rounded-2xl p-5 bg-white dark:bg-slate-800 relative group hover:border-slate-300 dark:hover:border-slate-600 transition shadow-sm flex flex-col h-full">
                    
                    <!-- BOTONES DE ACCIÓN (Editar y Eliminar) -->
                    <div class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition z-10">
                        <button type="button" onclick="abrirEditarModal({{ $cuadrilla->id }}, '{{ $cuadrilla->nombre }}', '{{ $cuadrilla->icono }}')" class="text-blue-400 hover:text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-full p-1.5 shadow-sm border border-blue-100 dark:border-blue-800 transition">
                            <i class="ph-bold ph-pencil-simple"></i>
                        </button>
                        <form action="{{ route('admin.cuadrillas.destroy', $cuadrilla) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta cuadrilla?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 dark:text-red-300 bg-red-50 dark:bg-red-900/30 rounded-full p-1.5 shadow-sm border border-red-100 dark:border-red-800 transition">
                                <i class="ph-bold ph-x"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="flex items-center gap-4 mb-4 mt-2">
                        <!-- ÍCONO DINÁMICO -->
                        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-institucional dark:text-blue-400 border border-slate-200 dark:border-slate-600 shadow-sm">
                            <i class="ph-fill {{ $cuadrilla->icono ?? 'ph-users-three' }} text-2xl"></i>
                        </div>
                        <div class="pr-12">
                            <h3 class="font-black text-slate-800 dark:text-white text-lg leading-tight">{{ $cuadrilla->nombre }}</h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5" style="color: {{ $cuadrilla->area->color }}">{{ $cuadrilla->area->nombre }}</p>
                        </div>
                    </div>
                    
                    <!-- Resumen Visual de Tripulación (CORRECCIÓN DE DARK MODE) -->
                    <div class="flex-grow mb-5 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider flex items-center justify-between">
                            Tripulación Activa 
                            <span class="bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full text-[10px]">{{ $cuadrilla->trabajadores->count() }}</span>
                        </p>
                        @if($cuadrilla->trabajadores->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($cuadrilla->trabajadores as $t)
                                    <span class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-700 dark:text-slate-200 shadow-sm">
                                        <span class="w-2 h-2 rounded-full bg-accion"></span> {{ explode(' ', trim($t->name))[0] }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 italic">Brigada vacía. Asigna personal.</p>
                        @endif
                    </div>
                    
                    <button type="button" onclick="document.getElementById('modal-cuadrilla-{{$cuadrilla->id}}').classList.remove('hidden')" class="w-full bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm shadow-md">
                        <i class="ph-bold ph-users text-lg"></i> Asignar Personal
                    </button>
                </div>

                <!-- ======================================================== -->
                <!-- MODAL INTERACTIVO DE DRAG & DROP POR CUADRILLA           -->
                <!-- ======================================================== -->
                <div id="modal-cuadrilla-{{$cuadrilla->id}}" class="hidden fixed inset-0 z-[600] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
                    <form action="{{ route('admin.cuadrillas.asignar', $cuadrilla) }}" method="POST" class="bg-white dark:bg-slate-800 rounded-3xl max-w-5xl w-full p-6 sm:p-8 shadow-2xl flex flex-col max-h-[90vh]">
                        @csrf
                        
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-black text-institucional dark:text-blue-400 flex items-center gap-2"><i class="ph-fill {{ $cuadrilla->icono ?? 'ph-users-three' }} text-accion"></i> {{ $cuadrilla->nombre }}</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Arrastra los trabajadores entre las listas para asignarlos.</p>
                            </div>
                            <button type="button" onclick="document.getElementById('modal-cuadrilla-{{$cuadrilla->id}}').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition p-2 bg-slate-50 dark:bg-slate-700 rounded-full border border-slate-200 dark:border-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                        </div>

                        <!-- INTERFAZ DUAL LISTBOX CON DARK MODE NATIVO -->
                        <div class="flex flex-col md:flex-row gap-4 flex-grow overflow-hidden min-h-[400px]">
                            
                            <!-- PANEL IZQUIERDO: DISPONIBLES -->
                            <div class="flex-1 flex flex-col border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden bg-slate-50/50 dark:bg-slate-900/50 shadow-inner">
                                <div class="bg-slate-100 dark:bg-slate-800 p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                                    <span class="font-bold text-slate-700 dark:text-slate-300 text-sm flex items-center gap-2"><i class="ph-bold ph-users text-slate-400"></i> Personal Disponible</span>
                                </div>
                                <div class="flex-grow p-3 overflow-y-auto list-container transition-colors" id="disp-{{$cuadrilla->id}}" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event)">
                                    <!-- CORRECCIÓN: Permitimos que cualquier trabajador sea asignado a cualquier cuadrilla -->
                                    @foreach($trabajadoresDisponibles as $t)
                                        @if(!$cuadrilla->trabajadores->contains($t->id))
                                            <div class="worker-item bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 p-3 rounded-xl mb-3 shadow-sm cursor-grab flex justify-between items-center transition hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md" draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" id="worker-{{$cuadrilla->id}}-{{$t->id}}" data-id="{{$t->id}}">
                                                <div class="flex items-center gap-3 pointer-events-none">
                                                    <div class="avatar-container w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center transition-colors"><i class="avatar-icon ph-bold ph-user text-slate-500 dark:text-slate-400 transition-colors"></i></div>
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{$t->name}}</p>
                                                        <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{$t->email}} &bull; {{ $t->area ? $t->area->nombre : 'Global' }}</p>
                                                    </div>
                                                </div>
                                                <input type="checkbox" class="worker-cb w-5 h-5 text-institucional rounded border-slate-300 focus:ring-institucional cursor-pointer">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- CONTROLES CENTRALES -->
                            <div class="flex flex-row md:flex-col justify-center gap-3 items-center py-2 md:py-0">
                                <button type="button" onclick="moveSelected('disp-{{$cuadrilla->id}}', 'asig-{{$cuadrilla->id}}')" class="p-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 shadow-sm hover:bg-institucional dark:hover:bg-blue-600 hover:text-white text-slate-600 dark:text-slate-300 rounded-xl transition" title="Agregar seleccionados">
                                    <i class="ph-bold ph-caret-right hidden md:block text-xl"></i>
                                    <i class="ph-bold ph-caret-down block md:hidden text-xl"></i>
                                </button>
                                <button type="button" onclick="moveSelected('asig-{{$cuadrilla->id}}', 'disp-{{$cuadrilla->id}}')" class="p-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 shadow-sm hover:bg-red-500 dark:hover:bg-red-600 hover:text-white text-slate-600 dark:text-slate-300 rounded-xl transition" title="Quitar seleccionados">
                                    <i class="ph-bold ph-caret-left hidden md:block text-xl"></i>
                                    <i class="ph-bold ph-caret-up block md:hidden text-xl"></i>
                                </button>
                            </div>

                            <!-- PANEL DERECHO: ASIGNADOS -->
                            <div class="flex-1 flex flex-col border-2 border-accion/40 dark:border-accion/60 rounded-2xl overflow-hidden bg-green-50/30 dark:bg-green-900/10 shadow-inner">
                                <div class="bg-accion/10 dark:bg-accion/20 p-4 border-b border-accion/20 dark:border-accion/30 flex justify-between items-center">
                                    <span class="font-bold text-institucional dark:text-green-400 text-sm flex items-center gap-2"><i class="ph-bold ph-check-circle text-accion"></i> En la Cuadrilla</span>
                                </div>
                                <div class="flex-grow p-3 overflow-y-auto list-container transition-colors" id="asig-{{$cuadrilla->id}}" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" ondrop="drop(event)">
                                    @foreach($cuadrilla->trabajadores as $t)
                                        <div class="worker-item bg-white dark:bg-slate-800 border border-accion p-3 rounded-xl mb-3 shadow-sm cursor-grab flex justify-between items-center transition hover:border-institucional hover:shadow-md" draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" id="worker-{{$cuadrilla->id}}-{{$t->id}}" data-id="{{$t->id}}">
                                            <input type="hidden" name="trabajadores[]" value="{{$t->id}}" class="worker-input">
                                            <div class="flex items-center gap-3 pointer-events-none">
                                                <div class="avatar-container w-10 h-10 rounded-full bg-accion flex items-center justify-center transition-colors"><i class="avatar-icon ph-bold ph-user text-white transition-colors"></i></div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{$t->name}}</p>
                                                    <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{$t->email}}</p>
                                                </div>
                                            </div>
                                            <input type="checkbox" class="worker-cb w-5 h-5 text-institucional rounded border-slate-300 focus:ring-institucional cursor-pointer">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-700">
                            <button type="button" onclick="document.getElementById('modal-cuadrilla-{{$cuadrilla->id}}').classList.add('hidden')" class="px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">Cancelar</button>
                            <button type="submit" class="px-8 py-3 rounded-xl bg-institucional hover:bg-blue-900 text-white font-bold transition shadow-xl shadow-institucional/20 flex items-center gap-2">
                                <i class="ph-bold ph-floppy-disk text-lg"></i> Guardar Tripulación
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="col-span-full p-12 text-center text-slate-400 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl bg-slate-50 dark:bg-slate-800/50">
                    <div class="w-20 h-20 bg-white dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-slate-100 dark:border-slate-600">
                        <i class="ph-light ph-users-three text-4xl text-slate-400"></i>
                    </div>
                    <p class="font-bold text-slate-500 dark:text-slate-300">No tienes brigadas registradas.</p>
                    <p class="text-sm mt-1">Usa el formulario de la derecha para crear tu primera unidad.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- COLUMNA DERECHA: FORMULARIO -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden sticky top-6">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
            <h2 class="text-lg font-bold text-institucional dark:text-blue-400 flex items-center gap-2">
                <i class="ph-bold ph-plus-circle text-accion"></i> Nueva Brigada
            </h2>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.cuadrillas.store') }}" method="POST" class="space-y-5">
                @csrf
                @if(auth()->user()->rol === 'super_admin')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pertenece al Departamento</label>
                        <select name="area_id" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 border p-3 text-sm focus:ring-accion outline-none">
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Identificador / Nombre</label>
                    <input type="text" name="nombre" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white border p-3 text-sm focus:ring-accion outline-none" placeholder="Ej. Camión Norte 02">
                </div>
                
                <!-- SELECTOR VISUAL DE ÍCONOS -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Ícono de Representación</label>
                    <div class="grid grid-cols-4 gap-2">
                        @php
                            $iconos = ['ph-users-three', 'ph-truck', 'ph-wrench', 'ph-hard-hat', 'ph-broom', 'ph-drop', 'ph-lightning', 'ph-tree'];
                        @endphp
                        @foreach($iconos as $index => $ico)
                            <label class="cursor-pointer">
                                <input type="radio" name="icono" value="{{ $ico }}" class="peer sr-only" {{ $index === 0 ? 'checked' : '' }}>
                                <div class="p-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-center peer-checked:bg-institucional peer-checked:text-white peer-checked:border-institucional dark:peer-checked:border-blue-500 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center justify-center">
                                    <i class="ph-bold {{ $ico }} text-2xl"></i>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="w-full bg-institucional hover:bg-blue-900 text-white font-bold py-3.5 rounded-xl transition-colors mt-2 shadow-md flex items-center justify-center gap-2">
                    Registrar Unidad <i class="ph-bold ph-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL DE EDICIÓN DE CUADRILLA (NUEVO)                    -->
<!-- ======================================================== -->
<div id="modal-editar-cuadrilla" class="hidden fixed inset-0 z-[700] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-institucional dark:text-blue-400 flex items-center gap-2"><i class="ph-bold ph-pencil-simple text-accion"></i> Editar Cuadrilla</h2>
            <button type="button" onclick="cerrarEditarModal()" class="text-slate-400 hover:text-red-500 transition"><i class="ph-bold ph-x text-lg"></i></button>
        </div>
        <form id="form-editar-cuadrilla" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                <input type="text" id="edit-nombre" name="nombre" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white border p-3 text-sm focus:ring-accion outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Ícono</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach($iconos as $ico)
                        <label class="cursor-pointer">
                            <input type="radio" name="icono" value="{{ $ico }}" id="edit-ico-{{ $ico }}" class="peer sr-only">
                            <div class="p-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-center peer-checked:bg-institucional peer-checked:text-white peer-checked:border-institucional dark:peer-checked:border-blue-500 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center justify-center">
                                <i class="ph-bold {{ $ico }} text-2xl"></i>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="w-full bg-institucional hover:bg-blue-900 text-white font-bold py-3 rounded-xl transition shadow-md">Guardar Cambios</button>
        </form>
    </div>
</div>

@endsection

@stack('scripts')
<script>
    // ========================================================
    // LÓGICA DE EDICIÓN
    // ========================================================
    function abrirEditarModal(id, nombre, iconoActual) {
        const modal = document.getElementById('modal-editar-cuadrilla');
        const form = document.getElementById('form-editar-cuadrilla');
        
        form.action = `/admin/cuadrillas/${id}`;
        document.getElementById('edit-nombre').value = nombre;
        
        // Seleccionar el ícono actual
        const radioIcono = document.getElementById('edit-ico-' + (iconoActual || 'ph-users-three'));
        if(radioIcono) radioIcono.checked = true;
        
        modal.classList.remove('hidden');
    }

    function cerrarEditarModal() {
        document.getElementById('modal-editar-cuadrilla').classList.add('hidden');
    }

    // ========================================================
    // MOTOR DE DRAG & DROP Y SELECCIÓN MULTIPLE (DARK MODE READY)
    // ========================================================

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.currentTarget.id);
        ev.currentTarget.classList.add('opacity-40', 'scale-95'); 
    }

    function dragEnd(ev) { ev.currentTarget.classList.remove('opacity-40', 'scale-95'); }

    function allowDrop(ev) {
        ev.preventDefault();
        ev.currentTarget.classList.add('bg-slate-200/50', 'dark:bg-slate-700/50', 'ring-2', 'ring-inset', 'ring-institucional/30'); 
    }

    function dragLeave(ev) {
        ev.currentTarget.classList.remove('bg-slate-200/50', 'dark:bg-slate-700/50', 'ring-2', 'ring-inset', 'ring-institucional/30');
    }

    function drop(ev) {
        ev.preventDefault();
        let targetList = ev.currentTarget;
        targetList.classList.remove('bg-slate-200/50', 'dark:bg-slate-700/50', 'ring-2', 'ring-inset', 'ring-institucional/30');

        var dataId = ev.dataTransfer.getData("text");
        var item = document.getElementById(dataId);

        if (item && targetList.classList.contains('list-container')) moverElemento(item, targetList);
    }

    function moveSelected(sourceId, targetId) {
        const source = document.getElementById(sourceId);
        const target = document.getElementById(targetId);
        const checkboxes = source.querySelectorAll('.worker-cb:checked');

        checkboxes.forEach(cb => {
            const item = cb.closest('.worker-item');
            moverElemento(item, target);
            cb.checked = false; 
        });
    }

    function moverElemento(item, targetList) {
        targetList.appendChild(item);
        
        const isAsignado = targetList.id.startsWith('asig-');
        const userId = item.getAttribute('data-id');
        
        const existingInput = item.querySelector('.worker-input');
        if (existingInput) existingInput.remove();

        if (isAsignado) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'trabajadores[]';
            input.value = userId;
            input.className = 'worker-input';
            item.appendChild(input);

            item.classList.replace('border-slate-200', 'border-accion');
            item.classList.replace('dark:border-slate-600', 'dark:border-accion');
            
            const iconContainer = item.querySelector('.avatar-container');
            iconContainer.classList.replace('bg-slate-100', 'bg-accion');
            iconContainer.classList.replace('dark:bg-slate-700', 'bg-accion');
            
            const icon = item.querySelector('.avatar-icon');
            icon.classList.replace('text-slate-500', 'text-white');
            icon.classList.replace('dark:text-slate-400', 'text-white');
        } else {
            item.classList.replace('border-accion', 'border-slate-200');
            item.classList.replace('dark:border-accion', 'dark:border-slate-600');
            
            const iconContainer = item.querySelector('.avatar-container');
            iconContainer.classList.replace('bg-accion', 'bg-slate-100');
            iconContainer.classList.replace('bg-accion', 'dark:bg-slate-700'); // Falsa clase para reset
            iconContainer.classList.add('dark:bg-slate-700'); // Refuerzo
            
            const icon = item.querySelector('.avatar-icon');
            icon.classList.replace('text-white', 'text-slate-500');
            icon.classList.add('dark:text-slate-400'); // Refuerzo
        }
    }
</script>