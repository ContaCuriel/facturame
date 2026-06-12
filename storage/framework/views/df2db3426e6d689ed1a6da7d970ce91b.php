<?php if (isset($component)) { $__componentOriginal2a9cb10c563b038a10af2de90472f1a8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2a9cb10c563b038a10af2de90472f1a8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.company-panel-layout','data' => ['company' => $company]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('company-panel-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['company' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($company)]); ?>
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
        Nuevo Producto o Servicio para: <?php echo e($company->name); ?>

    </h2>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

            <form method="POST" action="<?php echo e(route('products.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="company_id" value="<?php echo e($company->id); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label for="description" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Descripción*</label>
                        <input id="description" name="description" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="<?php echo e(old('description')); ?>" required />
                    </div>

                    <!-- SKU y Precio -->
                    <div>
                        <label for="sku" class="block font-medium text-sm text-gray-700 dark:text-gray-300">SKU / Núm. de Parte</label>
                        <input id="sku" name="sku" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="<?php echo e(old('sku')); ?>" />
                    </div>
                    <div>
                        <label for="price" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Precio Unitario (sin IVA)*</label>
                        <input id="price" name="price" type="number" step="0.01" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="<?php echo e(old('price')); ?>" required />
                    </div>

                    <!-- Buscador Inteligente para Clave de Producto/Servicio -->
                    <div x-data="autocomplete('<?php echo e(route('api.sat_product_keys.search')); ?>')" class="relative">
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
                            <?php $__currentLoopData = $satUnitKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php echo e(old('sat_unit_key') == $code ? 'selected' : ''); ?>><?php echo e($code); ?> - <?php echo e($name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($student->id); ?>" <?php echo e(old('student_id') == $student->id ? 'selected' : ''); ?>><?php echo e($student->name); ?> (<?php echo e($student->curp); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2a9cb10c563b038a10af2de90472f1a8)): ?>
<?php $attributes = $__attributesOriginal2a9cb10c563b038a10af2de90472f1a8; ?>
<?php unset($__attributesOriginal2a9cb10c563b038a10af2de90472f1a8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2a9cb10c563b038a10af2de90472f1a8)): ?>
<?php $component = $__componentOriginal2a9cb10c563b038a10af2de90472f1a8; ?>
<?php unset($__componentOriginal2a9cb10c563b038a10af2de90472f1a8); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\facturameorg\resources\views/products/create.blade.php ENDPATH**/ ?>