<!-- Archivo: resources/views/master/index.blade.php -->
@extends('layouts.master')

@section('content')

<!-- ALERTA DE SEGURIDAD: VISUALIZACIÓN EFÍMERA DE CREDENCIALES -->
@if (session('credenciales'))
    <div class="bg-blue-900/20 border border-blue-500/30 p-6 rounded-xl mb-8 shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <i class="ph-fill ph-shield-check text-2xl text-blue-400"></i>
                <div>
                    <h3 class="text-lg font-bold text-white">Credenciales Generadas</h3>
                    <p class="text-sm text-blue-300">Copia y envía estos datos al cliente. La contraseña desaparecerá permanentemente al recargar la página.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-[#0B1120] p-4 rounded-lg border border-blue-900/50">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mb-1">Cliente</p>
                    <p class="font-bold text-slate-200">{{ session('credenciales')['municipio'] }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mb-1">Usuario</p>
                    <p class="font-bold text-slate-200 select-all">{{ session('credenciales')['email'] }}</p>
                </div>
                <div class="relative">
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mb-1">Contraseña</p>
                    <div class="flex items-center gap-2">
                        <code class="font-mono text-base font-bold text-green-400 bg-green-400/10 px-2 py-1 rounded select-all" id="secure-password">{{ session('credenciales')['password'] }}</code>
                        <button onclick="copiarPassword()" id="btn-copy" class="text-slate-400 hover:text-white transition p-1.5 rounded" title="Copiar">
                            <i class="ph-bold ph-copy text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if (session('success'))
    <div class="bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl mb-6 flex items-center gap-3 shadow-lg">
        <i class="ph-fill ph-check-circle text-xl"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6 shadow-lg">
        <div class="flex items-center gap-2 mb-2 font-bold">
            <i class="ph-fill ph-warning-circle text-lg"></i> Revisa los siguientes datos:
        </div>
        <ul class="text-sm list-disc list-inside px-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Tarjetas KPI del Negocio -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-card border rounded-2xl p-6 shadow-lg relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 relative z-10">Municipios Activos</p>
        <div class="flex items-center justify-between relative z-10">
            <h3 class="text-4xl font-black text-white">{{ $totalClientes }}</h3>
            <i class="ph-fill ph-buildings text-4xl text-purple-500"></i>
        </div>
    </div>
    
    <div class="bg-card border rounded-2xl p-6 shadow-lg relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 relative z-10">Total Usuarios (SaaS)</p>
        <div class="flex items-center justify-between relative z-10">
            <h3 class="text-4xl font-black text-white">{{ $totalUsuariosSaaS }}</h3>
            <i class="ph-fill ph-users text-4xl text-blue-400"></i>
        </div>
    </div>
    
    <div class="bg-card border rounded-2xl p-6 shadow-lg relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-500/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 relative z-10">Volumen de Operaciones</p>
        <div class="flex items-center justify-between relative z-10">
            <h3 class="text-4xl font-black text-white">{{ $totalReportesPais }}</h3>
            <i class="ph-fill ph-chart-line-up text-4xl text-green-400"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Lado Izquierdo: Lista de Clientes -->
    <div class="lg:col-span-2 bg-card border rounded-2xl shadow-lg overflow-hidden flex flex-col h-[600px]">
        <div class="p-6 border-b border-slate-700 bg-slate-800/50">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ph-fill ph-database text-purple-400"></i> Base de Datos de Inquilinos (Tenants)
            </h2>
        </div>
        
        <div class="overflow-y-auto flex-grow">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-slate-800 z-10">
                    <tr class="text-slate-400 text-[10px] uppercase tracking-widest border-b border-slate-700">
                        <th class="p-4 font-black">Ayuntamiento / Inquilino</th>
                        <th class="p-4 font-black text-center">Usuarios Disp.</th>
                        <th class="p-4 font-bold text-center">Volumen Ops.</th>
                        <th class="p-4 font-bold text-center">Registro</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-800">
                    @forelse($municipios as $mun)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-white text-base">{{ $mun->nombre }}</div>
                            <span class="text-[9px] font-mono text-purple-400 uppercase tracking-widest border border-purple-500/30 px-1.5 py-0.5 rounded bg-purple-500/10">ID: {{ $mun->id }}</span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="font-bold text-slate-300">{{ $mun->usuarios_count }}</span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="font-bold text-slate-300">{{ $mun->reportes_count }}</span> <span class="text-xs text-slate-500">folios</span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="text-xs text-slate-500">{{ $mun->created_at->format('M Y') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-12 text-center text-slate-500 font-bold">Inicia registrando a tu primer cliente a la derecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lado Derecho: Formulario de Alta Onboarding -->
    <div class="bg-card border rounded-2xl shadow-lg overflow-hidden h-fit">
        <div class="p-5 border-b border-slate-700 bg-slate-800/50">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ph-bold ph-rocket-launch text-purple-400"></i> Alta de Cliente
            </h2>
            <p class="text-xs text-slate-400 mt-1">Despliegue de nuevo entorno aislado.</p>
        </div>

        <div class="p-5">
            <form action="{{ route('master.municipios.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1.5">Nombre del Municipio</label>
                    <input type="text" name="nombre_municipio" required class="w-full rounded-xl bg-[#0B1120] border-slate-700 border p-3 text-sm text-white focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-inner" placeholder="Ej. Ayuntamiento de Zapopan">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1.5">Nombre del Contacto</label>
                    <input type="text" name="admin_name" required class="w-full rounded-xl bg-[#0B1120] border-slate-700 border p-3 text-sm text-white focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-inner" placeholder="Ej. Juan Pérez">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1.5">Correo Administrativo</label>
                    <input type="email" name="admin_email" required class="w-full rounded-xl bg-[#0B1120] border-slate-700 border p-3 text-sm text-white focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-inner" placeholder="alcalde@zapopan.gob.mx">
                </div>

                <div class="bg-blue-500/10 border border-blue-500/30 p-3 rounded-lg flex items-center gap-2 mt-2">
                    <i class="ph-fill ph-info text-blue-400 text-lg"></i>
                    <p class="text-xs text-blue-200">Se generará una contraseña segura de un solo uso.</p>
                </div>

                <button type="submit" class="w-full bg-brand hover-brand text-white font-bold py-3.5 rounded-xl transition-colors mt-4 flex items-center justify-center gap-2">
                    <i class="ph-bold ph-plus-circle text-lg"></i> Crear Entorno
                </button>
            </form>
        </div>
    </div>

</div>

<!-- Script para copiar al portapapeles -->
<script>
    function copiarPassword() {
        const passwordTexto = document.getElementById('secure-password').innerText;
        navigator.clipboard.writeText(passwordTexto).then(() => {
            const btn = document.getElementById('btn-copy');
            btn.innerHTML = '<i class="ph-bold ph-check text-lg text-green-400"></i>';
            setTimeout(() => {
                btn.innerHTML = '<i class="ph-bold ph-copy text-lg"></i>';
            }, 2000);
        });
    }
</script>
@endsection