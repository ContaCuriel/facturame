<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nueva Empresa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('companies.store') }}">
                        @csrf

                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Razón Social</label>
                            <input id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" type="text" name="name" required autofocus />
                        </div>

                        <div class="mt-4">
                            <label for="rfc" class="block font-medium text-sm text-gray-700">RFC</label>
                            <input id="rfc" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" type="text" name="rfc" required />
                        </div>

                        <div class="mt-4">
    <label for="fiscal_regime" class="block font-medium text-sm text-gray-700">Régimen Fiscal</label>
    <select id="fiscal_regime" name="fiscal_regime" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        <option value="" disabled selected>-- Selecciona una opción --</option>
        @foreach ($fiscalRegimes as $code => $name)
            <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
        @endforeach
    </select>
</div>

                        <div class="mt-4">
                            <label for="zip_code" class="block font-medium text-sm text-gray-700">Código Postal (Domicilio Fiscal)</label>
                            <input id="zip_code" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" type="text" name="zip_code" required />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Guardar Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>