{{-- Ruta del archivo: resources/views/companies/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mis Empresas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="flex justify-end mb-4">
                        <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300">
                            Crear Empresa
                        </a>
                    </div>

                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($companies as $company)
                            <li class="py-4 flex justify-between items-center">
                                <div>
                                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $company->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">RFC: {{ $company->rfc }}</p>
                                </div>
                                
                                {{-- ✅ --- CORRECCIÓN FINAL AQUÍ --- ✅ --}}
                                {{-- El enlace ahora apunta a la ruta 'companies.show' --}}
                                <a href="{{ route('companies.show', $company) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:text-indigo-300 font-semibold">
                                    Entrar al Dashboard
                                </a>
                            </li>
                        @empty
                            <li class="py-4 text-center text-gray-500 dark:text-gray-400">
                                Aún no has registrado ninguna empresa.
                            </li>
                        @endforelse
                    </ul>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
