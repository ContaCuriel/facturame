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
        Nuevo Alumno para: <?php echo e($company->name); ?>

    </h2>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

            <form method="POST" action="<?php echo e(route('students.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="company_id" value="<?php echo e($company->id); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre del Alumno -->
                    <div class="md:col-span-2">
                        <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nombre Completo del Alumno*</label>
                        <input id="name" name="name" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="<?php echo e(old('name')); ?>" required />
                    </div>

                    <!-- CURP -->
                    <div>
                        <label for="curp" class="block font-medium text-sm text-gray-700 dark:text-gray-300">CURP*</label>
                        <input id="curp" name="curp" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="<?php echo e(old('curp')); ?>" required />
                    </div>

                    <!-- RVOE -->
                    <div>
                        <label for="aut_rvoe" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Aut. RVOE*</label>
                        <input id="aut_rvoe" name="aut_rvoe" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="<?php echo e(old('aut_rvoe')); ?>" required />
                    </div>
                    
                    <!-- Nivel Educativo -->
                    <div class="md:col-span-2">
                        <label for="education_level" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nivel Educativo*</label>
                        <select id="education_level" name="education_level" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                            <option value="" disabled selected>-- Selecciona un nivel --</option>
                            <?php $__currentLoopData = $educationLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($level); ?>" <?php echo e(old('education_level') == $level ? 'selected' : ''); ?>><?php echo e($level); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">
                        Guardar Alumno
                    </button>
                </div>
            </form>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2a9cb10c563b038a10af2de90472f1a8)): ?>
<?php $attributes = $__attributesOriginal2a9cb10c563b038a10af2de90472f1a8; ?>
<?php unset($__attributesOriginal2a9cb10c563b038a10af2de90472f1a8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2a9cb10c563b038a10af2de90472f1a8)): ?>
<?php $component = $__componentOriginal2a9cb10c563b038a10af2de90472f1a8; ?>
<?php unset($__componentOriginal2a9cb10c563b038a10af2de90472f1a8); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\facturameorg\resources\views/students/create.blade.php ENDPATH**/ ?>