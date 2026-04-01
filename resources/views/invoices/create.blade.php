<x-company-panel-layout :company="$company">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
        Nueva Factura para: {{ $company->name }}
    </h2>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
            
            <div x-data="invoiceForm()">
                {{-- Bloque para mostrar errores de sesión o validación --}}
                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md" role="alert">
                        <strong class="font-bold">¡Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md" role="alert">
                        <strong class="font-bold">Por favor, corrige los siguientes errores:</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('invoices.store') }}">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <input type="hidden" name="client_id" x-model="selectedClient.id">
                    <input type="hidden" name="items" :value="JSON.stringify(items)">
                    <input type="hidden" name="totals" :value="JSON.stringify({ subtotal: subtotal, total_traslados: totalTraslados, total_retenciones: totalRetenciones, total: total })">
                    <input type="hidden" name="invoice_email" x-model="invoiceEmail">
                    
                    {{-- SECCIÓN 1: DATOS DEL RECEPTOR --}}
                    <h3 class="text-lg font-bold border-b dark:border-gray-700 pb-2">1. Datos del Receptor</h3>
                    <div class="mt-4">
                        <label for="client_search" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Buscar Cliente</label>
                        <div class="relative">
                            <input id="client_search" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" placeholder="Buscar por RFC, Razón Social o Nombre Comercial..."
                                   x-model="clientSearchQuery" @input.debounce.300ms="searchClients()" @focus="showClientResults = true" @click.away="showClientResults = false">
                            
                            <div x-show="showClientResults && clientSearchResults.length > 0" class="absolute z-20 w-full bg-white dark:bg-gray-700 border rounded-md mt-1 max-h-60 overflow-y-auto" style="display: none;">
                                <ul>
                                    <template x-for="client in clientSearchResults" :key="client.id">
                                        <li @click="selectClient(client)" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <p class="font-bold" x-text="client.name"></p><p class="text-sm" x-text="client.rfc"></p>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div x-show="selectedClient.id" class="mt-4 p-4 border rounded-md bg-gray-50 dark:bg-gray-900 dark:border-gray-700" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><strong>Cliente:</strong> <span x-text="selectedClient.name"></span></p>
                                <p><strong>RFC:</strong> <span x-text="selectedClient.rfc"></span></p>
                                <p><strong>Régimen Fiscal:</strong> <span x-text="selectedClient.fiscal_regime"></span></p>
                            </div>
                            <div>
                                <label for="invoice_email" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Email para Envío</label>
                                <input id="invoice_email" type="email" x-model="invoiceEmail" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: CONCEPTOS (PRODUCTOS) --}}
                    <h3 class="text-lg font-bold border-b dark:border-gray-700 pb-2 mt-8">2. Conceptos</h3>
                    <div class="mt-4">
                        <label for="product_search" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Buscar Producto o Servicio</label>
                        <div class="relative">
                            <input id="product_search" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" placeholder="Buscar por descripción o SKU..."
                                   x-model="productSearchQuery" @input.debounce.300ms="searchProducts()" @focus="showProductResults = true" @click.away="showProductResults = false">
                            
                            <div x-show="showProductResults && productSearchResults.length > 0" class="absolute z-20 w-full bg-white dark:bg-gray-700 border rounded-md mt-1 max-h-60 overflow-y-auto" style="display: none;">
                                <ul>
                                    <template x-for="product in productSearchResults" :key="product.id">
                                        <li @click="addProduct(product)" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <p class="font-bold" x-text="product.description"></p><p class="text-sm" x-text="`$${product.price}`"></p>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Cant.</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Descripción</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">P. Unitario</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Importe</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-2 py-2"><input type="number" x-model.number="item.quantity" @input="updateItemTotal(index)" class="w-20 rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"></td>
                                        <td class="px-2 py-2"><input type="text" x-model="item.description" class="w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"></td>
                                        <td class="px-2 py-2"><input type="number" step="0.01" x-model.number="item.price" @input="updateItemTotal(index)" class="w-28 rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"></td>
                                        <td class="px-2 py-2 text-sm" x-text="`$${item.total.toFixed(2)}`"></td>
                                        <td class="px-2 py-2">
                                            <button @click.prevent="openTaxModal(index)" class="text-blue-500 hover:text-blue-700 text-sm">Editar Impuestos</button>
                                            <button @click.prevent="removeItem(index)" class="text-red-500 hover:text-red-700 ml-2">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">No hay conceptos agregados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- SECCIÓN 3: TOTALES --}}
                     <h3 class="text-lg font-bold border-b dark:border-gray-700 pb-2 mt-8">3. Totales y Desglose de Impuestos</h3>
                     <div class="mt-4 flex justify-end">
                        <div class="w-full max-w-sm space-y-2 text-gray-700 dark:text-gray-300">
                            <div class="flex justify-between"><span>Subtotal:</span> <span x-text="`$${subtotal.toFixed(2)}`"></span></div>
                            <div class="flex justify-between text-green-600"><span>(+) Impuestos Trasladados:</span> <span x-text="`$${totalTraslados.toFixed(2)}`"></span></div>
                            <div class="flex justify-between text-red-600"><span>(-) Impuestos Retenidos:</span> <span x-text="`$${totalRetenciones.toFixed(2)}`"></span></div>
                            <hr class="my-1 dark:border-gray-600">
                            <div class="flex justify-between font-bold text-xl"><span>Total:</span> <span x-text="`$${total.toFixed(2)}`"></span></div>
                        </div>
                     </div>

                    {{-- SECCIÓN 4: DATOS FISCALES --}}
                    <h3 class="text-lg font-bold border-b dark:border-gray-700 pb-2 mt-8">4. Datos Fiscales y Adicionales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                        <div>
                            <label for="cfdi_use" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Uso de CFDI*</label>
                            <select id="cfdi_use" name="cfdi_use" x-model="cfdiUse" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                                <option value="" disabled>-- Selecciona --</option>
                                @foreach ($cfdiUses as $code => $name)
                                    <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="payment_form" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Forma de Pago*</label>
                            <select id="payment_form" name="payment_form" x-model="paymentForm" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                                <option value="" disabled>-- Selecciona --</option>
                                @foreach ($paymentForms as $code => $name)
                                    <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="payment_method" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Método de Pago*</label>
                            <select id="payment_method" name="payment_method" x-model="paymentMethod" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                                <option value="" disabled>-- Selecciona --</option>
                                @foreach ($paymentMethods as $code => $name)
                                    <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6">
                        <label for="observations" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Observaciones</label>
                        <textarea id="observations" name="observations" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600"></textarea>
                    </div>
                    
                    <div class="flex items-center justify-end mt-8">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 disabled:opacity-50" :disabled="!selectedClient.id || items.length === 0">
                            Generar Factura
                        </button>
                    </div>
                </form>

                <!-- MODAL PARA EDITAR IMPUESTOS -->
                <div x-show="taxModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-30" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg" @click.away="taxModalOpen = false">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Editar Impuestos del Concepto</h3>
                        <template x-if="editingItem">
                            <div class="mt-4">
                                <p class="font-semibold text-sm text-gray-700 dark:text-gray-300" x-text="editingItem.description"></p>
                                <div class="mt-4 border-t dark:border-gray-600 pt-4">
                                    <div class="flex items-center space-x-4 text-gray-700 dark:text-gray-300">
                                        <label class="text-sm font-medium">Objeto de Impuesto:</label>
                                        <label><input type="radio" :name="`modal_isTaxable_${editingItemIndex}`" :value="true" x-model="editingItem.isTaxable" @change="toggleTax(editingItemIndex, true)"> Sí</label>
                                        <label><input type="radio" :name="`modal_isTaxable_${editingItemIndex}`" :value="false" x-model="editingItem.isTaxable" @change="toggleTax(editingItemIndex, false)"> No</label>
                                    </div>

                                    <div x-show="editingItem.isTaxable" class="mt-4">
                                        <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">Impuestos Aplicados:</h4>
                                        <ul class="text-xs space-y-1">
                                            <template x-for="(tax, taxIndex) in editingItem.taxes" :key="taxIndex">
                                                <li class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-2 rounded">
                                                    <span x-text="`${tax.type} - ${tax.name} - ${tax.factor} - ${tax.rate}`"></span>
                                                    <button @click.prevent="removeTax(editingItemIndex, taxIndex)" class="text-red-500 text-xs px-1 font-bold">&times;</button>
                                                </li>
                                            </template>
                                            <li x-show="editingItem.taxes.length === 0" class="text-center text-gray-500">Sin impuestos.</li>
                                        </ul>
                                        <h4 class="text-sm font-semibold mt-4 mb-2 text-gray-700 dark:text-gray-300">Añadir Nuevo Impuesto:</h4>
                                        <div class="grid grid-cols-12 gap-2 items-end">
                                            <div class="col-span-3"><label class="text-xs">Tipo</label><select x-model="editingItem.newTax.type" class="w-full text-xs rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"><option value="Traslado">Traslado</option><option value="Retencion">Retención</option></select></div>
                                            <div class="col-span-3"><label class="text-xs">Impuesto</label><select x-model="editingItem.newTax.name" class="w-full text-xs rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"><option value="IVA">IVA</option><option value="ISR">ISR</option><option value="IEPS">IEPS</option></select></div>
                                            <div class="col-span-3"><label class="text-xs">Factor</label><select x-model="editingItem.newTax.factor" class="w-full text-xs rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"><option value="Tasa">Tasa</option><option value="Exento">Exento</option></select></div>
                                            <div class="col-span-2"><label class="text-xs">Tasa</label><input type="number" step="0.0001" x-model.number="editingItem.newTax.rate" class="w-full text-xs rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"></div>
                                            <div class="col-span-1"><button @click.prevent="addTax(editingItemIndex)" class="bg-blue-500 text-white rounded px-2 py-1 text-xs">Añadir</button></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 text-right">
                                    <button @click="taxModalOpen = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Cerrar</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function invoiceForm() {
            return {
                // Cliente
                clientSearchQuery: '', clientSearchResults: [], showClientResults: false, selectedClient: {},
                invoiceEmail: '',
                // Productos
                productSearchQuery: '', productSearchResults: [], showProductResults: false, items: [],
                // Datos Fiscales
                cfdiUse: '', paymentForm: '', paymentMethod: '',
                // Modal
                taxModalOpen: false, editingItemIndex: null,

                companyId: {{ $company->id }},

                searchClients() {
                    if (this.clientSearchQuery.length < 2) { this.clientSearchResults = []; return; }
                    fetch(`{{ route('api.clients.search') }}?company_id=${this.companyId}&query=${this.clientSearchQuery}`)
                        .then(res => res.json()).then(data => this.clientSearchResults = data);
                },
                selectClient(client) {
                    this.selectedClient = client;
                    this.clientSearchQuery = client.name;
                    this.showClientResults = false;
                    this.invoiceEmail = client.email || '';
                    this.cfdiUse = client.cfdi_use || '';
                    this.paymentForm = client.payment_form || '';
                    this.paymentMethod = client.payment_method || '';
                },
                searchProducts() {
                    if (this.productSearchQuery.length < 2) { this.productSearchResults = []; return; }
                    fetch(`{{ route('api.products.search') }}?company_id=${this.companyId}&query=${this.productSearchQuery}`)
                        .then(res => res.json()).then(data => this.productSearchResults = data);
                },
                addProduct(product) {
                    this.items.push({
                        product_id: product.id,
                        quantity: 1,
                        description: product.description,
                        price: parseFloat(product.price),
                        total: parseFloat(product.price),
                        isTaxable: product.taxes,
                        taxes: product.taxes ? [{ type: 'Traslado', name: 'IVA', factor: 'Tasa', rate: 0.16 }] : [],
                        newTax: { type: 'Traslado', name: 'IVA', factor: 'Tasa', rate: 0.16 },
                        sat_product_key: product.sat_product_key,
                        sat_unit_key: product.sat_unit_key,
                        student: product.student || null 
                    });
                    this.productSearchQuery = '';
                    this.showProductResults = false;
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                updateItemTotal(index) {
                    let item = this.items[index];
                    item.total = item.quantity * item.price;
                },
                openTaxModal(index) {
                    this.editingItemIndex = index;
                    this.taxModalOpen = true;
                },
                get editingItem() {
                    return this.editingItemIndex !== null ? this.items[this.editingItemIndex] : null;
                },
                toggleTax(index, isTaxable) {
                    if (!isTaxable) {
                        this.items[index].taxes = [];
                    } else if (this.items[index].taxes.length === 0) {
                        this.items[index].taxes.push({ type: 'Traslado', name: 'IVA', factor: 'Tasa', rate: 0.16 });
                    }
                },
                addTax(index) {
                    const item = this.items[index];
                    if (item.newTax.rate !== null && item.newTax.rate >= 0) {
                        item.taxes.push({ ...item.newTax });
                    }
                },
                removeTax(itemIndex, taxIndex) {
                    this.items[itemIndex].taxes.splice(taxIndex, 1);
                },
                get subtotal() {
                    return this.items.reduce((acc, item) => acc + item.total, 0);
                },
                get totalTraslados() {
                    return this.items.reduce((total, item) => {
                        const itemTraslados = item.taxes
                            .filter(t => t.type === 'Traslado')
                            .reduce((acc, tax) => acc + (item.total * tax.rate), 0);
                        return total + itemTraslados;
                    }, 0);
                },
                get totalRetenciones() {
                    return this.items.reduce((total, item) => {
                        const itemRetenciones = item.taxes
                            .filter(t => t.type === 'Retencion')
                            .reduce((acc, tax) => acc + (item.total * tax.rate), 0);
                        return total + itemRetenciones;
                    }, 0);
                },
                get total() {
                    return this.subtotal + this.totalTraslados - this.totalRetenciones;
                }
            }
        }
    </script>
</x-company-panel-layout>
