@extends('layouts.comprador.app')

@section('content')
<style>
    .nexo-logo-fix img,
    .nexo-logo-fix svg {
        max-height: 38px !important;
        width: auto !important;
    }

    .nexo-market {
        background: #FAF7F2;
        min-height: 100vh;
    }

    .nexo-topbar {
        background: #fff;
        border-bottom: 1px solid #E8E0D4;
        position: sticky;
        top: 0;
        z-index: 40;
    }

    .nexo-search {
        border: 2px solid #E85D2F;
        border-radius: 999px;
        overflow: hidden;
        background: white;
    }

    .nexo-card {
        background: #fff;
        border: 1px solid #E8E0D4;
        border-radius: 22px;
        transition: .25s ease;
    }

    .nexo-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 18px 35px rgba(232,93,47,.14);
        border-color: #E85D2F;
    }

    .nexo-btn {
        background: linear-gradient(135deg, #E85D2F, #ff7b47);
        color: white;
        border-radius: 999px;
        font-weight: 900;
        transition: .25s ease;
    }

    .nexo-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(232,93,47,.28);
    }
</style>

<div class="nexo-market">

    <div class="nexo-topbar">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-5">


            <div class="nexo-search flex flex-1 items-center">
                <input
                    type="text"
                    placeholder="Buscar productos, tiendas o categorías..."
                    class="w-full border-0 focus:ring-0 px-5 py-3 rounded-l-full text-gray-700"
                >

                <button class="nexo-btn px-7 py-3 rounded-none rounded-r-full">
                    Buscar
                </button>
            </div>

            <div class="hidden md:flex items-center gap-3">
                <button class="px-4 py-2 rounded-full bg-[#FAF7F2] text-[#1C1917] font-bold">
                    🛒 Carrito
                </button>

                <button class="px-4 py-2 rounded-full bg-[#FAF7F2] text-[#1C1917] font-bold">
                    👤 Cuenta
                </button>
            </div>

        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <section class="grid lg:grid-cols-[260px_1fr] gap-6">

            <aside class="hidden lg:block">
                <div class="bg-white border border-[#E8E0D4] rounded-[26px] p-5 sticky top-24">
                    <h3 class="font-black text-[#1C1917] text-lg mb-4">
                        Categorías
                    </h3>

                    <div class="space-y-2">
                        <a class="block px-4 py-3 rounded-2xl bg-[#FFF0EA] text-[#E85D2F] font-black" href="#">🔥 Ofertas del día</a>
                        <a class="block px-4 py-3 rounded-2xl hover:bg-[#FAF7F2] font-bold" href="#">🧴 Cuidado personal</a>
                        <a class="block px-4 py-3 rounded-2xl hover:bg-[#FAF7F2] font-bold" href="#">🛍️ Moda y accesorios</a>
                        <a class="block px-4 py-3 rounded-2xl hover:bg-[#FAF7F2] font-bold" href="#">🍯 Alimentos locales</a>
                        <a class="block px-4 py-3 rounded-2xl hover:bg-[#FAF7F2] font-bold" href="#">🏠 Hogar</a>
                        <a class="block px-4 py-3 rounded-2xl hover:bg-[#FAF7F2] font-bold" href="#">🎁 Artesanías</a>
                        <a class="block px-4 py-3 rounded-2xl hover:bg-[#FAF7F2] font-bold" href="#">📦 Más vendidos</a>
                    </div>
                </div>
            </aside>

            <section>

                <div class="rounded-[32px] overflow-hidden bg-gradient-to-r from-[#E85D2F] to-[#ff7b47] p-8 md:p-10 text-white relative">
                    <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 rounded-full"></div>
                    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-white/10 rounded-full"></div>

                    <div class="relative max-w-2xl">
                        <span class="inline-block bg-white/20 px-4 py-2 rounded-full font-black text-sm mb-4">
                            Marketplace local
                        </span>

                        <h1 class="text-4xl md:text-5xl font-black leading-tight">
                            Encuentra productos únicos de emprendedores reales
                        </h1>

                        <p class="mt-4 text-orange-50 text-lg">
                            Compra fácil, compara tiendas y descubre ofertas cerca de ti dentro de NexoEco.
                        </p>

                        <button class="mt-6 bg-white text-[#E85D2F] px-7 py-3 rounded-full font-black shadow-lg">
                            Explorar ofertas
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="nexo-card p-5 text-center">
                        <div class="text-3xl">🚚</div>
                        <p class="font-black mt-2">Envíos</p>
                        <small class="text-gray-500">Rápidos y seguros</small>
                    </div>

                    <div class="nexo-card p-5 text-center">
                        <div class="text-3xl">💳</div>
                        <p class="font-black mt-2">Pagos</p>
                        <small class="text-gray-500">Protegidos</small>
                    </div>

                    <div class="nexo-card p-5 text-center">
                        <div class="text-3xl">🏪</div>
                        <p class="font-black mt-2">Tiendas</p>
                        <small class="text-gray-500">Locales</small>
                    </div>

                    <div class="nexo-card p-5 text-center">
                        <div class="text-3xl">🔥</div>
                        <p class="font-black mt-2">Ofertas</p>
                        <small class="text-gray-500">Cada día</small>
                    </div>
                </div>

                <div class="mt-10 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-[#1C1917]">
                            Productos destacados
                        </h2>
                        <p class="text-gray-500">
                            Inspirado en una experiencia tipo Amazon, Shopee y Mercado Libre.
                        </p>
                    </div>

                    <a href="#" class="hidden sm:block text-[#E85D2F] font-black hover:underline">
                        Ver todo
                    </a>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mt-6">

                    @php
                        $products = [
                            ['img' => '🧴', 'name' => 'Crema natural artesanal', 'shop' => 'Naturalia MX', 'price' => '$180', 'old' => '$230'],
                            ['img' => '👜', 'name' => 'Bolsa tejida premium', 'shop' => 'ArtMex', 'price' => '$350', 'old' => '$420'],
                            ['img' => '🌿', 'name' => 'Té orgánico local', 'shop' => 'TéVerde', 'price' => '$120', 'old' => '$150'],
                            ['img' => '🕯️', 'name' => 'Vela aromática artesanal', 'shop' => 'LuzPropia', 'price' => '$95', 'old' => '$130'],
                            ['img' => '🍯', 'name' => 'Miel natural de rancho', 'shop' => 'Dulce Campo', 'price' => '$145', 'old' => '$180'],
                            ['img' => '🎨', 'name' => 'Cuadro decorativo', 'shop' => 'EstudioK', 'price' => '$260', 'old' => '$310'],
                            ['img' => '🧁', 'name' => 'Caja de repostería', 'shop' => 'DulceFab', 'price' => '$280', 'old' => '$340'],
                            ['img' => '👕', 'name' => 'Playera bordada', 'shop' => 'Moda Local', 'price' => '$220', 'old' => '$270'],
                        ];
                    @endphp

                    @foreach ($products as $product)
                        <div class="nexo-card overflow-hidden">
                            <div class="h-44 bg-[#FFF0EA] flex items-center justify-center text-6xl">
                                {{ $product['img'] }}
                            </div>

                            <div class="p-4">
                                <div class="text-xs font-black text-[#E85D2F] bg-[#FFF0EA] inline-block px-3 py-1 rounded-full">
                                    En oferta
                                </div>

                                <h3 class="mt-3 font-black text-[#1C1917] leading-tight">
                                    {{ $product['name'] }}
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    🏪 {{ $product['shop'] }}
                                </p>

                                <div class="mt-3 flex items-end gap-2">
                                    <span class="text-2xl font-black text-[#E85D2F]">
                                        {{ $product['price'] }}
                                    </span>

                                    <span class="text-sm text-gray-400 line-through mb-1">
                                        {{ $product['old'] }}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-sm text-yellow-500 font-bold">
                                        ★★★★★
                                    </span>

                                    <span class="text-xs text-gray-400">
                                        128 vendidos
                                    </span>
                                </div>

                                <button class="nexo-btn w-full py-3 mt-4">
                                    Agregar al carrito
                                </button>
                            </div>
                        </div>
                    @endforeach

                </div>

            </section>

        </section>

    </main>
</div>
@endsection