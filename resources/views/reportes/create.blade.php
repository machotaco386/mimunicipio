@extends('layouts.citizen')

@section('title', 'Reportes - ' . $municipioSeleccionado->nombre)

@section('content')
<!-- Motor WebGL MapLibre CSS -->
<link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet" />

<!-- Estilos para invertir el color del texto satelital y el Punto Azul -->
<style>
    .etiquetas-blancas {
        -webkit-filter: invert(100%) brightness(2.5) contrast(1.5) drop-shadow(0 0 2px black) drop-shadow(0 0 3px black) !important;
        filter: invert(100%) brightness(2.5) contrast(1.5) drop-shadow(0 0 2px black) drop-shadow(0 0 3px black) !important;
    }

    /* Punto Azul Palpitante (Geolocalización) */
    .maplibregl-user-location-dot {
        background-color: #3b82f6 !important; /* Azul Tailwind */
        width: 18px !important;
        height: 18px !important;
        border: 3px solid white !important;
        box-shadow: 0 0 10px rgba(0,0,0,0.4) !important;
        position: relative;
    }
    .maplibregl-user-location-dot::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: #3b82f6;
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        z-index: -1;
        top: 0;
        left: 0;
    }
    @keyframes pulse-ring {
        0% { transform: scale(1); opacity: 0.6; }
        100% { transform: scale(4); opacity: 0; }
    }
</style>

<div class="relative w-full px-4 sm:px-0 animate-fade-in">
    
    <!-- Alerta Flotante para Rechazo de Ubicación -->
    <div id="alerta-geocerca" class="hidden fixed top-20 left-1/2 transform -translate-x-1/2 z-[2000] bg-red-500 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 transition-all duration-300">
        <i class="ph-bold ph-warning-octagon text-xl"></i>
        <span id="texto-alerta" class="font-bold text-sm">Ese punto no pertenece a nuestro municipio.</span>
    </div>

    <div class="text-center mb-6">
        <h1 class="text-2xl font-black text-institucional mb-1">Reportes en {{ $municipioSeleccionado->nombre }}</h1>
        <p class="text-sm text-slate-500 font-medium">Atención ciudadana rápida y directa.</p>
    </div>

    <!-- BLOQUE DE ERRORES DE LARAVEL -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 p-5 mb-6 rounded-2xl shadow-sm animate-scale-in">
            <div class="flex items-start gap-3">
                <i class="ph-fill ph-warning-circle text-red-500 text-2xl"></i>
                <div>
                    <h3 class="text-sm font-bold text-red-800 mb-1">No se pudo enviar el reporte:</h3>
                    <ul class="text-sm text-red-600 list-disc list-inside font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form id="form-reporte" action="{{ route('reportes.store') }}" method="POST" enctype="multipart/form-data" onsubmit="validarYEnviar(event)" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative pb-24">
        @csrf
        
        <!-- PASO 1: MAPA INTERACTIVO -->
        <div class="p-5 border-b border-slate-100 relative">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-institucional text-white flex items-center justify-center font-black shadow-sm">1</div>
                <h2 class="text-lg font-bold text-slate-800">Ubicación exacta</h2>
            </div>
            
            <!-- CONTENEDOR DEL MAPA -->
            <div id="map-container" class="relative w-full h-64 sm:h-80 rounded-2xl border-2 border-slate-200 z-10 block transition-all duration-300 overflow-hidden shadow-inner bg-slate-100">
                
                <!-- El Canvas WebGL de MapLibre -->
                <div id="map" class="w-full h-full z-10"></div>

                <!-- CONTROL DE CAPAS Y VISTAS -->
                <div id="controles-capas-mapa" class="absolute top-4 left-4 z-[1000]">
                    <!-- Botón unificado -->
                    <button id="btn-capas-crear" type="button" onclick="toggleMenuCapas()" class="bg-white/90 backdrop-blur-sm px-3 py-2 rounded-xl shadow-md border border-slate-200 text-sm font-bold text-slate-700 hover:text-institucional transition flex items-center gap-2">
                        <i class="ph-bold ph-stack text-lg text-accion"></i> Capas y Vistas
                    </button>
                    
                    <!-- Menú Dropdown (Solo Desktop 'sm') -->
                    <div id="menu-capas-crear" class="hidden sm:absolute sm:top-full sm:left-0 sm:mt-2 bg-white/95 backdrop-blur-md rounded-xl shadow-xl border border-slate-200 p-2 w-56 flex-col gap-1 z-[1000]">
                        <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Estilo Base</div>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                            <input type="radio" name="capa_crear" value="calles" checked onchange="cambiarCapaCrear('calles')" class="text-accion focus:ring-accion w-4 h-4">
                            <span class="text-sm font-bold text-slate-700"><i class="ph-fill ph-map-trifold text-blue-500"></i> Mapa Calles</span>
                        </label>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                            <input type="radio" name="capa_crear" value="satelite" onchange="cambiarCapaCrear('satelite')" class="text-accion focus:ring-accion w-4 h-4">
                            <span class="text-sm font-medium text-slate-700"><i class="ph-fill ph-globe-hemisphere-west text-green-600"></i> Satélite Alta Res</span>
                        </label>

                        <hr class="border-slate-100 my-1">
                        
                        <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Perspectiva</div>
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                            <input type="radio" name="vista_crear" value="2d" checked onchange="cambiarVista3D(false)" class="text-accion focus:ring-accion w-4 h-4">
                            <span class="text-sm font-bold text-slate-700"><i class="ph-fill ph-crosshair text-slate-500"></i> Plana (2D)</span>
                        </label>
                        <label id="wrapper-3d-desktop" class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                            <input type="radio" id="input-3d-desktop" name="vista_crear" value="3d" onchange="cambiarVista3D(true)" class="text-accion focus:ring-accion w-4 h-4">
                            <span class="text-sm font-medium text-slate-700 flex items-center gap-1">
                                <i class="ph-fill ph-mountains text-amber-600"></i> Relieve (3D)
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Botón Fullscreen -->
                <button type="button" onclick="toggleFullscreenMap()" class="absolute bottom-4 right-4 z-[1000] bg-white/90 backdrop-blur-sm text-institucional p-3 rounded-xl shadow-lg border border-slate-200 hover:bg-slate-50 active:scale-95 transition-all focus:outline-none">
                    <i id="fs-icon" class="ph-bold ph-arrows-out text-xl"></i>
                </button>

                <!-- Feedback de validación satelital -->
                <div id="indicador-validacion" class="hidden absolute top-4 right-4 z-[1000] bg-white/95 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg border border-slate-200 flex items-center gap-2 transition-all">
                    <i class="ph-bold ph-spinner-gap animate-spin text-institucional"></i>
                    <span class="text-xs font-bold text-slate-700">Validando...</span>
                </div>
            </div>
            
            <p class="text-xs text-slate-500 mt-3 font-medium flex items-center gap-1.5 bg-slate-50 p-3 rounded-xl border border-slate-100 transition-all" id="instrucciones-mapa">
                <i class="ph-fill ph-hand-pointing text-lg text-institucional"></i> Toca sobre el mapa para indicar dónde está el problema.
            </p>

            <!-- CAJA DE DIRECCIÓN -->
            <div id="box-direccion" class="hidden mt-3 bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm transition-all animate-scale-in">
                <div class="w-8 h-8 rounded-full bg-green-200 text-green-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="ph-bold ph-map-pin text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-0.5">Ubicación Registrada</p>
                    <p id="texto-direccion" class="text-sm font-bold text-green-800 leading-tight">Buscando...</p>
                </div>
            </div>
            
            <input type="hidden" name="latitud" id="latitud">
            <input type="hidden" name="longitud" id="longitud">
            <input type="hidden" name="direccion_texto" id="direccion_texto">
            
            <!-- SELECT OCULTO: Asegura que el reporte se guarde en la BD del municipio correcto -->
            <select name="municipio_id" id="select-municipio" class="hidden">
                @foreach($municipios as $mun)
                    <option value="{{ $mun->id }}" {{ $mun->id == $municipioSeleccionado->id ? 'selected' : '' }}>{{ $mun->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- PASO 2: DETALLES -->
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-full bg-institucional text-white flex items-center justify-center font-black shadow-sm">2</div>
                <h2 class="text-lg font-bold text-slate-800">¿Qué está pasando?</h2>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Categoría</label>
                    <button type="button" onclick="abrirModalCategorias()" class="w-full bg-white border border-slate-300 hover:border-institucional p-4 rounded-xl flex items-center justify-between shadow-sm active:scale-[0.98] transition-all">
                        <div class="flex items-center gap-3">
                            <div id="cat-icon-preview" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                <i class="ph-fill ph-question text-xl"></i>
                            </div>
                            <span id="cat-text-preview" class="font-bold text-slate-500">Toca para seleccionar...</span>
                        </div>
                        <i class="ph-bold ph-caret-down text-slate-400"></i>
                    </button>
                    <input type="hidden" name="categoria" id="input-categoria">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Foto de Evidencia</label>
                    <label for="input-foto" class="w-full bg-white border-2 border-dashed border-slate-300 hover:border-institucional p-6 rounded-xl flex flex-col items-center justify-center gap-2 active:bg-slate-50 transition cursor-pointer group">
                        <i class="ph-fill ph-camera text-4xl text-slate-300 group-hover:text-institucional transition-colors"></i>
                        <span class="text-sm font-bold text-institucional" id="foto-label">Tomar foto o elegir galería</span>
                        <input type="file" name="foto" id="input-foto" accept="image/jpeg, image/png, image/jpg" capture="environment" class="hidden" onchange="previewFoto(event)">
                    </label>
                    
                    <div id="foto-preview-container" class="hidden mt-3 relative rounded-xl overflow-hidden shadow-md border border-slate-200 h-48 w-full">
                        <img id="foto-preview" src="" class="w-full h-full object-cover">
                        <button type="button" onclick="quitarFoto(event)" class="absolute top-3 right-3 bg-red-500/90 backdrop-blur text-white w-10 h-10 rounded-full flex items-center justify-center shadow-lg active:scale-90 transition">
                            <i class="ph-bold ph-trash text-lg"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Descripción</label>
                    <textarea name="descripcion" id="input-desc" rows="3" placeholder="Ej. El bache es muy profundo y abarca medio carril..." class="w-full rounded-xl border border-slate-300 bg-white p-4 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-accion transition shadow-sm resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- PASO 3: CONTACTO -->
        <div class="p-5">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-full bg-institucional text-white flex items-center justify-center font-black shadow-sm">3</div>
                <h2 class="text-lg font-bold text-slate-800">Contacto</h2>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                    WhatsApp para notificaciones 
                    <span class="bg-blue-50 text-institucional lowercase font-medium px-2 py-0.5 rounded-md tracking-normal">Opcional</span>
                </label>
                <div class="relative flex shadow-sm rounded-xl overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-accion transition bg-white">
                    <span class="inline-flex items-center px-4 bg-slate-50 text-slate-500 font-bold border-r border-slate-200">
                        <i class="ph-fill ph-whatsapp-logo text-green-500 text-lg mr-1"></i> +52
                    </span>
                    <input type="tel" name="telefono_contacto" id="input-telefono" placeholder="10 dígitos" pattern="[0-9]{10}" class="w-full px-4 py-4 text-base font-bold text-slate-700 focus:outline-none bg-transparent">
                </div>
            </div>
        </div>

        <!-- BOTÓN PEGAJOSO CON PROTECCIÓN DE ENVÍO -->
        <div class="absolute bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-slate-200 p-4 shadow-[0_-10px_20px_rgba(0,0,0,0.05)] z-40">
            <button type="submit" id="btn-submit" class="w-full bg-institucional hover:bg-blue-900 text-white font-black py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg outline-none active:scale-[0.98]">
                <span id="btn-submit-text">Enviar Reporte Ahora</span>
                <i id="btn-submit-icon" class="ph-bold ph-paper-plane-tilt text-xl"></i>
            </button>
        </div>
    </form>
</div>

<!-- ========================================== -->
<!-- MODALES GLOBALES DE LA PWA                 -->
<!-- ========================================== -->

<!-- Modal Selector de Categorías -->
<div id="modal-categorias" class="fixed inset-0 z-[99999] hidden flex-col justify-end">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="cerrarModalCategorias()"></div>
    <div id="modal-categorias-content" class="bg-white rounded-t-3xl w-full max-w-md mx-auto relative z-10 animate-slide-up flex flex-col max-h-[85vh] transition-transform duration-300">
        
        <div id="modal-drag-handle" class="w-full pt-4 pb-2 flex justify-center cursor-grab active:cursor-grabbing touch-none">
            <div class="w-12 h-1.5 bg-slate-300 rounded-full hover:bg-slate-400 transition-colors"></div>
        </div>
        
        <button type="button" onclick="cerrarModalCategorias()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 active:scale-95 transition-all">
            <i class="ph-bold ph-x text-lg"></i>
        </button>

        <h3 class="px-6 font-black text-xl text-institucional mb-2 mt-1">Selecciona el problema</h3>
        <p class="px-6 text-sm text-slate-500 font-medium mb-4">Esto nos ayuda a enviar a la brigada correcta.</p>
        
        <div class="overflow-y-auto px-4 pb-8 space-y-2">
            <button type="button" onclick="setCat('Bache', 'Bache o Pavimento', 'ph-car', 'text-amber-500', 'bg-amber-50')" class="w-full p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-50 active:bg-slate-100 border border-slate-100 transition text-left">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-amber-50 text-amber-500"><i class="ph-fill ph-car text-2xl"></i></div>
                <div><span class="font-bold text-slate-700 block">Bache o Pavimento</span><span class="text-xs text-slate-400">Socavones, calles dañadas</span></div>
            </button>
            <button type="button" onclick="setCat('Fuga de agua', 'Fuga de Agua', 'ph-drop', 'text-blue-500', 'bg-blue-50')" class="w-full p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-50 active:bg-slate-100 border border-slate-100 transition text-left">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 text-blue-500"><i class="ph-fill ph-drop text-2xl"></i></div>
                <div><span class="font-bold text-slate-700 block">Fuga de Agua</span><span class="text-xs text-slate-400">Tuberías rotas, desperdicio</span></div>
            </button>
            <button type="button" onclick="setCat('Luz', 'Alumbrado Público', 'ph-lightbulb', 'text-yellow-500', 'bg-yellow-50')" class="w-full p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-50 active:bg-slate-100 border border-slate-100 transition text-left">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-yellow-50 text-yellow-500"><i class="ph-fill ph-lightbulb text-2xl"></i></div>
                <div><span class="font-bold text-slate-700 block">Alumbrado Público</span><span class="text-xs text-slate-400">Lámparas apagadas o rotas</span></div>
            </button>
            <button type="button" onclick="setCat('Drenaje', 'Drenaje o Alcantarilla', 'ph-toilet', 'text-slate-600', 'bg-slate-100')" class="w-full p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-50 active:bg-slate-100 border border-slate-100 transition text-left">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600"><i class="ph-fill ph-toilet text-2xl"></i></div>
                <div><span class="font-bold text-slate-700 block">Drenaje o Alcantarilla</span><span class="text-xs text-slate-400">Inundaciones, tapas faltantes</span></div>
            </button>
            <button type="button" onclick="setCat('Basura', 'Basura o Limpieza', 'ph-trash', 'text-green-500', 'bg-green-50')" class="w-full p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-50 active:bg-slate-100 border border-slate-100 transition text-left">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-green-50 text-green-500"><i class="ph-fill ph-trash text-2xl"></i></div>
                <div><span class="font-bold text-slate-700 block">Basura o Limpieza</span><span class="text-xs text-slate-400">Acumulación de residuos, parques</span></div>
            </button>
            <button type="button" onclick="setCat('Otro', 'Otro / Varios', 'ph-dots-three-circle', 'text-indigo-500', 'bg-indigo-50')" class="w-full p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-50 active:bg-slate-100 border border-slate-100 transition text-left">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-50 text-indigo-500"><i class="ph-fill ph-dots-three-circle text-2xl"></i></div>
                <div><span class="font-bold text-slate-700 block">Otro / Varios</span><span class="text-xs text-slate-400">Problemas en espacios públicos, etc.</span></div>
            </button>
        </div>
    </div>
</div>

<!-- Modal Capas Exclusivo para Móviles -->
<div id="modal-capas-movil" class="fixed inset-0 z-[99999] hidden flex-col justify-end sm:hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="cerrarModalCapas()"></div>
    <div id="modal-capas-movil-content" class="bg-white rounded-t-3xl w-full max-w-md mx-auto relative z-10 flex flex-col transition-transform duration-300 pb-8">
        
        <div id="modal-capas-drag" class="w-full pt-4 pb-2 flex justify-center cursor-grab active:cursor-grabbing touch-none">
            <div class="w-12 h-1.5 bg-slate-300 rounded-full hover:bg-slate-400 transition-colors"></div>
        </div>
        
        <button type="button" onclick="cerrarModalCapas()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 active:scale-95 transition-all">
            <i class="ph-bold ph-x text-lg"></i>
        </button>

        <h3 class="px-6 font-black text-xl text-institucional mb-4 mt-1">Capas y Vistas</h3>
        
        <div class="px-6 space-y-5">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Estilo Base</p>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block cursor-pointer group">
                        <input type="radio" name="capa_crear_movil" value="calles" checked onchange="cambiarCapaCrear('calles')" class="peer hidden">
                        <div class="flex flex-col items-center justify-center gap-2 p-4 border-2 border-slate-100 rounded-2xl transition-all peer-checked:border-institucional peer-checked:bg-blue-50 group-active:scale-95">
                            <i class="ph-fill ph-map-trifold text-3xl text-blue-500"></i>
                            <span class="text-sm font-bold text-slate-700">Calles</span>
                        </div>
                    </label>
                    <label class="block cursor-pointer group">
                        <input type="radio" name="capa_crear_movil" value="satelite" onchange="cambiarCapaCrear('satelite')" class="peer hidden">
                        <div class="flex flex-col items-center justify-center gap-2 p-4 border-2 border-slate-100 rounded-2xl transition-all peer-checked:border-institucional peer-checked:bg-blue-50 group-active:scale-95">
                            <i class="ph-fill ph-globe-hemisphere-west text-3xl text-green-600"></i>
                            <span class="text-sm font-bold text-slate-700">Satélite</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Perspectiva</p>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block cursor-pointer group">
                        <input type="radio" name="vista_crear_movil" value="2d" checked onchange="cambiarVista3D(false)" class="peer hidden">
                        <div class="flex flex-col items-center justify-center gap-2 p-4 border-2 border-slate-100 rounded-2xl transition-all peer-checked:border-institucional peer-checked:bg-blue-50 group-active:scale-95">
                            <i class="ph-fill ph-crosshair text-3xl text-slate-500"></i>
                            <span class="text-sm font-bold text-slate-700">Plana (2D)</span>
                        </div>
                    </label>
                    <label id="wrapper-3d-movil" class="block cursor-pointer group">
                        <input type="radio" id="input-3d-movil" name="vista_crear_movil" value="3d" onchange="cambiarVista3D(true)" class="peer hidden">
                        <div class="flex flex-col items-center justify-center gap-2 p-4 border-2 border-slate-100 rounded-2xl transition-all peer-checked:border-institucional peer-checked:bg-blue-50 group-active:scale-95">
                            <i class="ph-fill ph-mountains text-3xl text-amber-600"></i>
                            <span class="text-sm font-bold text-slate-700">Relieve (3D)</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alertas -->
<div id="modal-alerta" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="cerrarAlerta()"></div>
    <div class="bg-white rounded-3xl w-full max-w-sm relative z-10 animate-scale-in p-6 text-center shadow-2xl">
        <div id="alerta-icon-wrap" class="w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-4 bg-red-50 text-red-500 shadow-inner">
            <i id="alerta-icon" class="ph-fill ph-warning-circle text-4xl"></i>
        </div>
        <h3 id="alerta-titulo" class="font-black text-xl text-slate-800 mb-2">Atención</h3>
        <p id="alerta-mensaje" class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">Mensaje de error.</p>
        <button type="button" onclick="cerrarAlerta()" id="alerta-btn" class="w-full bg-red-500 text-white font-bold py-3.5 rounded-xl hover:bg-red-600 active:scale-95 transition-all shadow-md">
            Entendido
        </button>
    </div>
</div>

@endsection

@stack('scripts')
<!-- Librería MapLibre GL JS -->
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

<script>
    const LOCATIONIQ_TOKEN = 'TU_TOKEN_LOCATIONIQ_AQUI'; 

    let mapa, marker;
    
    // ==========================================
    // MAGIA MULTI-TENANT: Coordenadas y Validación Inyectadas
    // ==========================================
    let coordsActuales = [{{ $mapaData['lng'] }}, {{ $mapaData['lat'] }}];
    const ZOOM_INICIAL = {{ $mapaData['zoom'] }};
    
    // Normalizamos el nombre para la validación satelital (ej: "Nochistlán" -> "nochistlan")
    const MUNICIPIO_OBJETIVO = "{{ strtolower($municipioSeleccionado->nombre) }}".normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    
    let isSubmitting = false;
    let timeoutGeocoding = null;
    let ultimaCoordenadaGeocodificada = null;
    let ultimaDireccion = '';

    // ==========================================
    // SISTEMA DE SEGURIDAD (LocalStorage) - 30 MIN
    // ==========================================
    function verificarLimitesSeguridad() {
        const hoy = new Date().toLocaleDateString();
        let count = parseInt(localStorage.getItem('mm_reportes_count')) || 0;
        let date = localStorage.getItem('mm_reportes_date');
        let lastTime = parseInt(localStorage.getItem('mm_ultimo_reporte')) || 0;

        // Resetear contador si es un nuevo día
        if (date !== hoy) {
            count = 0;
            localStorage.setItem('mm_reportes_date', hoy);
            localStorage.setItem('mm_reportes_count', 0);
        }

        const MINUTOS_ESPERA = 30; // Bloqueo de Media Hora
        const ahora = new Date().getTime();
        const diffMinutos = (ahora - lastTime) / (1000 * 60);

        const btn = document.getElementById('btn-submit');
        const text = document.getElementById('btn-submit-text');

        if (count >= 3) {
            bloquearBotonUI(btn, text, 'Límite diario alcanzado (3/3)');
            mostrarAlertaUI('Límite Diario', 'Has alcanzado el límite máximo de 3 reportes por día. Gracias por ayudar a mejorar tu municipio. Vuelve mañana.', 'municipio');
            isSubmitting = true; 
        } else if (diffMinutos < MINUTOS_ESPERA) {
            let minRestantes = Math.ceil(MINUTOS_ESPERA - diffMinutos);
            bloquearBotonUI(btn, text, `Espera ${minRestantes} min para reportar`);
            isSubmitting = true;
        }
    }

    function bloquearBotonUI(btn, textElement, mensaje) {
        btn.disabled = true;
        btn.classList.add('bg-slate-400', 'cursor-not-allowed', 'opacity-90');
        btn.classList.remove('hover:bg-blue-900', 'bg-institucional', 'active:scale-[0.98]');
        textElement.innerText = mensaje;
        document.getElementById('btn-submit-icon').classList.replace('ph-paper-plane-tilt', 'ph-lock-key');
    }

    // ==========================================
    // SISTEMA DE DETECCIÓN DE HARDWARE (ENTERPRISE)
    // ==========================================
    function detectarHardwareLimitado() {
        if (navigator.deviceMemory && navigator.deviceMemory <= 3) return true;
        if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) return true;
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            if (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2) return true;
        }
        return false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        
        verificarLimitesSeguridad();

        const isHardwareBajo = detectarHardwareLimitado();
        
        if (isHardwareBajo) {
            const btn3DDesk = document.getElementById('input-3d-desktop');
            const wrap3DDesk = document.getElementById('wrapper-3d-desktop');
            if (btn3DDesk) btn3DDesk.disabled = true;
            if (wrap3DDesk) {
                wrap3DDesk.classList.add('opacity-40', 'grayscale');
                wrap3DDesk.addEventListener('click', function(e) {
                    e.preventDefault(); e.stopPropagation();
                    document.getElementById('menu-capas-crear').classList.add('hidden');
                    mostrarAlertaUI('Modo de Ahorro', 'Tu dispositivo tiene poca memoria o un procesador modesto. Hemos desactivado el relieve 3D para asegurar que la aplicación fluya rápido y sin congelarse.', 'municipio');
                }, true);
            }

            const btn3DMovil = document.getElementById('input-3d-movil');
            const wrap3DMovil = document.getElementById('wrapper-3d-movil');
            if (btn3DMovil) btn3DMovil.disabled = true;
            if (wrap3DMovil) {
                wrap3DMovil.classList.add('opacity-40', 'grayscale');
                wrap3DMovil.addEventListener('click', function(e) {
                    e.preventDefault(); e.stopPropagation();
                    cerrarModalCapas();
                    mostrarAlertaUI('Modo de Ahorro', 'Tu celular tiene poca memoria RAM o procesador básico. Desactivamos el 3D para evitar que se sature y se caliente, asegurando tu experiencia.', 'municipio');
                }, true);
            }
        }

        // 1. INICIALIZAR EL MOTOR con Coordenadas Dinámicas
        mapa = new maplibregl.Map({
            container: 'map',
            center: coordsActuales,
            zoom: ZOOM_INICIAL,
            pitch: 0,
            bearing: 0,
            antialias: isHardwareBajo ? false : (window.innerWidth >= 768), 
            style: {
                version: 8,
                sources: {
                    'calles-source': { type: 'raster', tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'], tileSize: 256, attribution: '© OpenStreetMap' },
                    'satelite-source': { type: 'raster', tiles: ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'], tileSize: 256, attribution: '© Esri' },
                    'etiquetas-source': { type: 'raster', tiles: ['https://a.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}@2x.png'], tileSize: 256 },
                    'terrain-source': { type: 'raster-dem', tiles: ['https://s3.amazonaws.com/elevation-tiles-prod/terrarium/{z}/{x}/{y}.png'], encoding: 'terrarium', tileSize: 256, maxzoom: 14 }
                },
                layers: [
                    { id: 'capa-satelite', type: 'raster', source: 'satelite-source', layout: { visibility: 'none' } },
                    { id: 'capa-calles', type: 'raster', source: 'calles-source', layout: { visibility: 'visible' } },
                    { id: 'capa-etiquetas', type: 'raster', source: 'etiquetas-source', layout: { visibility: 'none' } }
                ]
            }
        });

        mapa.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-right');
        
        // ==============================================================
        // NUEVO: Botón de Geolocalización Nativo (Punto Azul + Radio)
        // ==============================================================
        const geolocateControl = new maplibregl.GeolocateControl({
            positionOptions: { enableHighAccuracy: true },
            trackUserLocation: true,
            showUserHeading: true,
            showUserLocation: true,
            showAccuracyCircle: true // Activa el radio clásico de aproximación
        });
        mapa.addControl(geolocateControl, 'top-right');

        setTimeout(() => mapa.resize(), 400);

        // Disparar la localización automáticamente al cargar el mapa
        mapa.on('load', function() {
            geolocateControl.trigger();
        });

        // Evento que se dispara cuando la brújula encuentra al usuario
        geolocateControl.on('geolocate', function(e) {
            // CORRECCIÓN: Solo ubicamos al usuario (punto azul y radio), 
            // pero NO ponemos el pin rojo automáticamente para no agotar llamadas a la API
            // y permitir que el usuario decida el punto exacto.
            
            // Nos aseguramos de mostrarle las instrucciones para que toque el mapa
            const inst = document.getElementById('instrucciones-mapa');
            inst.classList.remove('hidden');
            
            // Animación sutil para llamar la atención a las instrucciones
            inst.classList.add('ring-2', 'ring-institucional', 'scale-105');
            setTimeout(() => {
                inst.classList.remove('ring-2', 'ring-institucional', 'scale-105');
            }, 2000);
        });

        // Click manual en el mapa (Mantenemos tu funcionalidad)
        mapa.on('click', function(e) {
            const lng = e.lngLat.lng;
            const lat = e.lngLat.lat;
            
            document.getElementById('indicador-validacion').classList.remove('hidden');
            document.getElementById('instrucciones-mapa').classList.add('hidden');
            document.getElementById('box-direccion').classList.add('hidden');

            if (timeoutGeocoding) clearTimeout(timeoutGeocoding);

            if (ultimaCoordenadaGeocodificada) {
                const dist = calcularDistancia(lat, lng, ultimaCoordenadaGeocodificada.lat, ultimaCoordenadaGeocodificada.lng);
                if (dist < 50) {
                    colocarPin(lat, lng);
                    mostrarDireccionEnUI(ultimaDireccion, lat, lng);
                    return; 
                }
            }

            timeoutGeocoding = setTimeout(() => {
                ejecutarGeocoding(lat, lng, MUNICIPIO_OBJETIVO);
            }, 1200);
        });

        document.addEventListener('click', function(e) {
            const btn = document.getElementById('btn-capas-crear');
            const menu = document.getElementById('menu-capas-crear');
            if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
                menu.classList.remove('sm:flex');
            }
        });
        
        inicializarSwipeModal('modal-drag-handle', 'modal-categorias-content', cerrarModalCategorias);
        inicializarSwipeModal('modal-capas-drag', 'modal-capas-movil-content', cerrarModalCapas);
    });

    // ==========================================
    // SISTEMA DE CAPAS Y VISTAS RESPONSIVO
    // ==========================================
    function toggleMenuCapas() {
        if (window.innerWidth < 640) {
            document.getElementById('modal-capas-movil').classList.remove('hidden');
            document.getElementById('modal-capas-movil').classList.add('flex');
            document.getElementById('modal-capas-movil-content').style.transform = '';
        } else {
            const menu = document.getElementById('menu-capas-crear');
            menu.classList.toggle('hidden');
            menu.classList.toggle('sm:flex');
        }
    }

    function cerrarModalCapas() {
        const modalContent = document.getElementById('modal-capas-movil-content');
        if(modalContent) modalContent.style.transform = '';
        document.getElementById('modal-capas-movil').classList.add('hidden');
        document.getElementById('modal-capas-movil').classList.remove('flex');
    }

    function cambiarCapaCrear(tipo) {
        document.querySelectorAll('input[name="capa_crear"]').forEach(r => r.checked = (r.value === tipo));
        document.querySelectorAll('input[name="capa_crear_movil"]').forEach(r => r.checked = (r.value === tipo));

        if (tipo === 'satelite') {
            mapa.setLayoutProperty('capa-calles', 'visibility', 'none');
            mapa.setLayoutProperty('capa-satelite', 'visibility', 'visible');
            mapa.setLayoutProperty('capa-etiquetas', 'visibility', 'visible');
        } else {
            mapa.setLayoutProperty('capa-satelite', 'visibility', 'none');
            mapa.setLayoutProperty('capa-etiquetas', 'visibility', 'none');
            mapa.setLayoutProperty('capa-calles', 'visibility', 'visible');
        }
        
        cerrarModalCapas();
        document.getElementById('menu-capas-crear').classList.add('hidden');
        document.getElementById('menu-capas-crear').classList.remove('sm:flex');
    }

    function cambiarVista3D(activar) {
        const isSatelite = document.querySelector('input[name="capa_crear"]:checked').value === 'satelite';
        
        const val = activar ? '3d' : '2d';
        document.querySelectorAll('input[name="vista_crear"]').forEach(r => r.checked = (r.value === val));
        document.querySelectorAll('input[name="vista_crear_movil"]').forEach(r => r.checked = (r.value === val));
        
        if (activar) {
            mapa.setTerrain({ source: 'terrain-source', exaggeration: 1.2 });
            mapa.setFog({
                'color': isSatelite ? 'rgba(15, 23, 42, 0.8)' : 'rgba(255, 255, 255, 0.9)',
                'high-color': 'rgba(36, 144, 235, 0.2)',
                'space-color': isSatelite ? '#000' : '#e2e8f0'
            });
            mapa.flyTo({ pitch: 70, bearing: 25, duration: 1500 });
        } else {
            mapa.setTerrain(null);
            mapa.setFog(null);
            mapa.flyTo({ pitch: 0, bearing: 0, duration: 1500 });
        }
        
        cerrarModalCapas();
        document.getElementById('menu-capas-crear').classList.add('hidden');
        document.getElementById('menu-capas-crear').classList.remove('sm:flex');
    }

    function toggleFullscreenMap() {
        const contenedor = document.getElementById('map-container');
        const icono = document.getElementById('fs-icon');

        if(contenedor.classList.contains('fixed')) {
            contenedor.classList.remove('fixed', 'top-0', 'left-0', 'w-full', 'h-[100dvh]', 'z-[9990]', 'rounded-none');
            contenedor.classList.add('relative', 'h-64', 'sm:h-80', 'rounded-2xl', 'border-2', 'border-slate-200');
            icono.classList.replace('ph-corners-in', 'ph-arrows-out');
            document.body.style.overflow = ''; 
        } else {
            contenedor.classList.remove('relative', 'h-64', 'sm:h-80', 'rounded-2xl', 'border-2', 'border-slate-200');
            contenedor.classList.add('fixed', 'top-0', 'left-0', 'w-full', 'h-[100dvh]', 'z-[9990]', 'rounded-none');
            icono.classList.replace('ph-arrows-out', 'ph-corners-in');
            document.body.style.overflow = 'hidden'; 
        }
        setTimeout(() => mapa.resize(), 300);
    }

    function inicializarSwipeModal(dragHandleId, modalContentId, closeFunction) {
        const dragHandle = document.getElementById(dragHandleId);
        const modalContent = document.getElementById(modalContentId);
        
        if(dragHandle && modalContent) {
            let startY = 0;
            let currentY = 0;
            
            dragHandle.addEventListener('touchstart', (e) => {
                startY = e.touches[0].clientY;
                modalContent.style.transition = 'none'; 
            }, { passive: true });

            dragHandle.addEventListener('touchmove', (e) => {
                currentY = e.touches[0].clientY;
                let diffY = currentY - startY;
                if (diffY > 0) { 
                    modalContent.style.transform = `translateY(${diffY}px)`;
                }
            }, { passive: true });

            dragHandle.addEventListener('touchend', (e) => {
                modalContent.style.transition = 'transform 0.3s ease-out';
                let diffY = currentY - startY;
                
                if (diffY > 80) { closeFunction(); } else { modalContent.style.transform = ''; }
            });
        }
    }

    function colocarPin(lat, lng) {
        if (marker) marker.remove();
        let el = document.createElement('div');
        el.innerHTML = `<div style="color: #1A365D; filter: drop-shadow(0 8px 10px rgba(26,54,93,0.4)); transform: translateY(-50%);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 256 256">
                                <path fill="currentColor" d="M128,16a88.1,88.1,0,0,0-88,88c0,75.3,80,132.17,83.41,134.55a8,8,0,0,0,9.18,0C136,236.17,216,179.3,216,104A88.1,88.1,0,0,0,128,16Zm0,56a32,32,0,1,1-32,32A32,32,0,0,1,128,72Z"></path>
                            </svg></div>`;
        marker = new maplibregl.Marker({ element: el }).setLngLat([lng, lat]).addTo(mapa);
        
        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;
    }

    async function ejecutarGeocoding(lat, lng, municipio_objetivo) {
        const urlLocationIQ = `https://us1.locationiq.com/v1/reverse.php?key=${LOCATIONIQ_TOKEN}&lat=${lat}&lon=${lng}&format=json`;
        const urlNominatim = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;

        try {
            let response = await fetch(urlLocationIQ);
            if (!response.ok) throw new Error('LocationIQ Fail');
            let data = await response.json();
            procesarDireccion(data, lat, lng, 'iq', municipio_objetivo);
            
        } catch (errorIq) {
            try {
                let responseNom = await fetch(urlNominatim);
                if (!responseNom.ok) throw new Error('Nominatim Fail');
                let dataNom = await responseNom.json();
                procesarDireccion(dataNom, lat, lng, 'nom', municipio_objetivo);
            } catch (errorNom) {
                colocarPin(lat, lng);
                mostrarDireccionEnUI('Coordenadas fijadas en ' + "{{ $municipioSeleccionado->nombre }}", lat, lng);
            }
        }
    }

    function procesarDireccion(data, lat, lng, source, municipio_objetivo) {
        if (!data || !data.address) {
            mostrarAlertaUI('Error Satelital', 'No se pudo leer la calle. Intenta tocar un poco más al centro.', 'error');
            return;
        }

        let esValido = false;
        let municipioEncontrado = '';
        let calle = '';
        let colonia = '';

        if (source === 'iq') {
            municipioEncontrado = data.address.town || data.address.city || data.address.village || data.address.county || '';
            calle = data.address.road || data.address.pedestrian || '';
            colonia = data.address.neighbourhood || data.address.suburb || '';
        } else {
            municipioEncontrado = data.address.county || data.address.city || data.address.town || data.address.village || data.address.municipality || '';
            calle = data.address.road || data.address.pedestrian || '';
            colonia = data.address.neighbourhood || data.address.suburb || '';
        }
        
        // Validación Robusta con soporte de acentos
        const posibles = [data.address.county, data.address.city, data.address.town, data.address.village, data.address.municipality];
        esValido = posibles.some(c => c && c.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(municipio_objetivo));

        if (esValido) {
            colocarPin(lat, lng);
            let dirFinal = calle ? calle + (colonia ? ', ' + colonia : '') : `Punto validado en ${municipioEncontrado}`;
            ultimaDireccion = dirFinal;
            ultimaCoordenadaGeocodificada = { lat, lng };
            mostrarDireccionEnUI(dirFinal, lat, lng);
        } else {
            if (marker) marker.remove();
            document.getElementById('latitud').value = '';
            document.getElementById('longitud').value = '';
            document.getElementById('indicador-validacion').classList.add('hidden');
            document.getElementById('box-direccion').classList.add('hidden');
            document.getElementById('instrucciones-mapa').innerHTML = '<span class="text-red-500 font-bold"><i class="ph-fill ph-warning-octagon"></i> Toca dentro del municipio.</span>';
            document.getElementById('instrucciones-mapa').classList.remove('hidden');
            mostrarAlertaUI('Fuera de Cobertura', `El punto pertenece a ${municipioEncontrado || 'otra región'}. Por favor, selecciona una calle en {{ $municipioSeleccionado->nombre }}.`, 'municipio');
        }
    }

    function mostrarDireccionEnUI(dir, lat, lng) {
        document.getElementById('indicador-validacion').classList.add('hidden');
        document.getElementById('instrucciones-mapa').classList.add('hidden');
        document.getElementById('texto-direccion').innerText = dir;
        document.getElementById('box-direccion').classList.remove('hidden');
        let inputDir = document.getElementById('direccion_texto');
        if (inputDir) inputDir.value = dir;
    }

    function calcularDistancia(lat1, lon1, lat2, lon2) {
        const R = 6371e3; 
        const phi1 = lat1 * Math.PI/180;
        const phi2 = lat2 * Math.PI/180;
        const deltaPhi = (lat2-lat1) * Math.PI/180;
        const deltaLambda = (lon2-lon1) * Math.PI/180;
        const a = Math.sin(deltaPhi/2) * Math.sin(deltaPhi/2) + Math.cos(phi1) * Math.cos(phi2) * Math.sin(deltaLambda/2) * Math.sin(deltaLambda/2);
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
    }

    function abrirModalCategorias() {
        const modalContent = document.getElementById('modal-categorias-content');
        if(modalContent) modalContent.style.transform = '';
        document.getElementById('modal-categorias').classList.remove('hidden');
        document.getElementById('modal-categorias').classList.add('flex');
    }
    
    function cerrarModalCategorias() {
        const modalContent = document.getElementById('modal-categorias-content');
        if(modalContent) modalContent.style.transform = '';
        document.getElementById('modal-categorias').classList.add('hidden');
        document.getElementById('modal-categorias').classList.remove('flex');
    }

    function setCat(valorBaseDatos, etiquetaVisual, icono, colorTxt, colorBg) {
        document.getElementById('input-categoria').value = valorBaseDatos;
        const previewText = document.getElementById('cat-text-preview');
        previewText.innerText = etiquetaVisual;
        previewText.classList.replace('text-slate-500', 'text-institucional');
        previewText.classList.replace('font-medium', 'font-black');
        const previewIcon = document.getElementById('cat-icon-preview');
        previewIcon.className = `w-10 h-10 rounded-full flex items-center justify-center ${colorBg} ${colorTxt}`;
        previewIcon.innerHTML = `<i class="ph-fill ${icono} text-xl"></i>`;
        cerrarModalCategorias();
    }

    function previewFoto(event) {
        const file = event.target.files[0];
        if (file) {
            document.getElementById('foto-preview').src = URL.createObjectURL(file);
            document.getElementById('foto-preview-container').classList.remove('hidden');
            document.getElementById('foto-label').innerText = "Evidencia Cargada Exitosamente";
            document.getElementById('foto-label').classList.replace('text-institucional', 'text-green-600');
        }
    }

    function quitarFoto(e) {
        e.preventDefault();
        document.getElementById('input-foto').value = '';
        document.getElementById('foto-preview').src = '';
        document.getElementById('foto-preview-container').classList.add('hidden');
        document.getElementById('foto-label').innerText = "Tomar foto o elegir galería";
        document.getElementById('foto-label').classList.replace('text-green-600', 'text-institucional');
    }

    function mostrarAlertaUI(titulo, mensaje, tipo = 'error') {
        document.getElementById('alerta-titulo').innerText = titulo;
        document.getElementById('alerta-mensaje').innerText = mensaje;
        const wrap = document.getElementById('alerta-icon-wrap');
        const icon = document.getElementById('alerta-icon');
        const btn = document.getElementById('alerta-btn');
        if (tipo === 'error') {
            wrap.className = 'w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-4 bg-red-50 text-red-500 shadow-inner';
            icon.className = 'ph-fill ph-warning-circle text-4xl';
            btn.className = 'w-full bg-red-500 text-white font-bold py-3.5 rounded-xl hover:bg-red-600 active:scale-95 transition-all shadow-md';
        } else if (tipo === 'municipio') {
            wrap.className = 'w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-4 bg-amber-50 text-amber-500 shadow-inner';
            icon.className = 'ph-fill ph-map-pin-line text-4xl';
            btn.className = 'w-full bg-amber-500 text-white font-bold py-3.5 rounded-xl hover:bg-amber-600 active:scale-95 transition-all shadow-md';
        }
        document.getElementById('modal-alerta').classList.remove('hidden');
        document.getElementById('modal-alerta').classList.add('flex');
    }

    function cerrarAlerta() {
        document.getElementById('modal-alerta').classList.add('hidden');
        document.getElementById('modal-alerta').classList.remove('flex');
    }

    function validarYEnviar(e) {
        e.preventDefault(); 
        if (isSubmitting) return;

        const lat = document.getElementById('latitud').value;
        const cat = document.getElementById('input-categoria').value;
        const desc = document.getElementById('input-desc').value.trim();
        const tel = document.getElementById('input-telefono').value.trim();

        if (!lat) {
            mostrarAlertaUI('Falta la ubicación', 'Toca el mapa para indicar el lugar exacto del problema.');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        if (!cat) {
            mostrarAlertaUI('Falta la Categoría', 'Selecciona qué tipo de problema vas a reportar (Ej. Bache, Fuga).');
            return;
        }
        if (desc.length < 10) {
            mostrarAlertaUI('Descripción muy corta', 'Ayuda a la cuadrilla escribiendo un poco más de detalles sobre el problema (mínimo 10 letras).');
            return;
        }
        if (tel.length > 0 && tel.length < 10) {
            mostrarAlertaUI('Teléfono incompleto', 'Si decides compartir tu WhatsApp, asegúrate de escribir los 10 dígitos correctamente.');
            return;
        }

        // --- ACTUALIZAR LOS CONTADORES LOCALES ---
        let count = parseInt(localStorage.getItem('mm_reportes_count')) || 0;
        localStorage.setItem('mm_reportes_count', count + 1);
        localStorage.setItem('mm_ultimo_reporte', new Date().getTime());

        isSubmitting = true;
        const btn = document.getElementById('btn-submit');
        const text = document.getElementById('btn-submit-text');
        const icon = document.getElementById('btn-submit-icon');

        btn.classList.add('pointer-events-none', 'bg-slate-800', 'opacity-80', 'scale-100');
        btn.classList.remove('hover:bg-blue-900', 'active:scale-[0.98]', 'bg-institucional');
        text.innerText = 'Enviando al servidor...';
        icon.className = 'ph-bold ph-spinner-gap animate-spin text-xl text-white';

        document.getElementById('form-reporte').submit();
    }
</script>