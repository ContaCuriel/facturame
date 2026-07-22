<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Mis Cajeras / Auxiliares') }}
            </h2>
            <a href="{{ route('auxiliares.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                + Nuevo Auxiliar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alertas de éxito o error de límite -->
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Licencia actual: <span class="font-bold">{{ $owner->plan ?? 'Básica' }}</span> | 
                        Auxiliares en uso: <span class="font-bold">{{ $auxiliares->count() }} / {{ $owner->max_auxiliares }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Empresa Asignada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($auxiliares as $auxiliar)
                                    <tr>
                                        <td class="py-4 px-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $auxiliar->name }}</td>
                                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">{{ $auxiliar->email }}</td>
                                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">
                                            @foreach($auxiliar->assignedCompanies as $company)
                                                <span class="inline-block bg-gray-200 dark:bg-gray-700 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300 mr-2">
                                                    {{ $company->commercial_name ?? $company->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">
                                            Aún no has registrado ningún auxiliar.
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