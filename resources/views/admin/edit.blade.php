<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Licencia: ') }} <span class="text-indigo-600 dark:text-indigo-400">{{ $user->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form action="{{ route('admin.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Plan -->
                            <div>
                                <x-input-label for="plan" :value="__('Nombre del Paquete/Plan')" />
                                <x-text-input id="plan" name="plan" type="text" class="mt-1 block w-full" :value="old('plan', $user->plan)" placeholder="Ej. Instituto Pro" />
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div>
                                <x-input-label for="expires_at" :value="__('Fecha de Vencimiento')" />
                                <x-text-input id="expires_at" name="expires_at" type="date" class="mt-1 block w-full dark:text-gray-300" :value="old('expires_at', $user->expires_at ? \Carbon\Carbon::parse($user->expires_at)->format('Y-m-d') : '')" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Deja en blanco si es una licencia ilimitada.</p>
                            </div>

                            <!-- Límite Empresas -->
                            <div>
                                <x-input-label for="max_empresas" :value="__('Límite de Empresas Permitidas')" />
                                <x-text-input id="max_empresas" name="max_empresas" type="number" class="mt-1 block w-full" :value="old('max_empresas', $user->max_empresas)" required min="1" />
                            </div>

                            <!-- Límite Auxiliares -->
                            <div>
                                <x-input-label for="max_auxiliares" :value="__('Límite de Cajeras/Auxiliares Permitidos')" />
                                <x-text-input id="max_auxiliares" name="max_auxiliares" type="number" class="mt-1 block w-full" :value="old('max_auxiliares', $user->max_auxiliares)" required min="0" />
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-end">
                            <a href="{{ route('admin.panel') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 mr-4 font-medium">Cancelar</a>
                            <x-primary-button>
                                {{ __('Guardar Cambios') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>