<nav x-data="{ open: false }" class="bg-white border-b border-[#E8E0D4] shadow-sm">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-[86px]">

            <!-- LEFT -->
            <div class="flex items-center">

                <!-- CUSTOM LOGO -->
                <div class="shrink-0 flex items-center">

                    <a href="{{ route('comprador.dashboard') }}" class="flex items-center">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 350 100"
                             class="h-[58px] w-auto">

                            <!-- Fondo transparente -->
                            <rect width="350" height="100" fill="transparent"/>

                            <!-- Isotipo -->
                            <g transform="translate(15, 15)">

                                <!-- Flecha 1 -->
                                <path
                                    d="M 20 40 L 40 20 L 60 40 L 48 40 L 48 60 L 32 60 L 32 40 Z"
                                    fill="#FF7A00"
                                    transform="rotate(45 40 40)"
                                />

                                <!-- Flecha 2 -->
                                <path
                                    d="M 20 40 L 40 20 L 60 40 L 48 40 L 48 60 L 32 60 L 32 40 Z"
                                    fill="#FFFFFF"
                                    stroke="#FF7A00"
                                    stroke-width="3"
                                    transform="rotate(225 40 40)"
                                />

                            </g>

                            <!-- TEXTO -->
                            <text
                                x="100"
                                y="65"
                                font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif"
                                font-size="46"
                                font-weight="800"
                                fill="#1C1917"
                            >
                                Nexo
                            </text>

                            <text
                                x="215"
                                y="65"
                                font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif"
                                font-size="46"
                                font-weight="800"
                                fill="#FF7A00"
                            >
                                Eco
                            </text>

                            <!-- ESLOGAN -->
                            <text
                                x="105"
                                y="85"
                                font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif"
                                font-size="12"
                                font-weight="700"
                                fill="#FF7A00"
                                letter-spacing="2"
                            >
                                COMPRA Y VENTA
                            </text>

                        </svg>

                    </a>

                </div>

                <!-- NAVIGATION -->
                <div class="hidden sm:flex sm:items-center sm:ms-10 space-x-8">

                    <x-nav-link
                        :href="route('comprador.dashboard')"
                        :active="request()->routeIs('comprador.dashboard')"
                    >
                        {{ __('Inicio') }}
                    </x-nav-link>

                </div>

            </div>

            <!-- RIGHT -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center px-4 py-2 rounded-full bg-[#FAF7F2] text-gray-700 hover:bg-[#FFF0EA] transition duration-200 font-bold"
                        >

                            <div>
                                {{ Auth::user()->name }}
                            </div>

                            <div class="ms-2">

                                <svg
                                    class="fill-current h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                >

                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />

                                </svg>

                            </div>

                        </button>

                    </x-slot>

                    <x-slot name="content">

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                            >
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>

                        </form>

                    </x-slot>

                </x-dropdown>

            </div>

            <!-- MOBILE BUTTON -->
            <div class="-me-2 flex items-center sm:hidden">

                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:bg-[#FAF7F2] transition"
                >

                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >

                        <path
                            :class="{'hidden': open, 'inline-flex': ! open }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{'hidden': ! open, 'inline-flex': open }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />

                    </svg>

                </button>

            </div>

        </div>

    </div>

    <!-- MOBILE MENU -->
    <div
        :class="{'block': open, 'hidden': ! open}"
        class="hidden sm:hidden bg-white border-t border-[#E8E0D4]"
    >

        <div class="pt-2 pb-3 space-y-1">

            <x-responsive-nav-link
                :href="route('comprador.dashboard')"
                :active="request()->routeIs('comprador.dashboard')"
            >
                {{ __('Inicio') }}
            </x-responsive-nav-link>

        </div>

        <!-- MOBILE PROFILE -->
        <div class="pt-4 pb-1 border-t border-[#E8E0D4]">

            <div class="px-4">

                <div class="font-bold text-base text-gray-800">
                    {{ Auth::user()->name }}
                </div>

                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->email }}
                </div>

            </div>

            <div class="mt-3 space-y-1">

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>

                </form>

            </div>

        </div>

    </div>

</nav>