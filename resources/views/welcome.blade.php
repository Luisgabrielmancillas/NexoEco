<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>MicroGestor - Gestión de Microemprendimientos</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:600,700,800" rel="stylesheet" />
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }
        
        .glass-morphism {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(107, 33, 168, 0.08);
            box-shadow: 0 8px 32px 0 rgba(76, 29, 149, 0.08);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #ffe97b 0%, #b4a0c8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-gradient {
            background: radial-gradient(circle at 30% 30%, rgba(102, 126, 234, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 70% 70%, rgba(118, 75, 162, 0.15) 0%, transparent 50%);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .feature-card {
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            box-shadow: 20px 20px 60px #d9d9d9, -20px -20px 60px #ffffff;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline {
            background: #ffffff;
            border: 2px solid #6b21a8;
            color: #6b21a8;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(107,33,168,0.12);
        }

        .btn-outline:hover {
            background: linear-gradient(135deg, #6b21a8 0%, #4c1d95 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(76,29,149,0.25);
        }
        
        .curve {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }
        
        .curve svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 150px;
        }
        
        .curve .shape-fill {
            fill: #FFFFFF;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navigation -->
    <nav class="fixed w-full z-50 py-4 glass-morphism">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rocket text-white text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        NexoEco
                    </span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-purple-600 transition font-medium">Características</a>
                    <a href="#stats" class="text-gray-700 hover:text-purple-600 transition font-medium">Estadísticas</a>
                    <a href="#testimonials" class="text-gray-700 hover:text-purple-600 transition font-medium">Testimonios</a>
                    <a href="#pricing" class="text-gray-700 hover:text-purple-600 transition font-medium">Precios</a>
                </div>
                
                <!-- Auth Buttons -->
                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary px-6 py-2.5 rounded-xl font-semibold text-sm">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-outline px-6 py-2.5 rounded-xl font-semibold text-sm hover:text-white">
                                <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary px-6 py-2.5 rounded-xl font-semibold text-sm">
                                    <i class="fas fa-user-plus mr-2"></i>Registrarse
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center hero-gradient overflow-hidden pt-20">
        <div class="container mx-auto px-6 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-purple-700 font-semibold text-sm mb-6">
                        🚀 Lanzamiento 2026
                    </div>
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        Gestiona tu <span class="gradient-text">microemprendimiento</span> como un profesional
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        La plataforma todo-en-uno para emprendedores que buscan crecimiento, organización y éxito en sus negocios.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" class="btn-primary px-8 py-4 rounded-xl font-semibold text-lg inline-flex items-center">
                            Comenzar gratis
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="#demo" class="btn-outline px-8 py-4 rounded-xl font-semibold text-lg inline-flex items-center">
                            <i class="fas fa-play-circle mr-2"></i>
                            Ver demo
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex items-center gap-8 mt-12">
                        <div>
                            <div class="text-3xl font-bold gradient-text">+10K</div>
                            <div class="text-gray-600">Emprendedores</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold gradient-text">+50K</div>
                            <div class="text-gray-600">Ventas gestionadas</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold gradient-text">98%</div>
                            <div class="text-gray-600">Satisfacción</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - Dashboard Preview -->
                <div class="relative lg:ml-12" data-aos="fade-left" data-aos-duration="1000">
                    <div class="floating">
                        <div class="glass-morphism rounded-2xl p-6 shadow-2xl">
                            <!-- Dashboard Preview -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex space-x-2">
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    </div>
                                    <span class="text-sm text-gray-500">Dashboard Preview</span>
                                </div>
                                
                                <!-- Charts -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-white/50 rounded-lg p-4">
                                        <div class="text-sm text-gray-600 mb-2">Ventas hoy</div>
                                        <div class="text-2xl font-bold text-purple-600">$2,450</div>
                                        <div class="h-16 flex items-end space-x-1 mt-2">
                                            <div class="w-6 bg-purple-500 rounded-t h-8"></div>
                                            <div class="w-6 bg-purple-400 rounded-t h-12"></div>
                                            <div class="w-6 bg-purple-500 rounded-t h-6"></div>
                                            <div class="w-6 bg-purple-400 rounded-t h-14"></div>
                                            <div class="w-6 bg-purple-500 rounded-t h-10"></div>
                                        </div>
                                    </div>
                                    <div class="bg-white/50 rounded-lg p-4">
                                        <div class="text-sm text-gray-600 mb-2">Clientes</div>
                                        <div class="text-2xl font-bold text-blue-600">+156</div>
                                        <div class="flex items-center mt-4">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-500 rounded-full h-2" style="width: 75%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Activity -->
                                <div class="bg-white/50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="font-semibold">Actividad reciente</span>
                                        <span class="text-xs text-purple-600">Ver todo</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-sm">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span>Nueva venta - Producto A</span>
                                            <span class="ml-auto text-gray-500">hace 2 min</span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                            <span>Nuevo cliente registrado</span>
                                            <span class="ml-auto text-gray-500">hace 15 min</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Curved bottom -->
        <div class="curve">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-sm font-semibold text-purple-600 uppercase tracking-wider">Características</span>
                <h2 class="text-4xl font-bold mt-2 mb-4">Todo lo que necesitas para crecer</h2>
                <p class="text-xl text-gray-600">Herramientas diseñadas específicamente para microemprendedores que quieren llevar su negocio al siguiente nivel.</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Análisis en tiempo real</h3>
                    <p class="text-gray-600 mb-4">Visualiza tus ventas, ingresos y métricas clave con gráficos interactivos y actualizados al instante.</p>
                    <div class="flex items-center text-purple-600 font-semibold">
                        Saber más <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Gestión de clientes</h3>
                    <p class="text-gray-600 mb-4">Administra tu cartera de clientes, historial de compras y preferencias para ofrecer un servicio personalizado.</p>
                    <div class="flex items-center text-purple-600 font-semibold">
                        Saber más <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-boxes text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Control de inventario</h3>
                    <p class="text-gray-600 mb-4">Mantén un control preciso de tu stock, recibe alertas de productos bajos y optimiza tus compras.</p>
                    <div class="flex items-center text-purple-600 font-semibold">
                        Saber más <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-file-invoice-dollar text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Facturación electrónica</h3>
                    <p class="text-gray-600 mb-4">Genera facturas profesionales automáticamente y mantén un registro organizado de todas tus transacciones.</p>
                    <div class="flex items-center text-purple-600 font-semibold">
                        Saber más <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="500">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">App móvil incluida</h3>
                    <p class="text-gray-600 mb-4">Gestiona tu negocio desde cualquier lugar con nuestra aplicación móvil completamente funcional.</p>
                    <div class="flex items-center text-purple-600 font-semibold">
                        Saber más <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="600">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-headset text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Soporte 24/7</h3>
                    <p class="text-gray-600 mb-4">Nuestro equipo de expertos está siempre disponible para ayudarte con cualquier duda o problema.</p>
                    <div class="flex items-center text-purple-600 font-semibold">
                        Saber más <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="py-20 bg-gradient-to-br from-purple-600 to-blue-600">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Stat 1 -->
                <div class="stat-card rounded-2xl p-8 text-center text-white" data-aos="zoom-in">
                    <i class="fas fa-rocket text-4xl mb-4 opacity-75"></i>
                    <div class="text-5xl font-bold mb-2">+10K</div>
                    <div class="text-lg opacity-90">Emprendedores activos</div>
                </div>
                
                <!-- Stat 2 -->
                <div class="stat-card rounded-2xl p-8 text-center text-white" data-aos="zoom-in" data-aos-delay="100">
                    <i class="fas fa-chart-line text-4xl mb-4 opacity-75"></i>
                    <div class="text-5xl font-bold mb-2">+50K</div>
                    <div class="text-lg opacity-90">Ventas mensuales</div>
                </div>
                
                <!-- Stat 3 -->
                <div class="stat-card rounded-2xl p-8 text-center text-white" data-aos="zoom-in" data-aos-delay="200">
                    <i class="fas fa-globe text-4xl mb-4 opacity-75"></i>
                    <div class="text-5xl font-bold mb-2">15</div>
                    <div class="text-lg opacity-90">Países</div>
                </div>
                
                <!-- Stat 4 -->
                <div class="stat-card rounded-2xl p-8 text-center text-white" data-aos="zoom-in" data-aos-delay="300">
                    <i class="fas fa-smile text-4xl mb-4 opacity-75"></i>
                    <div class="text-5xl font-bold mb-2">98%</div>
                    <div class="text-lg opacity-90">Satisfacción</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-sm font-semibold text-purple-600 uppercase tracking-wider">Precios</span>
                <h2 class="text-4xl font-bold mt-2 mb-4">Planes para cada etapa</h2>
                <p class="text-xl text-gray-600">Elige el plan que mejor se adapte a las necesidades de tu negocio</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Basic Plan -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-seedling text-3xl text-purple-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Básico</h3>
                        <div class="text-4xl font-bold mb-4">$0<span class="text-lg text-gray-500">/mes</span></div>
                        <p class="text-gray-600 mb-6">Perfecto para empezar</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Hasta 50 productos</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Análisis básico</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Soporte por email</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="block text-center btn-outline py-3 rounded-xl font-semibold">
                        Comenzar gratis
                    </a>
                </div>
                
                <!-- Pro Plan -->
                <div class="bg-white rounded-2xl p-8 shadow-2xl scale-105 border-2 border-purple-500 relative" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute top-0 right-0 bg-purple-600 text-white px-4 py-1 rounded-tr-2xl rounded-bl-2xl text-sm font-semibold">
                        Más popular
                    </div>
                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-fire text-3xl text-purple-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Profesional</h3>
                        <div class="text-4xl font-bold mb-4">$29<span class="text-lg text-gray-500">/mes</span></div>
                        <p class="text-gray-600 mb-6">Para negocios en crecimiento</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Productos ilimitados</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Análisis avanzado</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Soporte prioritario 24/7</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>API access</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="block text-center btn-primary py-3 rounded-xl font-semibold">
                        Comenzar prueba gratis
                    </a>
                </div>
                
                <!-- Enterprise Plan -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-crown text-3xl text-purple-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Empresarial</h3>
                        <div class="text-4xl font-bold mb-4">$99<span class="text-lg text-gray-500">/mes</span></div>
                        <p class="text-gray-600 mb-6">Para grandes operaciones</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Todo lo del plan Pro</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Múltiples usuarios</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Personalización completa</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Gerente de cuenta dedicado</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="block text-center btn-outline py-3 rounded-xl font-semibold">
                        Contactar ventas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-sm font-semibold text-purple-600 uppercase tracking-wider">Testimonios</span>
                <h2 class="text-4xl font-bold mt-2 mb-4">Lo que dicen nuestros usuarios</h2>
                <p class="text-xl text-gray-600">Miles de emprendedores ya confían en nosotros</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-gray-50 rounded-2xl p-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            MJ
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold">María José</h4>
                            <p class="text-sm text-gray-600">Emprendedora de moda</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"NexoEco ha transformado completamente mi negocio. Ahora puedo ver todas mis ventas en un solo lugar y tomar decisiones basadas en datos reales."</p>
                    <div class="flex mt-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="bg-gray-50 rounded-2xl p-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            CL
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold">Carlos López</h4>
                            <p class="text-sm text-gray-600">Tienda de tecnología</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"La gestión de inventario me ha salvado de quedarme sin stock en momentos críticos. Las alertas automáticas son increíblemente útiles."</p>
                    <div class="flex mt-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="bg-gray-50 rounded-2xl p-8" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            AG
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold">Ana García</h4>
                            <p class="text-sm text-gray-600">Artesanías online</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">"La facturación electrónica me ahorra horas de trabajo cada semana. Mis clientes aprecian recibir facturas profesionales automáticamente."</p>
                    <div class="flex mt-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-purple-600 to-blue-600 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-3xl mx-auto text-center text-white">
                <h2 class="text-4xl font-bold mb-6" data-aos="fade-up">¿Listo para hacer crecer tu negocio?</h2>
                <p class="text-xl mb-8 opacity-90" data-aos="fade-up" data-aos-delay="100">Únete a miles de emprendedores que ya están gestionando sus microemprendimientos de manera profesional.</p>
                <div class="flex flex-wrap justify-center gap-4" data-aos="fade-up" data-aos-delay="200">
                    <a href="{{ route('register') }}" class="bg-white text-purple-600 px-8 py-4 rounded-xl font-semibold text-lg inline-flex items-center hover:shadow-2xl transition">
                        <i class="fas fa-rocket mr-2"></i>
                        Comenzar ahora
                    </a>
                    <a href="#features" class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold text-lg inline-flex items-center hover:bg-white hover:text-purple-600 transition">
                        <i class="fas fa-info-circle mr-2"></i>
                        Conocer más
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-rocket text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold">NexoEco</span>
                    </div>
                    <p class="text-gray-400 mb-4">La solución completa para la gestión de microemprendimientos.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <!-- Links -->
                <div>
                    <h5 class="font-bold text-lg mb-4">Producto</h5>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Características</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition">Precios</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Demo</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">FAQ</a></li>
                    </ul>
                </div>
                
                <!-- Links -->
                <div>
                    <h5 class="font-bold text-lg mb-4">Compañía</h5>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Sobre nosotros</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Prensa</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Contacto</a></li>
                    </ul>
                </div>
                
                <!-- Links -->
                <div>
                    <h5 class="font-bold text-lg mb-4">Legal</h5>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Términos</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacidad</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Cookies</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Licencias</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-gray-800 my-8">
            
            <div class="text-center text-gray-400">
                <p>&copy; {{ date('Y') }} NexoEco. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('bg-white', 'shadow-lg');
                nav.classList.remove('bg-transparent');
            } else {
                nav.classList.remove('bg-white', 'shadow-lg');
                nav.classList.add('bg-transparent');
            }
        });
    </script>
</body>
</html>