<x-company-panel-layout :company="$company">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            📥 Facturas Recibidas (Gastos)
        </h1>
        
        <button onclick="alert('La descarga automática ocurre todos los días a las 5:00 AM.')" class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-600 transition">
            🔄 Sincronización Automática
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">UUID / Folio Fiscal</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Impuestos</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($gastos as $gasto)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $gasto->fecha_emision->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    <span class="font-bold block">{{ $gasto->nombre_emisor ?: 'Proveedor Desconocido' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">RFC: {{ $gasto->rfc_emisor }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span title="{{ $gasto->uuid }}">{{ substr($gasto->uuid, 0, 13) }}...</span>
                                    <span class="block text-xs mt-1">Uso: {{ $gasto->uso_cfdi }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">
                                    ${{ number_format($gasto->subtotal, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">
                                    @php $impuestos = $gasto->impuestos_trasladados - $gasto->impuestos_retenidos; @endphp
                                    <span class="{{ $impuestos > 0 ? 'text-green-600 dark:text-green-400' : '' }}">
                                        ${{ number_format($impuestos, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                                    ${{ number_format($gasto->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    @if(strtolower($gasto->estado) === 'vigente')
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border border-green-200 dark:border-green-800">
                                            Vigente
                                        </span>
                                    @else
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-200 dark:border-red-800">
                                            Cancelado
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <p class="text-4xl mb-3">📭</p>
                                    <p class="text-lg font-bold text-gray-700 dark:text-gray-300">Aún no hay gastos registrados.</p>
                                    <p class="text-sm mt-1">El robot sincronizará tus facturas recibidas automáticamente a las 5:00 AM.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $gastos->appends(['company_id' => $company->id])->links() }}
            </div>
            
        </div>
    </div>
</x-company-panel-layout>