<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel de Control - MiMunicipio')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class', 
            theme: {
                extend: {
                    colors: {
                        institucional: '#1A365D',
                        accion: '#84CC16',
                        sidebar: '#0f2038'
                    }
                }
            }
        }
    </script>

    <!-- MOTOR DE ACCESIBILIDAD -->
    <script>
        const sysTheme = localStorage.getItem('theme') || 'light';
        const sysTextSize = localStorage.getItem('textSize') || 'normal';
        const sysContrast = localStorage.getItem('contrast') || 'normal';
        const sysMotion = localStorage.getItem('motion') || 'normal';

        if (sysTheme === 'dark') document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-text-size', sysTextSize);
        document.documentElement.setAttribute('data-contrast', sysContrast);
        document.documentElement.setAttribute('data-reduce-motion', sysMotion);
    </script>

    <style>
        /* =========================================================
           REGLAS GLOBALES DE ACCESIBILIDAD Y MODO OSCURO MEJORADO
           ========================================================= */
        
        html[data-text-size="small"] { font-size: 14px !important; }
        html[data-text-size="normal"] { font-size: 16px !important; }
        html[data-text-size="large"] { font-size: 18.5px !important; }
        html[data-contrast="high"] { filter: contrast(1.15) saturate(1.20); }
        html[data-reduce-motion="true"] *, html[data-reduce-motion="true"] *::before, html[data-reduce-motion="true"] *::after { 
            animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; scroll-behavior: auto !important;
        }

        /* ---------------------------------------------------------
           AJUSTES DE COLOR INTELIGENTES (A PRUEBA DE BALAS)
           --------------------------------------------------------- */
        
        /* Fondos Generales */
        html.dark body, html.dark main { background-color: #0f172a !important; color: #f1f5f9 !important; }
        
        /* Tarjetas, Tablas y Cajas Blancas (Selectores Escapados) */
        html.dark .bg-white, html.dark .bg-slate-50, html.dark .bg-slate-50\/50 { 
            background-color: #1e293b !important; 
            border-color: #334155 !important; 
            color: #f8fafc !important; 
        }
        html.dark .bg-white\/90 { background-color: rgba(30, 41, 59, 0.9) !important; border-color: #334155 !important; color: #f8fafc !important; }
        
        /* Columnas Kanban y Fondos Secundarios */
        html.dark .bg-slate-100, html.dark .bg-slate-200, html.dark .bg-slate-200\/50 { 
            background-color: #0f172a !important; 
            border-color: #334155 !important; 
        }
        html.dark .bg-slate-300 { background-color: #334155 !important; color: #cbd5e1 !important; }

        /* Líneas divisorias de Tablas */
        html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color: #334155 !important; }
        html.dark .border-slate-100, html.dark .border-slate-200, html.dark .border-slate-300 { border-color: #334155 !important; }
        
        /* Textos Generales */
        html.dark .text-slate-800, html.dark .text-slate-700 { color: #f1f5f9 !important; }
        html.dark .text-slate-600, html.dark .text-slate-500 { color: #94a3b8 !important; }
        html.dark .text-slate-400 { color: #64748b !important; }
        
        /* Sombras y Topbar */
        html.dark .shadow-sm, html.dark .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5) !important; border: 1px solid #334155 !important; }
        html.dark header { background-color: #1e293b !important; border-bottom-color: #334155 !important; }
        
        /* Corrección de Tablas (Efecto Hover) */
        html.dark .hover\:bg-slate-50:hover, html.dark .hover\:bg-slate-50\/80:hover { background-color: #334155 !important; }

        /* Corrección de Inputs y Selects */
        html.dark input, html.dark select, html.dark textarea {
            background-color: #0f172a !important; border-color: #334155 !important; color: #f1f5f9 !important;
        }
        
        /* Tonos Translúcidos para Badges */
        html.dark .bg-amber-50, html.dark .bg-amber-100 { background-color: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; border-color: rgba(245, 158, 11, 0.3) !important; }
        html.dark .text-amber-700, html.dark .text-amber-600, html.dark .text-amber-500 { color: #fbbf24 !important; }
        html.dark .bg-blue-50, html.dark .bg-blue-100 { background-color: rgba(59, 130, 246, 0.15) !important; color: #60a5fa !important; border-color: rgba(59, 130, 246, 0.3) !important; }
        html.dark .text-blue-700, html.dark .text-blue-600, html.dark .text-blue-500 { color: #60a5fa !important; }
        html.dark .bg-green-50, html.dark .bg-green-100 { background-color: rgba(132, 204, 22, 0.15) !important; color: #a3e635 !important; border-color: rgba(132, 204, 22, 0.3) !important; }
        html.dark .text-green-700, html.dark .text-green-600, html.dark .text-green-500 { color: #a3e635 !important; }
        html.dark .bg-red-50, html.dark .bg-red-100 { background-color: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; border-color: rgba(239, 68, 68, 0.3) !important; }
        html.dark .text-red-700, html.dark .text-red-600, html.dark .text-red-500 { color: #f87171 !important; }

        html.dark .text-institucional { color: #93c5fd !important; } 
        html.dark .bg-institucional { background-color: #1e3a8a !important; border-color: #1e3a8a !important; }

        /* =========================================================
           ESTILOS UI PARA LEAFLET (MODO OSCURO NATIVO)
           ========================================================= */
        html.dark .leaflet-container { background: #0f172a !important; }
        html.dark .leaflet-bar a { background-color: #1e293b !important; color: #cbd5e1 !important; border-color: #334155 !important; }
        html.dark .leaflet-bar a:hover { background-color: #334155 !important; color: #f8fafc !important; }
        html.dark .leaflet-control-attribution { background-color: rgba(15, 23, 42, 0.8) !important; color: #64748b !important; }
        html.dark .leaflet-control-attribution a { color: #93c5fd !important; }
        html.dark .leaflet-popup-content-wrapper, html.dark .leaflet-popup-tip { background-color: #1e293b !important; color: #f1f5f9 !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5) !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans flex h-screen overflow-hidden selection:bg-accion selection:text-white transition-colors duration-300">

    <!-- Sidebar Modular -->
    <aside class="w-64 bg-sidebar text-white flex flex-col hidden md:flex z-50">
        <div class="h-16 flex items-center px-6 border-b border-white/10">
            <i class="ph-fill ph-map-pin-line text-2xl text-accion mr-2"></i>
            <span class="font-bold text-xl tracking-tight">MiMunicipio</span>
        </div>
        
        <div class="p-4 overflow-y-auto flex-grow">
            <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-3 px-2">Gestión Operativa</p>
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="ph ph-squares-four text-lg"></i> Dashboard
                </a>
                <a href="{{ route('admin.mapa') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.mapa') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="ph ph-map-trifold text-lg"></i> Mapa en vivo
                </a>
                <a href="{{ route('admin.reportes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.reportes.*') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="ph ph-kanban text-lg"></i> Tablero Reportes
                </a>
                
                <!-- SEGURIDAD: Visible para Super Admin y Coordinadores -->
                @if(auth()->user() && in_array(auth()->user()->rol, ['super_admin', 'coordinador']))
                    <a href="{{ route('admin.cuadrillas.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.cuadrillas.*') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="ph ph-truck text-lg"></i> Flotilla y Cuadrillas
                    </a>
                @endif
            </nav>

            <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mt-8 mb-3 px-2">Análisis y Sistema</p>
            <nav class="space-y-1">
                <!-- SEGURIDAD: Solo el Super Admin ve Departamentos -->
                @if(auth()->user() && auth()->user()->rol === 'super_admin')
                    <a href="{{ route('admin.areas.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.areas.*') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="ph ph-buildings text-lg"></i> Departamentos
                    </a>
                @endif
                
                <a href="{{ route('admin.metricas.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.metricas.*') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="ph ph-chart-line-up text-lg"></i> Métricas
                </a>

                <!-- SEGURIDAD: Solo el Super Admin gestiona Usuarios Administrativos -->
                @if(auth()->user() && auth()->user()->rol === 'super_admin')
                    <a href="{{ route('admin.usuarios.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.usuarios.*') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="ph ph-users text-lg"></i> Usuarios
                    </a>
                @endif

                <a href="{{ route('admin.configuracion.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.configuracion.*') ? 'bg-institucional text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="ph ph-gear text-lg"></i> Configuración
                </a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-2">
                <div class="w-8 h-8 rounded-full bg-institucional border border-white/20 flex items-center justify-center">
                    <i class="ph-fill ph-user text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name ?? 'Administrador' }}</p>
                    <p class="text-[10px] text-slate-400 truncate uppercase font-bold tracking-wider">
                        {{ auth()->user() && auth()->user()->rol === 'super_admin' ? 'ADMIN' : (auth()->user()->rol ?? 'OPERADOR') }}
                    </p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white shadow-sm border-b border-slate-200 flex items-center justify-between px-6 z-10 transition-colors duration-300">
            <h1 class="text-xl font-semibold text-slate-800">@yield('header_title', 'Dashboard General')</h1>
            <div class="flex items-center gap-4">
                
                <div class="relative" id="notificaciones-dropdown-container">
                    <button onclick="toggleNotificaciones()" class="relative p-2 text-slate-400 hover:text-institucional transition rounded-full hover:bg-slate-50 focus:outline-none">
                        <i class="ph ph-bell text-xl"></i>
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-[9px] font-bold border-2 border-white rounded-full flex items-center justify-center">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <div id="notificaciones-menu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 z-[500] overflow-hidden origin-top-right">
                        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <h3 class="font-bold text-institucional text-sm">Notificaciones</h3>
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('admin.notificaciones.limpiar') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-accion hover:text-[#65a30d] transition">Marcar todas leídas</button>
                                </form>
                            @endif
                        </div>
                        
                        <div class="max-h-[320px] overflow-y-auto">
                            @if(auth()->check() && auth()->user()->unreadNotifications && auth()->user()->unreadNotifications->count() > 0)
                                @foreach(auth()->user()->unreadNotifications as $notificacion)
                                    <a href="{{ route('admin.notificaciones.leer', $notificacion->id) }}" class="block p-4 border-b border-slate-50 hover:bg-slate-50 transition group">
                                        <div class="flex gap-3 items-start">
                                            <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center {{ $notificacion->data['color'] ?? 'bg-slate-100 text-slate-500' }}">
                                                <i class="ph-fill {{ $notificacion->data['icono'] ?? 'ph-bell' }} text-lg"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-700 mb-0.5">{{ $notificacion->data['titulo'] }}</p>
                                                <p class="text-[11px] text-slate-500 leading-tight mb-1">{{ $notificacion->data['mensaje'] }}</p>
                                                <p class="text-[9px] text-slate-400 font-medium">{{ $notificacion->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @else
                                <div class="p-6 text-center">
                                    <i class="ph-light ph-bell-z text-3xl text-slate-300 mb-2"></i>
                                    <p class="text-xs text-slate-500 font-medium">No tienes notificaciones nuevas.</p>
                                </div>
                            @endif
                        </div>
                        <div class="p-3 border-t border-slate-100 bg-slate-50 text-center">
                            <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Panel Administrativo</span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800 transition">Cerrar Sesión</button>
                </form>
            </div>
        </header>

        <!-- Área de Trabajo Inyectable -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-6 transition-colors duration-300">
            @yield('content')
        </main>
    </div>

    <!-- Scripts Base -->
    <script>
        function toggleNotificaciones() {
            document.getElementById('notificaciones-menu').classList.toggle('hidden');
        }
        document.addEventListener('click', function(event) {
            const container = document.getElementById('notificaciones-dropdown-container');
            const menu = document.getElementById('notificaciones-menu');
            if (container && !container.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>