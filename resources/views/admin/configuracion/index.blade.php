@extends('layouts.admin')

@section('title', 'Configuración - MiMunicipio')
@section('header_title', 'Ajustes del Sistema')

@section('content')

@if (session('success'))
    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-3 border border-green-200 shadow-sm">
        <i class="ph-fill ph-check-circle text-xl"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-200 shadow-sm">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold text-sm">Hay errores en tu solicitud:</span>
        </div>
        <ul class="text-sm list-disc list-inside px-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- Columna Izquierda: Datos del Municipio (Tenant) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                <i class="ph-fill ph-buildings text-accion"></i> Información Institucional
            </h2>
            <p class="text-xs text-slate-500 mt-1">Datos globales de la cuenta municipal.</p>
        </div>
        
        <div class="p-6 space-y-4">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Municipio Registrado</p>
                <p class="font-bold text-lg text-slate-800">{{ $municipio->nombre }}</p>
            </div>
            
            <hr class="border-slate-100">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-xs font-bold text-slate-500 mb-1">Total Reportes</p>
                    <p class="text-2xl font-bold text-institucional">{{ $totalReportes }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-xs font-bold text-slate-500 mb-1">Cuentas Activas</p>
                    <p class="text-2xl font-bold text-institucional">{{ $totalUsuarios }}</p>
                </div>
            </div>
            
            <div class="bg-blue-50 text-blue-700 p-4 rounded-xl mt-4 flex items-start gap-3 border border-blue-100">
                <i class="ph-fill ph-info mt-0.5"></i>
                <p class="text-xs font-medium">Los datos institucionales solo pueden ser modificados por el equipo de soporte técnico de MiMunicipio para garantizar la integridad de la base de datos.</p>
            </div>
        </div>
    </div>

    <div class="space-y-8 lg:col-span-2">
        <!-- Columna Derecha Superior: Formulario de Perfil -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                    <i class="ph-bold ph-shield-check text-accion"></i> Seguridad de tu Cuenta
                </h2>
                <p class="text-xs text-slate-500 mt-1">Actualiza tus datos de acceso al panel administrativo.</p>
            </div>

            <div class="p-6">
                <form action="{{ route('admin.configuracion.perfil') }}" method="POST" class="space-y-5 max-w-lg">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Nombre Completo</label>
                        <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required class="w-full rounded-xl border-slate-300 border p-3 text-sm focus:ring-accion focus:border-transparent outline-none bg-slate-50 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Correo Institucional</label>
                        <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required class="w-full rounded-xl border-slate-300 border p-3 text-sm focus:ring-accion focus:border-transparent outline-none bg-slate-50 transition">
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-sm font-bold text-slate-800 mb-4">Cambio de Contraseña <span class="text-xs font-normal text-slate-400">(Opcional)</span></p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nueva Contraseña</label>
                                <input type="password" name="password" minlength="6" placeholder="Dejar en blanco para no cambiar" class="w-full rounded-xl border-slate-300 border p-3 text-sm focus:ring-accion focus:border-transparent outline-none bg-slate-50 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" minlength="6" placeholder="Repite la contraseña" class="w-full rounded-xl border-slate-300 border p-3 text-sm focus:ring-accion focus:border-transparent outline-none bg-slate-50 transition">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-institucional hover:bg-blue-900 text-white font-bold py-3.5 px-8 rounded-xl shadow-md transition-colors flex items-center gap-2">
                            <i class="ph-bold ph-floppy-disk"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Columna Derecha Inferior: PREFERENCIAS DE ACCESIBILIDAD (NUEVO) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-bold text-institucional flex items-center gap-2">
                    <i class="ph-bold ph-wheelchair text-accion"></i> Accesibilidad y Experiencia (UX)
                </h2>
                <p class="text-xs text-slate-500 mt-1">Personaliza el sistema para adaptarlo a tus necesidades visuales. Los cambios se guardan en tu dispositivo.</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- Tema Visual -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">Tema de Interfaz</label>
                        <div class="flex bg-slate-100 p-1 rounded-xl w-full border border-slate-200 transition-colors">
                            <button type="button" onclick="setPref('theme', 'light')" id="btn-theme-light" class="flex-1 py-2.5 text-sm font-bold rounded-lg flex items-center justify-center gap-2 transition-all text-slate-500 hover:text-institucional border border-transparent">
                                <i class="ph-fill ph-sun text-amber-500"></i> Claro
                            </button>
                            <button type="button" onclick="setPref('theme', 'dark')" id="btn-theme-dark" class="flex-1 py-2.5 text-sm font-bold rounded-lg flex items-center justify-center gap-2 transition-all text-slate-500 hover:text-institucional border border-transparent">
                                <i class="ph-fill ph-moon text-indigo-400"></i> Oscuro
                            </button>
                        </div>
                    </div>

                    <!-- Tamaño de Texto -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">Tamaño de Tipografía</label>
                        <div class="flex bg-slate-100 p-1 rounded-xl w-full border border-slate-200 transition-colors">
                            <button type="button" onclick="setPref('textSize', 'small')" id="btn-text-small" class="flex-1 py-2 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-institucional border border-transparent">A-</button>
                            <button type="button" onclick="setPref('textSize', 'normal')" id="btn-text-normal" class="flex-1 py-2 text-sm font-bold rounded-lg transition-all text-slate-500 hover:text-institucional border border-transparent">Aa</button>
                            <button type="button" onclick="setPref('textSize', 'large')" id="btn-text-large" class="flex-1 py-2 text-base font-bold rounded-lg transition-all text-slate-500 hover:text-institucional border border-transparent">A+</button>
                        </div>
                    </div>

                    <!-- Alto Contraste -->
                    <div class="flex items-center justify-between p-4 border border-slate-200 rounded-xl bg-slate-50 transition-colors">
                        <div>
                            <p class="text-sm font-bold text-slate-700 flex items-center gap-2"><i class="ph-fill ph-contrast text-accion"></i> Alto Contraste</p>
                            <p class="text-[10px] text-slate-500 mt-0.5 leading-tight pr-4">Mejora la visibilidad saturando los colores.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" id="toggle-contrast" onchange="togglePref('contrast', 'high', 'normal', this.checked)" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accion"></div>
                        </label>
                    </div>

                    <!-- Reducir Animaciones -->
                    <div class="flex items-center justify-between p-4 border border-slate-200 rounded-xl bg-slate-50 transition-colors">
                        <div>
                            <p class="text-sm font-bold text-slate-700 flex items-center gap-2"><i class="ph-fill ph-person-simple-walk text-blue-500"></i> Reducir Movimiento</p>
                            <p class="text-[10px] text-slate-500 mt-0.5 leading-tight pr-4">Desactiva animaciones de la interfaz.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" id="toggle-motion" onchange="togglePref('motion', 'true', 'normal', this.checked)" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accion"></div>
                        </label>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@stack('scripts')
<script>
    // =========================================================
    // LÓGICA DEL MOTOR DE ACCESIBILIDAD (CLIENT-SIDE)
    // =========================================================

    document.addEventListener('DOMContentLoaded', () => {
        const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        const textSize = document.documentElement.getAttribute('data-text-size') || 'normal';
        const contrast = document.documentElement.getAttribute('data-contrast') || 'normal';
        const motion = document.documentElement.getAttribute('data-reduce-motion') || 'normal';

        actualizarBotonesSelect('theme', theme);
        actualizarBotonesSelect('text', textSize);
        
        document.getElementById('toggle-contrast').checked = (contrast === 'high');
        document.getElementById('toggle-motion').checked = (motion === 'true');
    });

    function setPref(key, value) {
        localStorage.setItem(key, value); 
        
        if (key === 'theme') {
            if (value === 'dark') document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
            actualizarBotonesSelect('theme', value);
        } else if (key === 'textSize') {
            document.documentElement.setAttribute('data-text-size', value);
            actualizarBotonesSelect('text', value);
        }
    }

    function togglePref(key, onValue, offValue, isChecked) {
        const value = isChecked ? onValue : offValue;
        localStorage.setItem(key, value);
        const htmlAttribute = (key === 'motion') ? 'reduce-motion' : key;
        document.documentElement.setAttribute('data-' + htmlAttribute, value);
    }

    // Utilidad mejorada para cambiar estilos de botones activos
    function actualizarBotonesSelect(grupo, valorActivo) {
        const activeClass = 'bg-white dark:bg-[#1e293b] text-institucional dark:text-blue-300 shadow-sm border-slate-200/50 dark:border-slate-600'.split(' ');
        const inactiveClass = 'text-slate-500 hover:text-institucional border-transparent'.split(' ');

        document.querySelectorAll(`[id^="btn-${grupo}-"]`).forEach(btn => {
            btn.classList.remove(...activeClass);
            btn.classList.add(...inactiveClass);
        });

        const btnActivo = document.getElementById(`btn-${grupo}-${valorActivo}`);
        if(btnActivo) {
            btnActivo.classList.remove(...inactiveClass);
            btnActivo.classList.add(...activeClass);
        }
    }
</script>