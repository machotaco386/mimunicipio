<!-- Archivo: resources/views/layouts/master.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Master - MiMunicipio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Paleta exclusiva del corporativo */
        body { background-color: #0f172a; color: #f8fafc; font-family: ui-sans-serif, system-ui, sans-serif; }
        .bg-card { background-color: #1e293b; border-color: #334155; }
        .text-brand { color: #a855f7; } /* Acento Púrpura */
        .bg-brand { background-color: #9333ea; }
        .hover-brand:hover { background-color: #7e22ce; }
    </style>
</head>
<body class="flex h-screen overflow-hidden selection:bg-purple-500 selection:text-white">

    <!-- Sidebar Master -->
    <aside class="w-64 bg-[#0B1120] border-r border-slate-800 flex flex-col hidden md:flex z-50">
        <div class="h-16 flex items-center px-6 border-b border-slate-800">
            <i class="ph-fill ph-rocket-launch text-2xl text-purple-500 mr-2"></i>
            <span class="font-bold text-xl tracking-tight text-white">MiMunicipio</span>
        </div>
        
        <div class="p-4 overflow-y-auto flex-grow">
            <p class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-3 px-2">Gestión de Clientes</p>
            <nav class="space-y-1">
                <a href="{{ route('master.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-bold transition bg-purple-600/10 text-purple-400 border border-purple-500/20">
                    <i class="ph-fill ph-buildings text-lg"></i> Red de Municipios
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-bold transition text-slate-400 hover:text-white hover:bg-white/5 opacity-50 cursor-not-allowed">
                    <i class="ph-fill ph-credit-card text-lg"></i> Facturación (Próximamente)
                </a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 text-sm font-bold text-red-400 hover:text-red-300 hover:bg-red-400/10 transition py-2 rounded-lg">
                    <i class="ph-bold ph-sign-out"></i> Cerrar Sesión Segura
                </button>
            </form>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-[#0B1120] border-b border-slate-800 flex items-center justify-between px-6 z-10">
            <h1 class="text-lg font-bold text-slate-200">Panel de Control</h1>
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-slate-400">{{ auth()->user()->name ?? 'Corporativo' }}</span>
                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center border border-slate-700">
                    <i class="ph-bold ph-user text-slate-400"></i>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-8">
            @yield('content')
        </main>
    </div>
</body>
</html>