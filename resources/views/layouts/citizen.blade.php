<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'MiMunicipio - Reporte Ciudadano')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        institucional: '#1A365D',
                        accion: '#84CC16',
                    }
                }
            }
        }
    </script>
    <!-- Animaciones CSS Personalizadas -->
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        @keyframes scaleIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .animate-slide-up { animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-scale-in { animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    </style>
    <!-- Enlaces limpios para Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased flex flex-col min-h-screen pb-safe">

    <!-- Navbar Minimalista -->
    <nav class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('reportes.create', ['municipio' => request()->route('municipio') ?? 'mexticacan']) }}" class="flex items-center gap-2 active:opacity-70 transition">
                    <i class="ph-fill ph-map-pin-line text-3xl text-institucional"></i>
                    <span class="font-bold text-xl tracking-tight text-institucional">
                        Mi<span class="text-accion">Municipio</span>
                    </span>
                </a>
                
                <!-- Solo dejamos el botón de Consultar, en formato "Pill" -->
                <a href="{{ route('reportes.consulta', ['m' => request()->route('municipio') ?? 'nochistlan']) }}" class="text-xs font-bold uppercase tracking-wider bg-slate-100 text-institucional hover:bg-slate-200 active:scale-95 px-4 py-2.5 rounded-full transition-all flex items-center gap-2">
                    <i class="ph-bold ph-magnifying-glass text-base"></i> Consultar
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="flex-grow w-full max-w-3xl mx-auto py-6">
        @yield('content')
    </main>

    <!-- Footer Oculto en móviles muy pequeños para dar prioridad a la App -->
    <footer class="bg-institucional text-white py-6 mt-auto hidden sm:block">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <p class="text-sm opacity-80 font-medium">Cuida tu zona, MiMunicipio funciona.</p>
            <p class="text-xs opacity-60 mt-1">&copy; {{ date('Y') }} Sistema de Gestión Ciudadana.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>