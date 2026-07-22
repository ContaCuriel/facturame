<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nuevo Auxiliar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form action="{{ route('auxiliares.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Nombre -->
                            <div>
                                <x-input-label for="name" :value="__('Nombre Completo')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Correo -->
                            <div>
                                <x-input-label for="email" :value="__('Correo Electrónico (Para iniciar sesión)')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <!-- Contraseña -->
                            <div>
                                <x-input-label for="password" :value="__('Contraseña')" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                <x-input-error class="mt-2" :messages="$errors->get('password')" />
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                            </div>

                            <!-- Empresas Asignadas (Multi-select con casillas) -->
                            <div class="md:col-span-2">
                                <x-input-label :value="__('Asignar a Empresa(s) / Sucursal(es)')" />
                                
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4 border border-gray-300 dark:border-gray-700 rounded-md p-4 bg-gray-50 dark:bg-gray-900 shadow-sm">
                                    @forelse($empresas as $empresa)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="companies[]" value="{{ $empresa->id }}" 
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700"
                                                {{ (is_array(old('companies')) && in_array($empresa->id, old('companies'))) ? 'checked' : '' }}>
                                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $empresa->commercial_name ?? $empresa->name }} 
                                                <span class="text-xs text-gray-500 block">(RFC: {{ $empresa->rfc }})</span>
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Aún no has registrado empresas.</p>
                                    @endforelse
                                </div>
                                
                                <x-input-error class="mt-2" :messages="$errors->get('companies')" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">El auxiliar podrá ver y operar la información de todas las empresas que selecciones.</p>
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-end">
                            <a href="{{ route('auxiliares.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 mr-4 font-medium">Cancelar</a>
                            <x-primary-button>
                                {{ __('Registrar Auxiliar') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>