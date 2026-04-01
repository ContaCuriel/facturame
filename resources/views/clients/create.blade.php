<x-company-panel-layout :company="$company">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nuevo Cliente para: {{ $company->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100" x-data="{ is_foreign: {{ old('is_foreign', 0) }} }">

                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company->id }}">

                        <h3 class="text-lg font-bold">Información Fiscal (Obligatoria)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Razón Social*</label>
                                <input id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="text" name="name" value="{{ old('name') }}" required />
                            </div>
                            <div>
                                <label for="rfc" class="block font-medium text-sm text-gray-700 dark:text-gray-300">RFC*</label>
                                <input id="rfc" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="text" name="rfc" value="{{ old('rfc') }}" required />
                            </div>
                            <div>
                                <label for="zip_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Código Postal Fiscal*</label>
                                <input id="zip_code" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="text" name="zip_code" value="{{ old('zip_code') }}" required />
                            </div>
                            <div>
                                <label for="fiscal_regime" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Régimen Fiscal*</label>
                                <select id="fiscal_regime" name="fiscal_regime" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                                    <option value="" disabled selected>-- Selecciona --</option>
                                    @foreach ($fiscalRegimes as $code => $name)
                                        <option value="{{ $code }}" {{ old('fiscal_regime') == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-8 dark:border-gray-600">

                        <h3 class="text-lg font-bold">Información Comercial y de Contacto</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <label for="commercial_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nombre Comercial</label>
                                <input id="commercial_name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="text" name="commercial_name" value="{{ old('commercial_name') }}" />
                            </div>
                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Email Principal</label>
                                <input id="email" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="email" name="email" value="{{ old('email') }}" />
                            </div>
                            <div>
                                <label for="email_cc" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Email en Copia (CC)</label>
                                <input id="email_cc" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="email" name="email_cc" value="{{ old('email_cc') }}" />
                            </div>
                            <div class="col-span-2">
                                <label for="address" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Domicilio Comercial</label>
                                <input id="address" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="text" name="address" value="{{ old('address') }}" />
                                <div class="mt-2">
                                    <label for="print_address" class="inline-flex items-center">
                                        <input type="checkbox" id="print_address" name="print_address" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm">
                                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Imprimir domicilio en la factura</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-8 dark:border-gray-600">

                        <h3 class="text-lg font-bold">Valores por Defecto para Facturación</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            <div>
                                <label for="cfdi_use" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Uso de CFDI</label>
                                <select id="cfdi_use" name="cfdi_use" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                                    <option value="" selected>-- Selecciona --</option>
                                    @foreach ($cfdiUses as $code => $name)
                                        <option value="{{ $code }}" {{ old('cfdi_use') == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="payment_form" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Forma de Pago</label>
                                <select id="payment_form" name="payment_form" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                                    <option value="" selected>-- Selecciona --</option>
                                    @foreach ($paymentForms as $code => $name)
                                        <option value="{{ $code }}" {{ old('payment_form') == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="payment_method" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Método de Pago</label>
                                <select id="payment_method" name="payment_method" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                                    <option value="" selected>-- Selecciona --</option>
                                    @foreach ($paymentMethods as $code => $name)
                                        <option value="{{ $code }}" {{ old('payment_method') == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-8 dark:border-gray-600">

                        <h3 class="text-lg font-bold">Cliente Extranjero</h3>
                        <div class="mt-4">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">¿Es un cliente extranjero sin RFC en México?</label>
                            <div class="mt-2 space-x-4">
                                <label><input type="radio" name="is_foreign" value="1" x-model="is_foreign"> Sí</label>
                                <label><input type="radio" name="is_foreign" value="0" x-model="is_foreign"> No</label>
                            </div>
                        </div>

                        <div x-show="is_foreign == 1" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 border-t dark:border-gray-600 pt-6">
                            <div>
                                <label for="tax_id_registration" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Núm. de Registro de ID Tributaria*</label>
                                <input id="tax_id_registration" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" type="text" name="tax_id_registration" value="{{ old('tax_id_registration') }}" />
                            </div>
                            <div>
                                <label for="tax_residence" class="block font-medium text-sm text-gray-700 dark:text-gray-300">País de Residencia Fiscal*</label>
                                <select id="tax_residence" name="tax_residence" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                                    <option value="" selected>-- Selecciona --</option>
                                     @foreach ($countries as $code => $name)
                                        <option value="{{ $code }}" {{ old('tax_residence') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">
                                Guardar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-company-panel-layout>