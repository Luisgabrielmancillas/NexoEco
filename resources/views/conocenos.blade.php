@extends('layouts.marketplace')

@section('content')
<div class="p-6 space-y-10">
    <!-- Encabezado principal -->
    <div class="w-full">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Conócenos</h1>
        
        <div class="space-y-4 text-gray-600 leading-relaxed text-base md:text-lg text-justify">
            <p>
                Bienvenido a <strong class="text-primary-600 font-semibold">NexoEco</strong>, la plataforma digital diseñada para transformar la forma en que conectamos con el comercio local en Manzanillo, Colima.
            </p>
            <p>
                Nuestra misión es impulsar la economía de la comunidad creando un puente directo entre los consumidores y los comercios locales. Queremos facilitar el descubrimiento de productos y servicios de tu entorno, promoviendo un consumo consciente, ágil y seguro que fortalezca el desarrollo de los emprendedores y negocios de nuestra región.
            </p>
        </div>
    </div>

    <!-- Sección Ilustrativa Estática -->
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-8 text-center md:text-left">Impulsando la Comunidad Local</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            
            <!-- Ilustración 1 -->
            <div class="flex flex-col items-center text-center p-4">
                <div class="w-40 h-40 mb-4 flex items-center justify-center">
                    <img src="{{ asset('images/tienda-icon.png') }}" 
                         alt="Explora Tiendas Locales" 
                         class="max-w-full max-h-full object-contain pointer-events-none select-none">
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Encuentra Comercio de Cerca</h3>
                <p class="text-sm text-gray-600 max-w-sm">
                    Descubre la variedad de tiendas, negocios y productos disponibles a pocos pasos de tu hogar.
                </p>
            </div>

            <!-- Ilustración 2 -->
            <div class="flex flex-col items-center text-center p-4">
                <div class="w-40 h-40 mb-4 flex items-center justify-center">
                    <img src="{{ asset('images/tienda-icon2.png') }}" 
                         alt="Súmate como Vendedor" 
                         class="max-w-full max-h-full object-contain pointer-events-none select-none">
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Crece como Emprendedor</h3>
                <p class="text-sm text-gray-600 max-w-sm">
                    Únete a NexoEco como vendedor y amplía la visibilidad de tu negocio digitalmente.
                </p>
            </div>

        </div>
    </div>

    <!-- Sección de Documentos Legales -->
    <div class="pt-8 border-t border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Información Legal y Transparencia</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Tarjeta 1: Aviso de Privacidad -->
            <div onclick="openModal('modal-privacidad')" 
                 class="cursor-pointer bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md hover:border-primary-500 transition-all group">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-primary-100 rounded-lg text-primary-600 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-primary-600">Aviso de Privacidad</h3>
                        <span class="text-xs text-gray-500">Documento PDF Oficial</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600">
                    Consulta cómo protegemos tus datos personales, derechos ARCO y el tratamiento de información bancaria.
                </p>
                <div class="mt-4 flex items-center text-sm font-medium text-primary-600">
                    Ver documento en pantalla &rarr;
                </div>
            </div>

            <!-- Tarjeta 2: Términos y Condiciones -->
            <div onclick="openModal('modal-terminos')" 
                 class="cursor-pointer bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md hover:border-primary-500 transition-all group">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-primary-100 rounded-lg text-primary-600 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-primary-600">Términos y Condiciones</h3>
                        <span class="text-xs text-gray-500">Documento PDF Oficial</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600">
                    Conoce las reglas de la plataforma, condiciones para compradores, vendedores y políticas de uso.
                </p>
                <div class="mt-4 flex items-center text-sm font-medium text-primary-600">
                    Ver documento en pantalla &rarr;
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: AVISO DE PRIVACIDAD (VISOR PDF)   -->
<!-- ========================================== -->
<div id="modal-privacidad" class="fixed inset-0 z-50 hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-5xl w-full h-[85vh] flex flex-col shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Aviso de Privacidad Integral</h3>
                <p class="text-xs text-gray-500">NEXOECO — Documento PDF</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ asset('docs/Aviso de privacidad.pdf') }}" target="_blank" download class="text-xs text-primary-600 hover:text-primary-700 font-medium border border-primary-200 bg-primary-50 px-3 py-1.5 rounded-lg transition-colors">
                    Descargar PDF
                </a>
                <button onclick="closeModal('modal-privacidad')" class="text-gray-400 hover:text-gray-600 text-2xl font-bold p-1">&times;</button>
            </div>
        </div>
        
        <div class="flex-1 w-full bg-gray-100">
            <iframe src="{{ asset('docs/Aviso de privacidad.pdf') }}#toolbar=0" class="w-full h-full border-0">
                <p class="p-4 text-center text-gray-600">
                    Tu navegador no soporta la vista previa de PDF. 
                    <a href="{{ asset('docs/Aviso de privacidad.pdf') }}" target="_blank" class="text-primary-600 no-underline">Haz clic aquí para descargarlo</a>.
                </p>
            </iframe>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-right">
            <button onclick="closeModal('modal-privacidad')" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-medium text-sm rounded-lg transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: TÉRMINOS Y CONDICIONES (VISOR PDF)-->
<!-- ========================================== -->
<div id="modal-terminos" class="fixed inset-0 z-50 hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-5xl w-full h-[85vh] flex flex-col shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Términos y Condiciones de Uso</h3>
                <p class="text-xs text-gray-500">NEXOECO — Documento PDF</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ asset('docs/Terminos_Condiciones_Nexoeco.pdf') }}" target="_blank" download class="text-xs text-primary-600 hover:text-primary-700 font-medium border border-primary-200 bg-primary-50 px-3 py-1.5 rounded-lg transition-colors">
                    Descargar PDF
                </a>
                <button onclick="closeModal('modal-terminos')" class="text-gray-400 hover:text-gray-600 text-2xl font-bold p-1">&times;</button>
            </div>
        </div>
        
        <div class="flex-1 w-full bg-gray-100">
            <iframe src="{{ asset('docs/Terminos_Condiciones_Nexoeco.pdf') }}#toolbar=0" class="w-full h-full border-0">
                <p class="p-4 text-center text-gray-600">
                    Tu navegador no soporta la vista previa de PDF. 
                    <a href="{{ asset('docs/Terminos_Condiciones_Nexoeco.pdf') }}" target="_blank" class="text-primary-600 no-underline">Haz clic aquí para descargarlo</a>.
                </p>
            </iframe>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-right">
            <button onclick="closeModal('modal-terminos')" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-medium text-sm rounded-lg transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- JavaScript para control de Modales -->
<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('backdrop-blur-sm')) {
            event.target.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
</script>
@endsection