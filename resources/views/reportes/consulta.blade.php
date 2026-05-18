@extends('layouts.citizen')

@section('title', 'Consultar Reporte - MiMunicipio')

@section('content')
<div class="w-full px-4 sm:px-0 animate-fade-in relative">
    
    <!-- Toast Flotante de Copiado -->
    <div id="toast-copiado" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-2 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none z-[5000]">
        <i class="ph-fill ph-check-circle text-green-400 text-xl"></i>
        <span class="font-bold text-sm">¡Folio copiado al portapapeles!</span>
    </div>

    <!-- Botón Volver (UX) -->
    <div class="mb-6">
        <!-- Añadimos el id="btn-volver" para que el Javascript pueda manipular la ruta -->
        <a id="btn-volver" href="{{ route('reportes.create', ['municipio' => 'mexticacan']) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-institucional font-bold text-sm bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-sm transition active:scale-95">
            <i class="ph-bold ph-arrow-left"></i> Volver a Nuevo Reporte
        </a>
    </div>

    <!-- Alerta de Reporte Creado Exitosamente -->
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-8 rounded-3xl mb-8 text-center shadow-md animate-scale-in">
            <div class="w-20 h-20 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-green-500/30">
                <i class="ph-bold ph-check text-4xl"></i>
            </div>
            <h2 class="text-2xl font-black mb-2 text-[#1A365D]">¡Recibimos tu reporte!</h2>
            <p class="text-slate-600 mb-6 font-medium">Guarda este folio oficial. Lo usarás para revisar si ya lo reparamos.</p>
            
            <!-- CONTENEDOR DEL FOLIO CON BOTÓN DE COPIAR -->
            <div class="inline-flex flex-col sm:flex-row items-center gap-4 bg-white border-2 border-dashed border-green-400 pl-6 pr-2 py-2 rounded-2xl shadow-inner group mb-6">
                <span id="folio-texto" class="font-mono text-3xl font-black text-institucional tracking-widest select-all">{{ session('folio_generado') }}</span>
                <div class="hidden sm:block w-2"></div>
                <button type="button" onclick="copiarAlPortapapeles()" class="w-full sm:w-12 h-12 flex items-center justify-center bg-green-50 text-green-600 hover:bg-green-500 hover:text-white rounded-xl transition-all active:scale-95 group-hover:shadow-md cursor-pointer" title="Copiar Folio">
                    <i class="ph-bold ph-copy text-2xl hidden sm:block"></i>
                    <span class="sm:hidden font-bold flex items-center gap-2"><i class="ph-bold ph-copy text-lg"></i> Copiar Folio</span>
                </button>
            </div>

            <!-- BOTÓN DE CONSULTA AUTOMÁTICA -->
            <div>
                <button type="button" onclick="buscarMiReporteMagico('{{ session('folio_generado') }}')" class="inline-flex items-center gap-2 bg-institucional hover:bg-blue-900 text-white font-bold py-3.5 px-8 rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95 w-full sm:w-auto justify-center">
                    <i class="ph-bold ph-magnifying-glass"></i> Consultar mi reporte ahora
                </button>
            </div>
        </div>
    @endif

    <!-- Alerta de Búsqueda Fallida -->
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-600 p-5 rounded-2xl mb-8 flex items-center gap-4 shadow-sm animate-scale-in">
            <div class="bg-red-100 p-2 rounded-full"><i class="ph-fill ph-warning-circle text-2xl"></i></div>
            <div>
                <p class="font-bold text-sm">Folio no encontrado</p>
                <p class="text-xs font-medium opacity-80">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Panel de Búsqueda -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-slate-200 mb-8">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-blue-50 text-institucional rounded-full flex items-center justify-center"><i class="ph-bold ph-magnifying-glass text-2xl"></i></div>
            <div>
                <h1 class="text-xl font-black text-institucional">Rastrear Folio</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Escribe el número de tu reporte o selecciona uno reciente.</p>
            </div>
        </div>

        <form id="form-busqueda" action="{{ route('reportes.buscar') }}" method="POST" class="mt-6 flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="relative flex-grow">
                <input type="text" id="input-folio" name="folio" placeholder="Ej. MX-2026-00001" required autocomplete="off"
                       class="w-full rounded-2xl border-slate-300 border p-4 pl-12 focus:ring-2 focus:ring-accion outline-none font-mono uppercase text-slate-700 font-bold bg-slate-50 transition">
                <i class="ph-bold ph-hash absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400 text-lg"></i>
            </div>
            <button type="submit" id="btn-submit-buscar" class="bg-institucional hover:bg-blue-900 active:scale-95 text-white font-black py-4 px-8 rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                Buscar
            </button>
        </form>

        <!-- HISTORIAL LOCALSTORAGE (Auto-guardado de folios) -->
        <div id="historial-folios" class="hidden mt-5 pt-4 border-t border-slate-100 flex-col sm:flex-row items-start sm:items-center gap-3 animate-fade-in">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                <i class="ph-fill ph-clock-counter-clockwise text-sm"></i> Reportes en este dispositivo:
            </span>
            <div id="lista-folios" class="flex flex-wrap gap-2">
                <!-- Se llena mediante Javascript -->
            </div>
        </div>
    </div>

    <!-- Resultados -->
    @if(isset($reporte))
        <div class="bg-white rounded-3xl shadow-md border border-slate-200 overflow-hidden animate-slide-up" id="seccion-resultados">
            <div class="bg-slate-50 p-6 border-b border-slate-200 flex justify-between items-center">
                <div>
                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Resultado de Búsqueda</p>
                    <p class="font-mono text-2xl font-black text-institucional tracking-tight">{{ $reporte->folio }}</p>
                </div>
                
                <span class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider shadow-sm border
                    @if($reporte->estado == 'Pendiente') bg-amber-50 text-amber-700 border-amber-200
                    @elseif($reporte->estado == 'En progreso') bg-blue-50 text-blue-700 border-blue-200
                    @else bg-green-50 text-green-700 border-green-200 @endif">
                    <i class="ph-bold @if($reporte->estado == 'Pendiente') ph-clock @elseif($reporte->estado == 'En progreso') ph-gear @else ph-check @endif mr-1"></i>
                    {{ $reporte->estado }}
                </span>
            </div>
            
            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 mb-8">
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Problema Reportado</p>
                        <p class="font-bold text-slate-800 text-lg flex items-center gap-2">
                            <i class="ph-fill ph-tag text-accion"></i> {{ $reporte->categoria }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Fecha del Aviso</p>
                        <p class="font-bold text-slate-700 flex items-center gap-2">
                            <i class="ph-fill ph-calendar-blank text-slate-400"></i> {{ $reporte->created_at->format('d M, Y') }}
                        </p>
                    </div>
                    <div class="sm:col-span-2 bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-inner">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-2">Tu Descripción</p>
                        <p class="text-slate-600 text-sm font-medium italic leading-relaxed">"{{ $reporte->descripcion }}"</p>
                    </div>
                </div>
                
                <!-- Timeline Operativo -->
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-center text-xs font-black text-institucional uppercase tracking-widest mb-6">Línea de Tiempo Operativa</p>
                    
                    <div class="relative px-4 sm:px-10">
                        <div class="absolute left-0 top-1/2 w-full h-1.5 bg-slate-100 -translate-y-1/2 rounded-full"></div>
                        <div class="absolute left-0 top-1/2 h-1.5 bg-accion -translate-y-1/2 rounded-full transition-all duration-700 ease-out shadow-[0_0_10px_rgba(132,204,22,0.5)]" 
                             style="width: {{ $reporte->estado == 'Pendiente' ? '15%' : ($reporte->estado == 'En progreso' ? '50%' : '100%') }}"></div>
                        
                        <div class="relative flex justify-between">
                            <!-- Paso 1 -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-accion text-white flex items-center justify-center border-4 border-white shadow-md z-10"><i class="ph-bold ph-check text-sm"></i></div>
                                <span class="text-[10px] sm:text-xs font-bold text-slate-700 mt-2">Recibido</span>
                            </div>
                            <!-- Paso 2 -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full {{ $reporte->estado == 'En progreso' || $reporte->estado == 'Resuelto' ? 'bg-accion text-white shadow-[0_0_15px_rgba(132,204,22,0.4)]' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center border-4 border-white shadow-sm z-10 transition-colors duration-500 delay-300">
                                    <i class="ph-bold ph-gear text-sm {{ $reporte->estado == 'En progreso' ? 'animate-spin-slow' : '' }}"></i>
                                </div>
                                <span class="text-[10px] sm:text-xs font-bold {{ $reporte->estado == 'En progreso' || $reporte->estado == 'Resuelto' ? 'text-slate-700' : 'text-slate-400' }} mt-2">En proceso</span>
                            </div>
                            <!-- Paso 3 -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full {{ $reporte->estado == 'Resuelto' ? 'bg-accion text-white shadow-[0_0_15px_rgba(132,204,22,0.4)]' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center border-4 border-white shadow-sm z-10 transition-colors duration-500 delay-500">
                                    <i class="ph-bold ph-check-circle text-sm"></i>
                                </div>
                                <span class="text-[10px] sm:text-xs font-bold {{ $reporte->estado == 'Resuelto' ? 'text-slate-700' : 'text-slate-400' }} mt-2">Resuelto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 0. MEMORIA DE NAVEGACIÓN (Arreglo del Botón Volver y Logo) ---
        let referrer = document.referrer;
        
        // Si el ciudadano viene de la página de un municipio (/m/teocaltiche), guardamos esa URL
        if (referrer && referrer.includes('/m/')) {
            localStorage.setItem('ultimo_municipio_url', referrer.split('?')[0]);
        }
        
        let urlGuardada = localStorage.getItem('ultimo_municipio_url');
        if (urlGuardada) {
            // Actualizamos la ruta del botón de Volver
            let btnVolver = document.getElementById('btn-volver');
            if (btnVolver) btnVolver.href = urlGuardada;
            
            // Actualizamos también el Logo del Navbar Superior para que no te regrese a Mexticacán
            document.querySelectorAll('nav a').forEach(link => {
                if (link.href.includes('/m/mexticacan')) {
                    link.href = urlGuardada;
                }
            });
        }


        // 1. Guardar Folio Automáticamente si la sesión lo mandó (cuando acaban de reportar)
        @if(session('folio_generado'))
            guardarFolioLocal("{{ session('folio_generado') }}");
        @endif

        // 2. Si es el resultado de una búsqueda exitosa, guardarlo también en caché
        @if(isset($reporte))
            guardarFolioLocal("{{ $reporte->folio }}");
            
            // Hacer scroll suave hacia los resultados automáticamente
            setTimeout(() => {
                const resultados = document.getElementById('seccion-resultados');
                if(resultados) resultados.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        @endif

        // 3. Renderizar el historial de reportes debajo de la barra de búsqueda
        mostrarHistorial();
    });

    // ----------------------------------------------------
    // FUNCIÓN MAGICA: Buscar reporte inmediatamente
    // ----------------------------------------------------
    function buscarMiReporteMagico(folioGenerado) {
        // Llenar el input
        document.getElementById('input-folio').value = folioGenerado;
        
        // Cambiar el botón visualmente a estado de carga
        const btn = document.getElementById('btn-submit-buscar');
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap animate-spin"></i> Buscando...';
        btn.classList.add('opacity-80', 'pointer-events-none');
        
        // Enviar el formulario
        document.getElementById('form-busqueda').submit();
    }

    // ----------------------------------------------------
    // Copiar al portapapeles
    // ----------------------------------------------------
    function copiarAlPortapapeles() {
        const folioText = document.getElementById('folio-texto').innerText;
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(folioText).then(mostrarToastCopiado);
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = folioText;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                mostrarToastCopiado();
            } catch (err) {
                console.error('No se pudo copiar el folio', err);
            }
            document.body.removeChild(textArea);
        }
    }

    function mostrarToastCopiado() {
        const toast = document.getElementById('toast-copiado');
        toast.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none');
        }, 3000);
    }

    // ----------------------------------------------------
    // LOGICA DE LOCALSTORAGE (Historial de Reportes)
    // ----------------------------------------------------
    const CLAVE_LOCALSTORAGE = 'mimunicipio_folios';

    function guardarFolioLocal(nuevoFolio) {
        let folios = JSON.parse(localStorage.getItem(CLAVE_LOCALSTORAGE) || '[]');
        
        // Evitar duplicados y mover el actual al principio
        folios = folios.filter(f => f !== nuevoFolio);
        folios.unshift(nuevoFolio);
        
        // Mantener solo los últimos 4 folios en memoria
        if (folios.length > 4) {
            folios.pop();
        }
        
        localStorage.setItem(CLAVE_LOCALSTORAGE, JSON.stringify(folios));
    }

    function mostrarHistorial() {
        let folios = JSON.parse(localStorage.getItem(CLAVE_LOCALSTORAGE) || '[]');
        const contenedor = document.getElementById('historial-folios');
        const lista = document.getElementById('lista-folios');

        if (folios.length > 0) {
            contenedor.classList.remove('hidden');
            contenedor.classList.add('flex');
            lista.innerHTML = ''; // Limpiar lista
            
            // Generar los botones dinámicamente
            folios.forEach(folio => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'bg-blue-50 hover:bg-institucional hover:text-white text-institucional text-sm font-bold font-mono px-4 py-2 rounded-xl border border-blue-200 transition-all active:scale-95 shadow-sm flex items-center gap-1.5';
                btn.innerHTML = `<i class="ph-bold ph-clock text-base"></i> ${folio}`;
                
                // Al hacer clic, llena el input y envía el formulario al instante
                btn.onclick = function() {
                    document.getElementById('input-folio').value = folio;
                    
                    // Efecto de carga
                    const btnSubmit = document.getElementById('btn-submit-buscar');
                    btnSubmit.innerHTML = '<i class="ph-bold ph-spinner-gap animate-spin"></i> Buscando...';
                    btnSubmit.classList.add('opacity-80', 'pointer-events-none');

                    document.getElementById('form-busqueda').submit();
                };
                
                lista.appendChild(btn);
            });
        }
    }
</script>