<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexoEco - Dashboard Comprador</title>

    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg">

            <div class="p-6 border-b">

                <h2 class="text-xl font-bold">
                    NexoEco
                </h2>

                <p class="text-sm text-gray-500">
                    Menú
                </p>

            </div>


            <nav class="p-4">

                <a
                    href="/"
                    class="block p-3 rounded hover:bg-gray-100"
                >

                    ← Volver al inicio

                </a>

            </nav>

        </aside>

        <div class="flex-1">

            <!-- Barra superior -->
            <header class="bg-white shadow">

                <div class="flex items-center justify-between px-6 py-4">

                    <!-- Logo / Nombre -->
                    <div>

                        <p class="text-sm text-gray-500">
                            Panel del comprador
                        </p>

                    </div>


                    <!-- Barra de búsqueda -->

                    <div class="w-full md:w-1/3">

                        <input
                            type="text"
                            placeholder="Buscar productos o servicios..."
                            class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring"
                        >

                    </div>


                    <!-- Usuario -->

                    <div class="flex items-center gap-4">

                        <button class="border px-3 py-2 rounded">

                            Notificaciones

                        </button>

                        <div class="text-right">

                            <p class="font-semibold">
                                Usuario
                            </p>

                            <p class="text-sm text-gray-500">
                                Comprador
                            </p>

                        </div>

                    </div>

                </div>

            </header>


            <main class="p-6">

                <!-- Tarjetas resumen -->
                <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="bg-white rounded-lg shadow p-5">

                        <h2 class="text-gray-500 text-sm">
                            Pedidos realizados
                        </h2>

                        <p class="text-3xl font-bold mt-2">
                            0
                        </p>

                    </div>

                    <div class="bg-white rounded-lg shadow p-5">

                        <h2 class="text-gray-500 text-sm">
                            Servicios disponibles
                        </h2>

                        <p class="text-3xl font-bold mt-2">
                            0
                        </p>

                    </div>


                    <div class="bg-white rounded-lg shadow p-5">

                        <h2 class="text-gray-500 text-sm">
                            Productos favoritos
                        </h2>

                        <p class="text-3xl font-bold mt-2">
                            0
                        </p>

                    </div>


                    <div class="bg-white rounded-lg shadow p-5">

                        <h2 class="text-gray-500 text-sm">
                            Carrito activo
                        </h2>

                        <p class="text-3xl font-bold mt-2">
                            0
                        </p>

                    </div>

                </section>



                <!-- Actividad reciente -->

                <section class="bg-white rounded-lg shadow mt-8 p-6">

                    <h2 class="text-xl font-semibold mb-4">
                        Actividad reciente
                    </h2>

                    <div class="border-b py-3">

                        <p class="font-medium">
                            Pedido #001
                        </p>

                        <p class="text-gray-500 text-sm">
                            No hay actividad todavía
                        </p>

                    </div>

                </section>



                <!-- Recomendaciones -->

                <section class="bg-white rounded-lg shadow mt-8 p-6">

                    <h2 class="text-xl font-semibold mb-4">
                        Productos recomendados
                    </h2>

                    <ul class="space-y-2">

                        <li class="border p-3 rounded">
                            Producto ejemplo 1
                        </li>

                        <li class="border p-3 rounded">
                            Producto ejemplo 2
                        </li>

                        <li class="border p-3 rounded">
                            Producto ejemplo 3
                        </li>

                    </ul>

                </section>

            </main>

        </div>

    </div>

</body>
</html>