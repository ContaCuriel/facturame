<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Súper Administrador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Gestión de Clientes y Licencias</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Límite Empresas</th>
                                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($clientes as $cliente)
                                    <tr>
                                        <td class="py-4 px-4 text-sm font-medium text-gray-900">{{ $cliente->name }}</td>
                                        <td class="py-4 px-4 text-sm text-gray-500">{{ $cliente->email }}</td>
                                        <td class="py-4 px-4 text-sm text-center text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $cliente->plan ?? 'Sin Plan' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-sm text-center text-gray-500">{{ $cliente->max_empresas }}</td>
                                        <td class="py-4 px-4 text-sm text-center text-gray-500">
                                            {{ $cliente->expires_at ? \Carbon\Carbon::parse($cliente->expires_at)->format('d/m/Y') : 'Ilimitado' }}
                                        </td>
                                        <td class="py-4 px-4 text-sm text-center font-medium">
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900">Editar Licencia</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                                            Aún no tienes clientes registrados en el sistema.
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