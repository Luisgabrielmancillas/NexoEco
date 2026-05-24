<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Panel - Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- PANEL PRINCIPAL -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    👋 Bienvenido al panel de administración
                </div>
            </div>

            <!-- CRUD USERS -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            👥 Administradores Registrados
                        </h3>

                        <a href="#"
                           class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            + Nuevo usuario
                        </a>
                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-300">

                            <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Nombre</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($users as $user)
                                    <tr class="border-b dark:border-gray-700">

                                        <td class="px-4 py-3">
                                            {{ $user->id }}
                                        </td>

                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $user->email }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $user->tipos_usuario->first()->nombre_tipo ?? 'Sin rol' }}
                                        </td>

                                        <td class="px-4 py-3 text-right space-x-2">

                                            <a href="#" class="text-blue-500 hover:underline">
                                                Editar
                                            </a>

                                            <a href="#" class="text-green-500 hover:underline">
                                                Ver
                                            </a>

                                            <a href="#" class="text-red-500 hover:underline">
                                                Eliminar
                                            </a>

                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-6 text-gray-400">
                                            No hay usuarios registrados
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>