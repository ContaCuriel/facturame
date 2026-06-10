{{-- Ruta del archivo: resources/views/companies/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Mis Empresas') }}
            </h2>
            <a href="{{ route('companies.create') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Crear Empresa
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ search: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($companies->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center transition-all duration-300 hover:shadow-md">
                    <div class="w-24 h-24 mx-auto bg-indigo-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-12 h-12 text-indigo-400 dark:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Aún no hay empresas registradas</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">Comienza registrando tu primera empresa o sucursal. Necesitarás su RFC y datos fiscales para empezar a facturar.</p>
                    <a href="{{ route('companies.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-lg hover:-translate-y-1">
                        Registrar mi primera empresa
                    </a>
                </div>
            @else
                <div class="mb-8 relative max-w-2xl mx-auto lg:mx-0">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="search" placeholder="Buscar por razón social o RFC..." class="block w-full pl-11 pr-4 py-3.5 border-gray-200 dark:border-gray-700 rounded-2xl leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($companies as $company)
                        <div x-show="search === '' || '{{ mb_strtolower($company->name . ' ' . $company->rfc) }}'.includes(search.toLowerCase())" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col group relative overflow-hidden cursor-default"
                             style="display: none;" x-init="$el.style.display = 'flex'">
                            
                            <div class="h-2 w-full bg-gradient-to-r from-indigo-500 to-purple-600"></div>

                            <div class="p-6 flex-grow flex flex-col items-center text-center">
                                
                                <div class="w-20 h-20 mb-5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center border-2 border-indigo-100 dark:border-indigo-800 overflow-hidden shadow-inner relative group-hover:scale-105 transition-transform">
                                    @if($company->logo_path)
                                        <img src="{{ Storage::url($company->logo_path) }}" alt="{{ $company->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                                            {{ substr($company->name, 0, 2) }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 leading-tight" title="{{ $company->name }}">
                                    {{ $company->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-4 bg-gray-100 dark:bg-gray-700/50 px-3 py-1 rounded-md inline-block">
                                    RFC: <span class="text-gray-800 dark:text-gray-200 font-bold">{{ $company->rfc }}</span>
                                </p>
                                
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/50">
                                    <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span> Sistema Activo
                                </span>
                            </div>

                            <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4 mt-auto">
                                <a href="{{ route('companies.show', $company) }}" class="w-full flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors">
                                    Entrar al Workspace
                                    <svg class="ml-2 w-4 h-4 group-hover:translate-x-1.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>