<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MiMunicipio</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4 selection:bg-accion selection:text-white">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 p-8 sm:p-10">
        <!-- Logo -->
        <div class="flex items-center justify-center gap-2 mb-8">
            <div class="relative flex items-center justify-center w-10 h-10">
                <i class="ph-fill ph-map-pin text-4xl text-accion absolute mt-1"></i>
                <i class="ph-bold ph-chat-circle-dots text-white absolute text-xs mb-1"></i>
            </div>
            <span class="font-bold text-3xl tracking-tight text-institucional">
                Mi<span class="text-accion">Municipio</span>
            </span>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-xl font-bold text-slate-800">Acceso Administrativo</h1>
            <p class="text-sm text-slate-500 mt-1">Ingresa tus credenciales para continuar.</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-medium border border-red-100 flex items-center gap-2">
                <i class="ph-fill ph-warning-circle text-lg"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Correo Institucional</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="ph ph-envelope-simple text-slate-400 text-lg"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-accion focus:border-transparent outline-none transition" 
                           placeholder="admin@mimunicipio.com">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="ph ph-lock-key text-slate-400 text-lg"></i>
                    </div>
                    <input type="password" name="password" required
                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-accion focus:border-transparent outline-none transition" 
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-accion focus:ring-accion w-4 h-4">
                    <span class="text-sm text-slate-600 font-medium">Recordarme</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-institucional hover:bg-blue-900 text-white font-bold py-3.5 rounded-xl shadow-md transition-colors mt-4">
                Iniciar Sesión
            </button>
        </form>
        
        <div class="mt-8 text-center text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} Sistema Integral de Gestión Ciudadana.</p>
            <p>Uso exclusivo para personal autorizado.</p>
        </div>
    </div>

</body>
</html>