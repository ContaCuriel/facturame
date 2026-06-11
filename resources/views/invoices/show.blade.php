<x-company-panel-layout :company="$invoice->company">
    
    {{-- Encabezado y Botones de Acción Globales --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                Factura {{ $invoice->series }}-{{ $invoice->folio }}
                
                @php
                    $colorClass = match($invoice->status) {
                        'draft' => 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-500',
                        'issued' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 border-green-200 dark:border-green-800',
                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100 border-red-200 dark:border-red-800',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white'
                    };
                @endphp
                <span class="px-3 py-1 text-sm rounded-full font-bold border {{ $colorClass }}">
                    {{ $invoice->status_es }}
                </span>
            </h1>
            @if($invoice->uuid)
                <p class="text-sm text-gray-500 dark:text-gray-300 mt-1">UUID: {{ $invoice->uuid }}</p>
            @endif
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('invoices.index', ['company_id' => $invoice->company_id]) }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-semibold transition">
                ← Volver
            </a>
            
            @if($invoice->status === 'issued')
                <a href="{{ route('invoices.pdf', $invoice) }}" class="px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-900 dark:text-indigo-100 dark:border-indigo-600 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-800 text-sm font-bold transition">
                    📄 Descargar PDF
                </a>
                <a href="{{ route('invoices.xml', $invoice) }}" class="px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-900 dark:text-indigo-100 dark:border-indigo-600 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-800 text-sm font-bold transition">
                    💻 Descargar XML
                </a>
            @elseif($invoice->status === 'draft')
                <a href="{{ route('invoices.edit', $invoice) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-bold transition shadow-md">
                    ✏️ Continuar Edición
                </a>
            @endif
        </div>
    </div>

    {{-- Cuadrícula principal dividida --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- COLUMNA IZQUIERDA: Detalles de la Factura (70%) --}}
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-bold border-b dark:border-gray-600 pb-2 mb-4 text-gray-800 dark:text-white">Datos del Receptor</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-200">
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white text-base mb-1">{{ $invoice->client->name ?? 'Cliente Eliminado' }}</p>
                        <p><strong class="dark:text-white">RFC:</strong> {{ $invoice->client->rfc ?? 'N/A' }}</p>
                        <p><strong class="dark:text-white">C.P.:</strong> {{ $invoice->client->zip_code ?? 'N/A' }}</p>
                        <p><strong class="dark:text-white">Régimen:</strong> {{ $invoice->client->fiscal_regime ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p><strong class="dark:text-white">Fecha de Emisión:</strong> {{ $invoice->created_at->format('d/m/Y H:i A') }}</p>
                        <p><strong class="dark:text-white">Uso de CFDI:</strong> {{ $invoice->client->cfdi_use ?? 'N/A' }}</p>
                        <p><strong class="dark:text-white">Método de Pago:</strong> {{ $invoice->payment_method }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-bold border-b dark:border-gray-600 pb-2 mb-4 text-gray-800 dark:text-white">Conceptos</h2>
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
                            @foreach ($invoice->items as $item)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-4 py-3">{{ $item['quantity'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="block text-gray-900 dark:text-white">{{ $item['description'] }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Clave SAT: {{ $item['sat_product_key'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">${{ number_format($item['price'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">${{ number_format($item['total'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-xs space-y-2 text-sm text-gray-600 dark:text-gray-200">
                        <div class="flex justify-between"><span>Subtotal:</span> <span class="dark:text-white">${{ number_format($invoice->subtotal, 2) }}</span></div>
                        @if($invoice->taxes > 0)
                            <div class="flex justify-between text-green-600 dark:text-green-400"><span>Impuestos Trasladados:</span> <span>${{ number_format($invoice->taxes, 2) }}</span></div>
                        @endif
                        @if($invoice->taxes < 0)
                            <div class="flex justify-between text-red-600 dark:text-red-400"><span>Impuestos Retenidos:</span> <span>${{ number_format(abs($invoice->taxes), 2) }}</span></div>
                        @endif
                        <hr class="dark:border-gray-600 my-2">
                        <div class="flex justify-between text-xl font-bold text-gray-900 dark:text-white mt-2">
                            <span>TOTAL:</span> <span>${{ number_format($invoice->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Dashboard Financiero (30%) --}}
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-bold border-b dark:border-gray-600 pb-2 mb-4 text-gray-800 dark:text-white">Estado de Pago</h2>
                
                @if($invoice->status === 'draft')
                    <div class="text-center py-6 text-gray-500 dark:text-gray-300">
                        <p class="text-3xl">🕒</p>
                        <p class="mt-2 text-sm">El panel de pagos se activará cuando la factura sea timbrada.</p>
                    </div>
                @elseif($invoice->status === 'cancelled')
                    <div class="text-center py-6 text-red-600 dark:text-red-400">
                        <p class="text-4xl">❌</p>
                        <p class="mt-2 font-bold">Factura Cancelada</p>
                    </div>
                @elseif($invoice->payment_method === 'PUE')
                    <div class="text-center py-6 text-green-600 dark:text-green-400">
                        <p class="text-4xl">✅</p>
                        <p class="mt-2 font-bold text-gray-900 dark:text-white">PUE - Pagado en una sola exhibición</p>
                        <p class="text-sm mt-1">No requiere complementos de pago.</p>
                    </div>
                @elseif($invoice->payment_method === 'PPD')
                    {{-- LÓGICA DE BARRA DE PROGRESO PPD --}}
                    @php
                        $totalPaid = $invoice->payments->where('status', '!=', 'cancelled')->sum('amount');
                        $pending = max(0, $invoice->total - $totalPaid);
                        $percentage = ($invoice->total > 0) ? ($totalPaid / $invoice->total) * 100 : 0;
                        $isFullyPaid = $totalPaid >= ($invoice->total - 0.01);
                    @endphp

                    <div class="space-y-4">
                        <div class="flex justify-between text-sm text-gray-700 dark:text-gray-200">
                            <span>Cobrado: <strong class="dark:text-white">${{ number_format($totalPaid, 2) }}</strong></span>
                            <span class="{{ $isFullyPaid ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">
                                Saldo: ${{ number_format($pending, 2) }}
                            </span>
                        </div>
                        
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-4 overflow-hidden border border-gray-300 dark:border-gray-600">
                            <div class="h-3 rounded-full transition-all duration-500 ease-in-out {{ $isFullyPaid ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ min(100, $percentage) }}%"></div>
                        </div>

                        @if($isFullyPaid)
                            <div class="p-3 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-100 rounded text-center text-sm font-bold">
                                🎉 ¡Factura liquidada!
                            </div>
                        @else
                            <a href="{{ route('payments.index', $invoice) }}" class="block w-full text-center px-4 py-2 bg-blue-600 dark:bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:hover:bg-blue-500 text-sm font-bold transition shadow">
                                💳 Registrar Pago (REP)
                            </a>
                        @endif
                    </div>

                    <div class="mt-8">
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider mb-4">Historial de Pagos</h3>
                        
                        @if($invoice->payments->where('status', '!=', 'cancelled')->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic text-center">Aún no se han registrado pagos.</p>
                        @else
                            <div class="relative border-l-2 border-gray-200 dark:border-gray-600 ml-3 space-y-4">
                                @foreach($invoice->payments->where('status', '!=', 'cancelled') as $payment)
                                    <div class="relative pl-6">
                                        <span class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-blue-500 border-2 border-white dark:border-gray-800"></span>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white">${{ number_format($payment->amount, 2) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-300">Fecha: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-400">UUID: {{ substr($payment->uuid, 0, 13) }}...</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-company-panel-layout>