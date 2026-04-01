<x-company-panel-layout :company="$company">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
        Nuevo Producto o Servicio para: {{ $company->name }}
    </h2>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

            <form method="POST" action="{{ route('products.store') }}">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label for="description" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Descripción*</label>
                        <input id="description" name="description" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('description') }}" required />
                    </div>

                    <!-- SKU y Precio -->
                    <div>
                        <label for="sku" class="block font-medium text-sm text-gray-700 dark:text-gray-300">SKU / Núm. de Parte</label>
                        <input id="sku" name="sku" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('sku') }}" />
                    </div>
                    <div>
                        <label for="price" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Precio Unitario (sin IVA)*</label>
                        <input id="price" name="price" type="number" step="0.01" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('price') }}" required />
                    </div>

                    <!-- Buscador Inteligente para Clave de Producto/Servicio -->
                    <div x-data="autocomplete('{{ route('api.sat_product_keys.search') }}')" class="relative">
                        <label for="sat_product_key_search" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Clave Prod/Serv (SAT)*</label>
                        <input type="hidden" name="sat_product_key" x-model="selectedCode">
                        <input id="sat_product_key_search" 
                               type="text" 
                               x-model="search" 
                               @input.debounce.300ms="fetchResults()"
                               @focus="showResults = true"
                               @click.away="showResults = false"
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" 
                               placeholder="Escribe para buscar..." 
                               required>
                        
                        <div x-show="showResults && results.length > 0" class="absolute z-10 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md mt-1 max-h-60 overflow-y-auto" style="display: none;">
                            <ul>
                                <template x-for="result in results" :key="result.id">
                                    <li @click="selectResult(result)" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <span class="font-bold" x-text="result.code"></span> - <span x-text="result.name"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Clave de Unidad del SAT (Ahora como lista desplegable) -->
                    <div>
                        <label for="sat_unit_key" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Clave de Unidad (SAT)*</label>
                        <select id="sat_unit_key" name="sat_unit_key" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                            <option value="" disabled selected>-- Selecciona --</option>
                            @foreach ($satUnitKeys as $code => $name)
                                <option value="{{ $code }}" {{ old('sat_unit_key') == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Checkbox de Impuestos -->
                    <div class="md:col-span-2">
                         <label for="taxes" class="inline-flex items-center">
                            <input type="checkbox" id="taxes" name="taxes" value="1" checked class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Este producto causa impuestos (IVA)</span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
            <label for="student_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Asociar con Alumno (Opcional, para IEDU)</label>
            <select id="student_id" name="student_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600">
                <option value="">-- Ninguno --</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->name }} ({{ $student->curp }})</option>
                @endforeach
            </select>
        </div>
    </div>
    
                </div>

                <div class="flex items-center justify-end mt-8">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">
                        Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function autocomplete(apiUrl) {
            return {
                search: '',
                results: [],
                showResults: false,
                selectedCode: '',
                fetchResults() {
                    if (this.search.length < 3) {
                        this.results = [];
                        this.showResults = false;
                        return;
                    }
                    fetch(`${apiUrl}?query=${this.search}`)
                        .then(response => response.json())
                        .then(data => {
                            this.results = data;
                            this.showResults = true;
                        });
                },
                selectResult(result) {
                    this.selectedCode = result.code;
                    this.search = `${result.code} - ${result.name}`;
                    this.showResults = false;
                }
            }
        }
    </script>
</x-company-panel-layout>
