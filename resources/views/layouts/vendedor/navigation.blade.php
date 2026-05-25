<nav x-data="{ open: false }"
     class="bg-white dark:bg-[#111827] border-b border-gray-200 dark:border-gray-700 shadow-sm">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            <!-- Lado izquierdo -->
            <div class="flex items-center">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">

                    <a href="{{ route('vendedor.dashboard') }}"
                       class="block w-[220px] h-auto text-gray-900 dark:text-white">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 350 100"
                             width="100%"
                             height="100%">

                            <!-- Fondo -->
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

                            <!-- Texto -->
                            <text
                                x="100"
                                y="65"
                                font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif"
                                font-size="46"
                                font-weight="800"
                                fill="currentColor">

                                Nexo

                            </text>

                            <text
                                x="215"
                                y="65"
                                font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif"
                                font-size="46"
                                font-weight="800"
                                fill="#FF7A00">

                                Eco

                            </text>

                            <!-- Subtexto -->
                            <text
                                x="105"
                                y="85"
                                font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif"
                                font-size="12"
                                font-weight="600"
                                fill="#FF7A00"
                                letter-spacing="2">

                                COMPRA Y VENTA

                            </text>

                        </svg>

                    </a>

                </div>

                <!-- Links Desktop -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    <x-nav-link
                        :href="route('vendedor.dashboard')"
                        :active="request()->routeIs('vendedor.dashboard')">

                        Panel Vendedor

                    </x-nav-link>

                </div>

            </div>

            <!-- Lado derecho -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-3">

                <!-- Dark Mode -->
                <button
                    @click="darkMode = !darkMode"
                    class="p-2 rounded-md text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">

                    <!-- Sol -->
                    <svg
                        x-show="!darkMode"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v1m0 16v1m8.66-10h1M3 12H2m15.364 6.364l.707.707M5.929 5.929l-.707-.707m12.728 0l.707-.707M5.929 18.071l-.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"
                        />

                    </svg>

                    <!-- Luna -->
                    <svg
                        x-show="darkMode"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"
                        />

                    </svg>

                </button>

                <!-- Dropdown usuario -->
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 bg-white dark:bg-[#111827] hover:text-orange-500 transition">

                            <div>
                                {{ Auth::user()->name }}
                            </div>

                        </button>

                    </x-slot>

                    <x-slot name="content">

                        <x-dropdown-link :href="route('profile.edit')">
                            Perfil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">

                                Cerrar sesión

                            </x-dropdown-link>

                        </form>

                    </x-slot>

                </x-dropdown>

            </div>

            <!-- Botón Mobile -->
            <div class="-me-2 flex items-center sm:hidden">

                <button
                    @click="open = ! open"
                    class="p-2 rounded-md text-gray-400 dark:text-gray-500">

                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24">

                        <path
                            :class="{'hidden': open}"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{'hidden': ! open}"
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

    <!-- Menú responsive -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">

        <!-- Links -->
        <div class="pt-2 pb-3 space-y-1">

            <x-responsive-nav-link
                :href="route('vendedor.dashboard')"
                :active="request()->routeIs('vendedor.dashboard')">

                Panel Vendedor

            </x-responsive-nav-link>

        </div>

        <!-- Usuario -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">

            <div class="px-4">

                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                    {{ Auth::user()->name }}
                </div>

                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->email }}
                </div>

            </div>

            <div class="mt-3 space-y-1">

                <x-responsive-nav-link :href="route('profile.edit')">
                    Perfil
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">

                        Cerrar sesión

                    </x-responsive-nav-link>

                </form>

            </div>

        </div>

    </div>

</nav>