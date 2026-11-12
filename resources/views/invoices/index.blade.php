<x-company-panel-layout :company="$company">
    {{-- Alpine.js ahora maneja las variables para las 3 ventanas modales --}}
    <div x-data="{ 
        cancelModalOpen: false, 
        cancelActionUrl: '', 
        replacementUuid: '', 
        motive: '02',
        emailModalOpen: false,
        emailActionUrl: '',
        recipientEmail: '',
        importXmlModalOpen: false
    }">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Facturas Emitidas
            </h1>
            
            {{-- CONTENEDOR DE BOTONES SUPERIORES --}}
            <div class="flex items-center space-x-3">
                <!-- Botón de Importar XML -->
                <button @click="importXmlModalOpen = true" class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-600 transition ease-in-out duration-150 shadow-sm">
                    📄 Importar XML
                </button>

                <!-- Botón de Nueva Factura -->
                <a href="{{ route('invoices.create', ['company_id' => $company->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white shadow-sm transition ease-in-out duration-150">
                    Nueva Factura
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative dark:bg-green-900 dark:border-green-600 dark:text-green-300" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('info'))
            <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-md relative dark:bg-blue-900 dark:border-blue-600 dark:text-blue-300" role="alert">
                <strong class="font-bold">Información:</strong>
                <span class="block sm:inline">{{ session('info') }}</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($invoices as $invoice)
                                <tr>
                                    {{-- ✅ EL FOLIO COMO ENLACE DINÁMICO ✅ --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                Borrador (F-{{ $invoice->folio }})
                                            </a>
                                        @else
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $invoice->series }}-{{ $invoice->folio }}
                                            </a>
                                            <span class="block text-xs text-gray-500 truncate" title="{{ $invoice->uuid }}">{{ substr($invoice->uuid, 0, 8) }}...</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $invoice->client->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">${{ number_format($invoice->total, 2) }}</td>
                                    
                                    {{-- ETIQUETA VISUAL PPD O PUE --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($invoice->status === 'draft')
                                            <span class="text-gray-400 text-xs italic">Pendiente</span>
                                        @elseif($invoice->payment_method === 'PPD')
                                            @php
                                                $totalPaid = $invoice->payments->where('status', '!=', 'cancelled')->sum('amount');
                                            @endphp
                                            @if($totalPaid >= ($invoice->total - 0.01))
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold border border-green-200">PPD (Pagado)</span>
                                            @else
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold border border-yellow-200">PPD (Crédito)</span>
                                            @endif
                                        @elseif($invoice->payment_method === 'PUE')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold border border-green-200">PUE (Pagado)</span>
                                        @else
                                            <span class="text-gray-500 text-xs italic">Histórica</span>
                                        @endif
                                    </td>

                                    {{-- ✅ ESTADO DE LA FACTURA CON COLORES ✅ --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $colorClass = match($invoice->status) {
                                                'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-300',
                                                'issued' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                            {{ $invoice->status_es }}
                                        </span>
                                    </td>
                                    
                                    {{-- ✅ BOTONES FILTRADOS SEGÚN ESTADO ✅ --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center space-x-4">
                                            @if($invoice->status === 'draft')
                                                {{-- Opciones para Borrador --}}
                                                <a href="{{ route('invoices.edit', $invoice) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 font-bold">Continuar</a>
                                            @else
                                                {{-- Opciones para Timbradas / Canceladas --}}
                                                <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 font-bold">Ver Detalles</a>
                                                
                                                {{-- Si NO fue importada por XML, mostramos PDF/XML de Facturama --}}
                                                @if($invoice->facturama_id !== 'XML_IMPORTADO')
                                                    <a href="{{ route('invoices.pdf', $invoice) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">PDF</a>
                                                    <a href="{{ route('invoices.xml', $invoice) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">XML</a>
                                                @endif
                                                
                                                @if($invoice->status === 'issued')
                                                    @if($invoice->facturama_id !== 'XML_IMPORTADO')
                                                        <button @click="emailModalOpen = true; emailActionUrl = '{{ route('invoices.email', $invoice) }}'; recipientEmail = '{{ $invoice->client->email ?? '' }}'" class="text-green-600 dark:text-green-400 hover:text-green-900">Enviar</button>
                                                    @endif
                                                    
                                                    @if($invoice->payment_method === 'PPD')
                                                        <a href="{{ route('payments.index', $invoice) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 font-bold">Pagos</a>
                                                    @endif

                                                    @if($invoice->facturama_id !== 'XML_IMPORTADO')
                                                        <button @click="cancelModalOpen = true; cancelActionUrl = '{{ route('invoices.cancel', $invoice) }}'" class="text-red-600 dark:text-red-400 hover:text-red-900">Cancelar</button>
                                                    @endif
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No hay facturas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL CANCELAR -->
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

        <!-- MODAL CORREO -->
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

        <!-- MODAL IMPORTAR XML -->
        <div x-show="importXmlModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-30" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg" @click.away="importXmlModalOpen = false">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Importar Factura de Otro Sistema</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Sube el archivo XML de una factura emitida (Ej. desde Contpaqi o el SAT). El sistema extraerá los datos para que puedas generarle su Complemento de Pago (REP).</p>
                
                <form action="{{ route('invoices.import_xml') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    
                    <div class="mt-4">
                        <label for="xml_file" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Archivo XML*</label>
                        <input type="file" id="xml_file" name="xml_file" accept=".xml" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:bg-gray-700 dark:file:text-gray-300" required>
                    </div>

                    <div class="mt-6 text-right">
                        <button type="button" @click="importXmlModalOpen = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm ml-2">Subir e Importar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-company-panel-layout>