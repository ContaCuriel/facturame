<x-company-panel-layout :company="$company">

    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
        Editando Cliente: {{ $client->name }}
    </h2>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100" x-data="{ is_foreign: {{ old('is_foreign', $client->is_foreign) ? '1' : '0' }} }">

            <form method="POST" action="{{ route('clients.update', $client) }}">
                @csrf
                @method('PUT')

                {{-- INFORMACIÓN FISCAL --}}
                <h3 class="text-lg font-bold">Información Fiscal (Obligatoria)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Razón Social*</label>
                        <input id="name" name="name" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('name', $client->name) }}" required />
                    </div>
                    <div>
                        <label for="rfc" class="block font-medium text-sm text-gray-700 dark:text-gray-300">RFC*</label>
                        <input id="rfc" name="rfc" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('rfc', $client->rfc) }}" required />
                    </div>
                    <div>
                        <label for="zip_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Código Postal Fiscal*</label>
                        <input id="zip_code" name="zip_code" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('zip_code', $client->zip_code) }}" required />
                    </div>
                    <div>
                        <label for="fiscal_regime" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Régimen Fiscal*</label>
                        <select id="fiscal_regime" name="fiscal_regime" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                            @foreach ($fiscalRegimes as $code => $name)
                                <option value="{{ $code }}" @selected(old('fiscal_regime', $client->fiscal_regime) == $code)>{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- INFORMACIÓN COMERCIAL --}}
                <hr class="my-8 dark:border-gray-700">
                <h3 class="text-lg font-bold">Información Comercial y de Contacto</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                     <div>
                        <label for="commercial_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nombre Comercial</label>
                        <input id="commercial_name" name="commercial_name" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('commercial_name', $client->commercial_name) }}" />
                    </div>
                    <div>
                        <label for="email" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Email Principal</label>
                        <input id="email" name="email" type="email" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('email', $client->email) }}" />
                    </div>
                    <div>
                        <label for="email_cc" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Email en Copia (CC)</label>
                        <input id="email_cc" name="email_cc" type="email" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('email_cc', $client->email_cc) }}" />
                    </div>
                    <div class="col-span-2">
                        <label for="address" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Domicilio Comercial</label>
                        <input id="address" name="address" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('address', $client->address) }}" />
                        <div class="mt-2">
                            <label for="print_address" class="inline-flex items-center">
                                <input type="checkbox" id="print_address" name="print_address" value="1" @checked(old('print_address', $client->print_address)) class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Imprimir domicilio en la factura</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- VALORES POR DEFECTO --}}
                <hr class="my-8 dark:border-gray-700">
                <h3 class="text-lg font-bold">Valores por Defecto para Facturación</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <label for="cfdi_use" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Uso de CFDI</label>
                        <select id="cfdi_use" name="cfdi_use" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                            <option value="">-- Selecciona --</option>
                            @foreach ($cfdiUses as $code => $name)
                                <option value="{{ $code }}" @selected(old('cfdi_use', $client->cfdi_use) == $code)>{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="payment_form" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Forma de Pago</label>
                        <select id="payment_form" name="payment_form" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                             <option value="">-- Selecciona --</option>
                            @foreach ($paymentForms as $code => $name)
                                <option value="{{ $code }}" @selected(old('payment_form', $client->payment_form) == $code)>{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="payment_method" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Método de Pago</label>
                        <select id="payment_method" name="payment_method" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                             <option value="">-- Selecciona --</option>
                            @foreach ($paymentMethods as $code => $name)
                                <option value="{{ $code }}" @selected(old('payment_method', $client->payment_method) == $code)>{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- CLIENTE EXTRANJERO --}}
                <hr class="my-8 dark:border-gray-700">
                <h3 class="text-lg font-bold">Cliente Extranjero</h3>
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">¿Es un cliente extranjero sin RFC en México?</label>
                    <div class="mt-2 space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="is_foreign" value="1" x-model="is_foreign" class="dark:bg-gray-900 border-gray-300 dark:border-gray-700">
                            <span class="ms-2">Sí</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="is_foreign" value="0" x-model="is_foreign" class="dark:bg-gray-900 border-gray-300 dark:border-gray-700">
                            <span class="ms-2">No</span>
                        </label>
                    </div>
                </div>
                
                <div x-show="is_foreign == 1" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 border-t dark:border-gray-700 pt-6">
                    <div>
                        <label for="tax_id_registration" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Núm. de Registro de ID Tributaria*</label>
                        <input id="tax_id_registration" name="tax_id_registration" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('tax_id_registration', $client->tax_id_registration) }}" />
                    </div>
                    <div>
                        <label for="tax_residence" class="block font-medium text-sm text-gray-700 dark:text-gray-300">País de Residencia Fiscal*</label>
                        <select id="tax_residence" name="tax_residence" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                            <option value="">-- Selecciona --</option>
                             @foreach ($countries as $code => $name)
                                <option value="{{ $code }}" @selected(old('tax_residence', $client->tax_residence) == $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">
                        Actualizar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>