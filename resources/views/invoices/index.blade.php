<x-company-panel-layout :company="$company">
    {{-- Alpine.js ahora maneja las variables para ambas ventanas modales --}}
    <div x-data="{ 
        cancelModalOpen: false, 
        cancelActionUrl: '', 
        replacementUuid: '', 
        motive: '02',
        emailModalOpen: false,
        emailActionUrl: '',
        recipientEmail: ''
    }">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Facturas Emitidas
            </h1>
            <a href="{{ route('invoices.create', ['company_id' => $company->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                Nueva Factura
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

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Folio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $invoice->series }}-{{ $invoice->folio }}
                                        <span class="block text-xs text-gray-500 truncate" title="{{ $invoice->uuid }}">{{ substr($invoice->uuid, 0, 8) }}...</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $invoice->client->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">${{ number_format($invoice->total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->status === 'issued' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        {{-- ✅ --- BOTONES DE ACCIÓN ACTUALIZADOS --- ✅ --}}
                                        <div class="flex justify-end items-center space-x-4">
                                            <a href="{{ route('invoices.pdf', $invoice) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">PDF</a>
                                            <a href="{{ route('invoices.xml', $invoice) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">XML</a>
                                            <button @click="emailModalOpen = true; emailActionUrl = '{{ route('invoices.email', $invoice) }}'; recipientEmail = '{{ $invoice->client->email ?? '' }}'" class="text-green-600 dark:text-green-400 hover:text-green-900">Enviar</button>
                                            @if($invoice->status === 'issued')
                                            <button @click="cancelModalOpen = true; cancelActionUrl = '{{ route('invoices.cancel', $invoice) }}'" class="text-red-600 dark:text-red-400 hover:text-red-900">Cancelar</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No hay facturas emitidas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ventana Modal de Cancelación -->
        <div x-show="cancelModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-30" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg" @click.away="cancelModalOpen = false">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Cancelar Factura</h3>
                <form :action="cancelActionUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mt-4">
                        <label for="motive" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Motivo de Cancelación*</label>
                        <select id="motive" name="motive" x-model="motive" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300" required>
                            <option value="01">01 - Comprobante emitido con errores con relación</option>
                            <option value="02">02 - Comprobante emitido con errores sin relación</option>
                            <option value="03">03 - No se llevó a cabo la operación</option>
                            <option value="04">04 - Operación nominativa relacionada a una factura global</option>
                        </select>
                    </div>

                    <div x-show="motive === '01'" class="mt-4" style="display: none;">
                        <label for="replacement_uuid" class="block font-medium text-sm text-gray-700 dark:text-gray-300">UUID de la factura que sustituye*</label>
                        <input type="text" id="replacement_uuid" name="replacement_uuid" x-model="replacementUuid" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300" placeholder="Pega aquí el folio fiscal de la nueva factura">
                    </div>

                    <div class="mt-6 text-right">
                        <button type="button" @click="cancelModalOpen = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Cerrar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm ml-2">Confirmar Cancelación</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ✅ --- NUEVA VENTANA MODAL DE ENVÍO POR CORREO --- ✅ --}}
        <div x-show="emailModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-30" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg" @click.away="emailModalOpen = false">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Enviar Factura por Correo</h3>
                <form :action="emailActionUrl" method="POST">
                    @csrf
                    <div class="mt-4">
                        <label for="email" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Dirección de Correo*</label>
                        <input type="email" id="email" name="email" x-model="recipientEmail" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300" required>
                    </div>
                    <div class="mt-6 text-right">
                        <button type="button" @click="emailModalOpen = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Cerrar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm ml-2">Enviar Correo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-company-panel-layout>

