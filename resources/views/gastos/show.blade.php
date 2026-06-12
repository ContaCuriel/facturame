<x-company-panel-layout :company="$company">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                Gasto Recibido
                <span class="px-3 py-1 text-sm rounded-full font-bold border bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 border-green-200 dark:border-green-800">
                    Completado
                </span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-300 mt-1">UUID: {{ $gasto->uuid }}</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('gastos.index', ['company_id' => $company->id]) }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-semibold transition">
                ← Volver a Gastos
            </a>
            </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-bold border-b dark:border-gray-600 pb-2 mb-4 text-gray-800 dark:text-white">Datos del Emisor (Proveedor)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-200">
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white text-base mb-1">{{ $gasto->nombre_emisor ?: 'Proveedor Desconocido' }}</p>
                        <p><strong class="dark:text-white">RFC:</strong> {{ $gasto->rfc_emisor }}</p>
                    </div>
                    <div>
                        <p><strong class="dark:text-white">Fecha de Emisión:</strong> {{ $gasto->fecha_emision->format('d/m/Y H:i A') }}</p>
                        <p><strong class="dark:text-white">Método de Pago:</strong> {{ $gasto->metodo_pago }}</p>
                        <p><strong class="dark:text-white">Forma de Pago:</strong> {{ $gasto->forma_pago }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-bold border-b dark:border-gray-600 pb-2 mb-4 text-gray-800 dark:text-white">Conceptos de la Compra</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-white">Cant</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-white">Descripción</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-white">P. Unitario</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-700 dark:text-white">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($conceptos as $item)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-4 py-3">{{ $item['cantidad'] }} <span class="text-xs text-gray-400">({{ $item['unidad'] }})</span></td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $item['descripcion'] }}</td>
                                    <td class="px-4 py-3 text-right">${{ number_format($item['precio_unitario'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">${{ number_format($item['importe'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500 italic">No se pudieron extraer los conceptos del XML.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-xs space-y-2 text-sm text-gray-600 dark:text-gray-200">
                        <div class="flex justify-between">
                            <span>Subtotal:</span> 
                            <span class="dark:text-white">${{ number_format($gasto->subtotal, 2) }}</span>
                        </div>
                        
                        {{-- IMPUESTOS TRASLADADOS (SUMAN) --}}
                        @foreach($impuestos['trasladados'] as $traslado)
                            <div class="flex justify-between text-green-600 dark:text-green-400">
                                <span>
                                    Impuesto ({{ $traslado['impuesto'] === '002' ? 'IVA' : ($traslado['impuesto'] === '003' ? 'IEPS' : $traslado['impuesto']) }} {{ (float)$traslado['tasa'] * 100 }}%):
                                </span> 
                                <span>${{ number_format($traslado['importe'], 2) }}</span>
                            </div>
                        @endforeach

                        {{-- IMPUESTOS RETENIDOS (RESTAN) --}}
                        @foreach($impuestos['retenidos'] as $retencion)
                            <div class="flex justify-between text-red-600 dark:text-red-400">
                                <span>
                                    Retención ({{ $retencion['impuesto'] === '001' ? 'ISR' : ($retencion['impuesto'] === '002' ? 'IVA' : $retencion['impuesto']) }}):
                                </span> 
                                <span>- ${{ number_format($retencion['importe'], 2) }}</span>
                            </div>
                        @endforeach

                        <hr class="dark:border-gray-600 my-2">
                        <div class="flex justify-between text-xl font-bold text-gray-900 dark:text-white mt-2">
                            <span>TOTAL:</span> 
                            <span>${{ number_format($gasto->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <p class="text-sm text-blue-800 dark:text-blue-300">
                    💡 <strong>Nota del Sistema:</strong> Este gasto fue importado automáticamente desde los servidores del SAT. Los cálculos e impuestos son extraídos directamente del archivo XML oficial.
                </p>
            </div>
        </div>
    </div>
</x-company-panel-layout>