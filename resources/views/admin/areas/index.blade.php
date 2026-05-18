@extends('layouts.admin')

@section('title', 'Departamentos - MiMunicipio')
@section('header_title', 'Estructura Administrativa')

@section('content')

@if (session('success'))
    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-3 border border-green-200 shadow-sm">
        <i class="ph-fill ph-check-circle text-xl"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-3 border border-red-200 shadow-sm">
        <i class="ph-fill ph-warning-circle text-xl"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                <i class="ph-bold ph-buildings text-accion"></i> Departamentos del Municipio
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4 font-bold">Departamento</th>
                        <th class="p-4 font-bold text-center">Personal</th>
                        <th class="p-4 font-bold text-center">Cuadrillas</th>
                        <th class="p-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($areas as $area)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <span class="w-4 h-4 rounded-full shadow-inner border border-slate-200" style="background-color: {{ $area->color }}"></span>
                                <p class="font-bold text-institucional">{{ $area->nombre }}</p>
                            </div>
                        </td>
                        <td class="p-4 text-center font-bold text-slate-600">{{ $area->usuarios_count }}</td>
                        <td class="p-4 text-center font-bold text-slate-600">{{ $area->cuadrillas_count }}</td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este departamento?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition p-1.5 rounded-lg hover:bg-red-50"><i class="ph-bold ph-trash text-lg"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-8 text-center text-slate-400">No hay áreas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                <i class="ph-bold ph-plus-circle text-accion"></i> Registrar Área
            </h2>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.areas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre Oficial</label>
                    <input type="text" name="nombre" required class="w-full rounded-lg border-slate-300 border p-2.5 text-sm focus:ring-accion outline-none" placeholder="Ej. Obras Públicas">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Color Identificador</label>
                    <input type="color" name="color" value="#1A365D" required class="w-full h-12 rounded-lg border-slate-300 border p-1 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Se usará en el mapa y tablero Kanban.</p>
                </div>
                <button type="submit" class="w-full bg-institucional hover:bg-blue-900 text-white font-bold py-3 rounded-xl transition-colors mt-2 shadow-md">Crear Departamento</button>
            </form>
        </div>
    </div>
</div>
@endsection