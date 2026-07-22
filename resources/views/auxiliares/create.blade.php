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

                            <!-- Empresa Asignada -->
                            <div class="md:col-span-2">
                                <x-input-label for="company_id" :value="__('Asignar a Empresa/Sucursal')" />
                                <select id="company_id" name="company_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Selecciona una empresa --</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ old('company_id') == $empresa->id ? 'selected' : '' }}>
                                            {{ $empresa->commercial_name ?? $empresa->name }} (RFC: {{ $empresa->rfc }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('company_id')" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">El auxiliar solo podrá ver y operar la información de la empresa seleccionada.</p>
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