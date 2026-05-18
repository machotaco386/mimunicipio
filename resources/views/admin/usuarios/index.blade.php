@extends('layouts.admin')

@section('title', 'Gestión de Usuarios - MiMunicipio')
@section('header_title', 'Usuarios Administrativos')

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
    
    <!-- Lado Izquierdo: Lista de Usuarios -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                <i class="ph-bold ph-users text-accion"></i> Personal Autorizado
            </h2>
            <span class="bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1 rounded-full">{{ $usuarios->count() }} usuarios</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4 font-bold">Nombre</th>
                        <th class="p-4 font-bold">Rol</th>
                        <th class="p-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @foreach($usuarios as $usuario)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="p-4">
                            <p class="font-bold text-institucional">{{ $usuario->name }}</p>
                            <p class="text-xs text-slate-500">{{ $usuario->email }}</p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border
                                {{ $usuario->rol === 'admin' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $usuario->rol }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            @if($usuario->id !== auth()->id())
                                <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar el acceso a este usuario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition p-1.5 rounded-lg hover:bg-red-50">
                                        <i class="ph-bold ph-trash text-lg"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400 italic">Tú</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lado Derecho: Formulario de Alta -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                <i class="ph-bold ph-user-plus text-accion"></i> Registrar Nuevo
            </h2>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.usuarios.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre Completo</label>
                    <input type="text" name="name" required class="w-full rounded-lg border-slate-300 border p-2.5 text-sm focus:ring-accion focus:border-accion outline-none" placeholder="Ej. Ana López">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" required class="w-full rounded-lg border-slate-300 border p-2.5 text-sm focus:ring-accion focus:border-accion outline-none" placeholder="operador@municipio.com">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Contraseña de Acceso</label>
                    <input type="password" name="password" required minlength="6" class="w-full rounded-lg border-slate-300 border p-2.5 text-sm focus:ring-accion focus:border-accion outline-none" placeholder="Mínimo 6 caracteres">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nivel de Permisos</label>
                    <select name="rol" required class="w-full rounded-lg border-slate-300 border p-2.5 text-sm focus:ring-accion focus:border-accion outline-none">
                        <option value="operador">Operador (Atiende reportes)</option>
                        <option value="admin">Administrador (Control total)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-institucional hover:bg-blue-900 text-white font-bold py-3 rounded-xl transition-colors mt-2 shadow-md">
                    Crear Cuenta
                </button>
            </form>
        </div>
    </div>

</div>
@endsection