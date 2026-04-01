<x-company-panel-layout :company="$company">
    {{-- Encabezado del Dashboard --}}
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">
        Dashboard de {{ $company->name }}
    </h1>

    {{-- Widgets de Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Widget: Ingresos del Mes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos del Mes</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                ${{ number_format($monthlyRevenue, 2) }}
            </p>
        </div>

        <!-- Widget: Facturas del Mes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Facturas del Mes</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $invoiceCount }}
            </p>
        </div>

        <!-- Widget: Total de Clientes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Clientes</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $totalClients }}
            </p>
        </div>
    </div>

    {{-- Tabla de Facturas Recientes --}}
    <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h3 class="text-lg font-semibold mb-4">Facturas Recientes</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Folio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentInvoices as $invoice)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->series }}-{{ $invoice->folio }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $invoice->client->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $invoice->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">${{ number_format($invoice->total, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->status === 'issued' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        Timbrada
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay facturas recientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-company-panel-layout>