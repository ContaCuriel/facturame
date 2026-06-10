<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nueva Empresa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                
                <form method="POST" action="{{ route('companies.store') }}">
                    @csrf

                    <!-- Razón Social -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Razón Social (Legal) *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" required placeholder="Ej. Operadora de Alimentos S.A. de C.V.">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Nombre Comercial -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Nombre Comercial (Opcional)</label>
                        <input type="text" name="commercial_name" value="{{ old('commercial_name') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" placeholder="Ej. Taquería El Pastor">
                        <p class="text-xs text-gray-500 mt-1">Si lo dejas en blanco, usaremos la Razón Social.</p>
                        <x-input-error :messages="$errors->get('commercial_name')" class="mt-2" />
                    </div>

                    <!-- RFC -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">RFC *</label>
                        <input type="text" name="rfc" value="{{ old('rfc') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm uppercase" required minlength="12" maxlength="13" placeholder="ABC123456T1">
                        <x-input-error :messages="$errors->get('rfc')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Régimen Fiscal -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Régimen Fiscal *</label>
                            <select name="fiscal_regime" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" required>
                                <option value="">Selecciona un régimen...</option>
                                @foreach($fiscalRegimes as $key => $name)
                                    <option value="{{ $key }}" {{ old('fiscal_regime') == $key ? 'selected' : '' }}>{{ $key }} - {{ $name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('fiscal_regime')" class="mt-2" />
                        </div>

                        <!-- Código Postal -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Código Postal *</label>
                            <input type="text" name="zip_code" value="{{ old('zip_code') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" required minlength="5" maxlength="5" placeholder="Ej. 12345">
                            <x-input-error :messages="$errors->get('zip_code')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-gray-100 dark:border-gray-700 pt-6">
                        <a href="{{ route('companies.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-bold mr-4 transition">Cancelar</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-md hover:-translate-y-0.5 transition-all">
                            Guardar Empresa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>