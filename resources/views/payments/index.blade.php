<x-company-panel-layout :company="$invoice->company">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Control de Pagos (REP)
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Factura: {{ $invoice->series }}-{{ $invoice->folio }} | Cliente: {{ $invoice->client->name }}
            </p>
        </div>
        <a href="{{ route('invoices.index', ['company_id' => $invoice->company_id]) }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">
            Volver a Facturas
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative dark:bg-green-900 dark:border-green-600 dark:text-green-300" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative dark:bg-red-900 dark:border-red-600 dark:text-red-300" role="alert">
            <strong class="font-bold">¡Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative dark:bg-red-900 dark:border-red-600 dark:text-red-300">
            <strong class="font-bold">Revisa los siguientes datos:</strong>
            <ul class="list-disc pl-5 mt-2 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tarjetas de Resumen (Cálculos automáticos del controlador) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold uppercase">Total de la Factura</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($invoice->total, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-green-500">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold uppercase">Total Pagado</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-red-500">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold uppercase">Saldo Pendiente</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($outstandingBalance, 2) }}</p>
        </div>
    </div>

    <!-- Bloque Dinámico: Mostrar formulario o mensaje de liquidado -->
    @if($outstandingBalance <= 0)
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md">
            <strong>¡Factura liquidada!</strong> Esta factura ya ha sido pagada en su totalidad.
        </div>
    @else
        <!-- Formulario para timbrar nuevo pago -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Registrar Nuevo Pago (Parcialidad {{ $nextInstallment }})</h2>
                
                <form action="{{ route('payments.store', $invoice) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Fecha del Pago*</label>
    <input type="date" name="payment_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300" required>
</div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Forma de Pago*</label>
                            <select name="payment_form" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300" required>
                                <option value="01">01 - Efectivo</option>
                                <option value="02">02 - Cheque nominativo</option>
                                <option value="03">03 - Transferencia electrónica de fondos</option>
                                <option value="04">04 - Tarjeta de crédito</option>
                                <option value="28">28 - Tarjeta de débito</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Monto Recibido ($)*</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $outstandingBalance }}" name="amount" value="{{ $outstandingBalance }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300" required>
                            <small class="text-gray-500 dark:text-gray-400">Máximo: ${{ number_format($outstandingBalance, 2) }}</small>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700">
                            Timbrar Complemento de Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Tabla de Historial de Pagos -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Historial de Complementos Emitidos</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Parcialidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha de Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">UUID</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($payments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Pago #{{ $payment->installment_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">${{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $payment->uuid }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('payments.pdf', $payment) }}" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 mr-3 font-bold">PDF</a>
<a href="{{ route('payments.xml', $payment) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-bold">XML</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Aún no hay complementos de pago registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-company-panel-layout>