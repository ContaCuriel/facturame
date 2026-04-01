<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Configuración de: {{ $company->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alerta de éxito --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-600 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium">Credenciales de Facturama</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Ingresa las llaves de API que obtuviste de tu cuenta de Facturama.
                    </p>

                    <form method="POST" action="{{ route('companies.config.update', $company) }}" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <label for="facturama_api_key" class="block font-medium text-sm text-gray-700 dark:text-gray-300">API Key</label>
                            <input id="facturama_api_key" name="facturama_api_key" type="text" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('facturama_api_key', $company->facturama_api_key) }}" required>
                        </div>

                        <div>
                            <label for="facturama_secret_key" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Secret Key</label>
                            <input id="facturama_secret_key" name="facturama_secret_key" type="password" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                            <small class="text-gray-500 dark:text-gray-400">La Secret Key no se muestra por seguridad. Ingresa una nueva para actualizarla.</small>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>