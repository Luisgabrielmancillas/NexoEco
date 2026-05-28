<x-app-layout>

    <x-slot name="header">

        <div class="flex justify-between items-center">

            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white">
                    Dashboard Administrativo
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Panel de control de administradores
                </p>
            </div>

            <!-- NUEVO USUARIO -->
            <button
                onclick="openCreateModal()"
                class="bg-indigo-600 hover:bg-indigo-700 transition-all duration-300
                text-white px-5 py-3 rounded-2xl shadow-lg hover:scale-105">

                + Nuevo Usuario

            </button>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- ALERTAS -->
            @if(session('success'))

                <div class="bg-green-100 border border-green-300
                    text-green-700 px-6 py-4 rounded-2xl shadow">

                    {{ session('success') }}

                </div>

            @endif

            @if(session('error'))

                <div class="bg-red-100 border border-red-300
                    text-red-700 px-6 py-4 rounded-2xl shadow">

                    {{ session('error') }}

                </div>

            @endif

            <!-- STATS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- TOTAL -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md
                    hover:shadow-2xl transition duration-300 hover:-translate-y-1">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                Total Usuarios
                            </p>

                            <h2 class="text-3xl font-bold text-gray-800 dark:text-white mt-2">
                                {{ $users->count() }}
                            </h2>

                        </div>

                        <div class="bg-indigo-100 dark:bg-indigo-900 p-4 rounded-xl">
                            👥
                        </div>

                    </div>

                </div>

                <!-- ACTIVOS -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md
                    hover:shadow-2xl transition duration-300 hover:-translate-y-1">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                Usuarios Activos
                            </p>

                            <h2 class="text-3xl font-bold text-green-500 mt-2">
                                {{ $users->where('activo', true)->count() }}
                            </h2>

                        </div>

                        <div class="bg-green-100 dark:bg-green-900 p-4 rounded-xl">
                            ✅
                        </div>

                    </div>

                </div>

                <!-- INACTIVOS -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md
                    hover:shadow-2xl transition duration-300 hover:-translate-y-1">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                Usuarios Inactivos
                            </p>

                            <h2 class="text-3xl font-bold text-red-500 mt-2">
                                {{ $users->where('activo', false)->count() }}
                            </h2>

                        </div>

                        <div class="bg-red-100 dark:bg-red-900 p-4 rounded-xl">
                            🚫
                        </div>

                    </div>

                </div>

            </div>

            <!-- TABLA -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">

                <!-- HEADER -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">

                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        Usuarios Registrados
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Administración del sistema
                    </p>

                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">

                            <tr>

                                <th class="px-6 py-4 text-left">
                                    ID
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Usuario
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Email
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Rol
                                </th>

                                <th class="px-6 py-4 text-left">
                                    Estado
                                </th>

                                <th class="px-6 py-4 text-center">
                                    Acciones
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($users as $user)

                                <tr class="border-b dark:border-gray-700
                                    hover:bg-gray-50 dark:hover:bg-gray-700
                                    transition duration-200">

                                    <!-- ID -->
                                    <td class="px-6 py-4 font-semibold text-gray-800 dark:text-white">
                                        #{{ $user->id }}
                                    </td>

                                    <!-- USER -->
                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="w-10 h-10 rounded-full bg-indigo-500
                                                flex items-center justify-center text-white font-bold">

                                                {{ strtoupper(substr($user->name,0,1)) }}

                                            </div>

                                            <div>

                                                <p class="font-semibold text-gray-800 dark:text-white">
                                                    {{ $user->name }}
                                                </p>

                                                <p class="text-xs text-gray-500">
                                                    {{ $user->nombre_completo }}
                                                </p>

                                            </div>

                                        </div>

                                    </td>

                                    <!-- EMAIL -->
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                        {{ $user->email }}
                                    </td>

                                    <!-- ROL -->
                                    <td class="px-6 py-4">

                                        <span class="bg-indigo-100 text-indigo-700
                                            px-3 py-1 rounded-full text-xs font-semibold">

                                            {{ optional($user->tipos_usuario->first())->nombre_tipo ?? 'Sin rol' }}

                                        </span>

                                    </td>

                                    <!-- STATUS -->
                                    <td class="px-6 py-4">

                                        @if($user->activo)

                                            <span class="bg-green-100 text-green-700
                                                px-3 py-1 rounded-full text-xs font-semibold">

                                                Activo

                                            </span>

                                        @else

                                            <span class="bg-red-100 text-red-700
                                                px-3 py-1 rounded-full text-xs font-semibold">

                                                Inactivo

                                            </span>

                                        @endif

                                    </td>

                                    <!-- ACTIONS -->
                                    <td class="px-6 py-4">

                                        <div class="flex justify-center gap-3">

                                            <!-- EDITAR -->
                                            <button
                                                onclick="openEditModal(
                                                    {{ $user->id }},
                                                    '{{ $user->name }}',
                                                    '{{ $user->email }}'
                                                )"

                                                class="bg-blue-500 hover:bg-blue-600
                                                text-white px-4 py-2 rounded-xl
                                                transition-all duration-300 hover:scale-105">

                                                ✏️ Editar

                                            </button>

                                            <!-- ACTIVAR / DESACTIVAR -->
                                            @if($user->id !== auth()->id())

                                                @if($user->activo)

                                                    <button
                                                        onclick="openModal(
                                                            {{ $user->id }},
                                                            '{{ $user->name }}',
                                                            'desactivar'
                                                        )"

                                                        class="bg-red-500 hover:bg-red-600
                                                        text-white px-4 py-2 rounded-xl
                                                        transition-all duration-300 hover:scale-105">

                                                        🚫 Desactivar

                                                    </button>

                                                @else

                                                    <button
                                                        onclick="openModal(
                                                            {{ $user->id }},
                                                            '{{ $user->name }}',
                                                            'activar'
                                                        )"

                                                        class="bg-green-500 hover:bg-green-600
                                                        text-white px-4 py-2 rounded-xl
                                                        transition-all duration-300 hover:scale-105">

                                                        ✅ Reactivar

                                                    </button>

                                                @endif

                                            @else

                                                <button
                                                    disabled
                                                    class="bg-gray-400
                                                    cursor-not-allowed
                                                    text-white px-4 py-2 rounded-xl">

                                                    Sesión activa

                                                </button>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="py-16 text-center">

                                        <div class="flex flex-col items-center justify-center">

                                            <div class="text-6xl mb-4">
                                                📭
                                            </div>

                                            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">
                                                No hay usuarios
                                            </h3>

                                            <p class="text-gray-500 mt-2">
                                                Aún no existen usuarios registrados
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <!-- MODAL ACTIVAR/DESACTIVAR -->
    <div id="deleteModal"
         class="fixed inset-0 bg-black/50 hidden
         items-center justify-center z-50">

        <div class="bg-white dark:bg-gray-800
            rounded-3xl p-8 w-full max-w-md
            shadow-2xl">

            <div class="text-center">

                <div class="text-6xl mb-4">
                    ⚙️
                </div>

                <h2 class="text-2xl font-bold
                    text-gray-800 dark:text-white mb-3">

                    Acción Usuario

                </h2>

                <p class="text-gray-600 dark:text-gray-300 mb-6"></p>

            </div>

            <form id="deleteForm" method="POST">

                @csrf
                @method('DELETE')

                <div class="flex justify-center gap-3">

                    <button
                        type="button"
                        onclick="closeModal()"
                        class="px-5 py-3 rounded-xl
                        bg-gray-200 hover:bg-gray-300">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="px-5 py-3 rounded-xl
                        text-white transition">

                        Confirmar

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- MODAL CREATE -->
    <div id="createModal"
         class="fixed inset-0 bg-black/50 hidden
         items-center justify-center z-50">

        <div class="bg-white dark:bg-gray-800
            rounded-3xl p-8 w-full max-w-lg
            shadow-2xl">

            <div class="flex justify-between items-center mb-6">

                <h2 class="text-2xl font-bold
                    text-gray-800 dark:text-white">

                    Crear Usuario

                </h2>

                <button onclick="closeCreateModal()"
                    class="text-gray-500 hover:text-red-500 text-2xl">

                    ×

                </button>

            </div>

            <form action="{{ route('admin.users.store') }}"
                  method="POST">

                @csrf

                <div class="space-y-5">

                    <div>

                        <label class="block mb-2 font-semibold">
                            Nombre
                        </label>

                        <input
                            type="text"
                            name="name"
                            required
                            class="w-full rounded-xl border-gray-300
                            dark:border-gray-700 dark:bg-gray-900
                            dark:text-white">

                    </div>

                    <div>

                        <label class="block mb-2 font-semibold">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            required
                            class="w-full rounded-xl border-gray-300
                            dark:border-gray-700 dark:bg-gray-900
                            dark:text-white">

                    </div>

                    <div>

                        <label class="block mb-2 font-semibold">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full rounded-xl border-gray-300
                            dark:border-gray-700 dark:bg-gray-900
                            dark:text-white">

                    </div>

                    <div class="flex justify-end gap-3 pt-4">

                        <button
                            type="button"
                            onclick="closeCreateModal()"
                            class="px-5 py-3 rounded-xl bg-gray-200">

                            Cancelar

                        </button>

                        <button
                            type="submit"
                            class="px-5 py-3 rounded-xl
                            bg-indigo-600 hover:bg-indigo-700
                            text-white">

                            Crear Usuario

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- MODAL EDIT -->
    <div id="editModal"
         class="fixed inset-0 bg-black/50 hidden
         items-center justify-center z-50">

        <div class="bg-white dark:bg-gray-800
            rounded-3xl p-8 w-full max-w-lg
            shadow-2xl">

            <div class="flex justify-between items-center mb-6">

                <h2 class="text-2xl font-bold
                    text-gray-800 dark:text-white">

                    Editar Usuario

                </h2>

                <button onclick="closeEditModal()"
                    class="text-gray-500 hover:text-red-500 text-2xl">

                    ×

                </button>

            </div>

            <form id="editForm" method="POST">

                @csrf
                @method('PUT')

                <div class="space-y-5">

                    <div>

                        <label class="block mb-2 font-semibold">
                            Nombre
                        </label>

                        <input
                            id="editName"
                            type="text"
                            name="name"
                            required
                            class="w-full rounded-xl border-gray-300
                            dark:border-gray-700 dark:bg-gray-900
                            dark:text-white">

                    </div>

                    <div>

                        <label class="block mb-2 font-semibold">
                            Email
                        </label>

                        <input
                            id="editEmail"
                            type="email"
                            name="email"
                            required
                            class="w-full rounded-xl border-gray-300
                            dark:border-gray-700 dark:bg-gray-900
                            dark:text-white">

                    </div>

                    <div class="flex justify-end gap-3 pt-4">

                        <button
                            type="button"
                            onclick="closeEditModal()"
                            class="px-5 py-3 rounded-xl bg-gray-200">

                            Cancelar

                        </button>

                        <button
                            type="submit"
                            class="px-5 py-3 rounded-xl
                            bg-blue-600 hover:bg-blue-700
                            text-white">

                            Actualizar Usuario

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- JS -->
    <script>

        // ACTIVAR / DESACTIVAR
        function openModal(id, name, actionType)
        {
            const modal = document.getElementById('deleteModal');

            const form = document.getElementById('deleteForm');

            form.action = `/admin/users/${id}`;

            const title =
                document.querySelector('#deleteModal h2');

            const text =
                document.querySelector('#deleteModal p');

            const submitBtn =
                form.querySelector('button[type="submit"]');

            submitBtn.classList.remove(
                'bg-red-500',
                'hover:bg-red-600',
                'bg-green-500',
                'hover:bg-green-600'
            );

            if(actionType === 'activar')
            {
                title.innerText = 'Reactivar Usuario';

                text.innerHTML =
                    `¿Seguro que deseas reactivar a
                    <span class="font-bold">${name}</span>?`;

                submitBtn.innerText = 'Sí, reactivar';

                submitBtn.classList.add(
                    'bg-green-500',
                    'hover:bg-green-600'
                );
            }
            else
            {
                title.innerText = 'Desactivar Usuario';

                text.innerHTML =
                    `¿Seguro que deseas desactivar a
                    <span class="font-bold">${name}</span>?`;

                submitBtn.innerText = 'Sí, desactivar';

                submitBtn.classList.add(
                    'bg-red-500',
                    'hover:bg-red-600'
                );
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal()
        {
            const modal = document.getElementById('deleteModal');

            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        // CREATE
        function openCreateModal()
        {
            document.getElementById('createModal')
                .classList.replace('hidden', 'flex');
        }

        function closeCreateModal()
        {
            document.getElementById('createModal')
                .classList.replace('flex', 'hidden');
        }

        // EDIT
        function openEditModal(id, name, email)
        {
            const modal = document.getElementById('editModal');

            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;

            document.getElementById('editForm').action =
                `/admin/users/${id}`;

            modal.classList.replace('hidden', 'flex');
        }

        function closeEditModal()
        {
            document.getElementById('editModal')
                .classList.replace('flex', 'hidden');
        }

    </script>

</x-app-layout>