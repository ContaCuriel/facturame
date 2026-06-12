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
    
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">
        Dashboard de <?php echo e($company->name); ?>

    </h1>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Widget: Ingresos del Mes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos del Mes</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                $<?php echo e(number_format($monthlyRevenue, 2)); ?>

            </p>
        </div>

        <!-- Widget: Facturas del Mes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Facturas del Mes</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                <?php echo e($invoiceCount); ?>

            </p>
        </div>

        <!-- Widget: Total de Clientes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Clientes</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                <?php echo e($totalClients); ?>

            </p>
        </div>
    </div>

    
    <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h3 class="text-lg font-semibold mb-4">Facturas Recientes</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Folio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $recentInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"><?php echo e($invoice->series); ?>-<?php echo e($invoice->folio); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300"><?php echo e($invoice->client->name ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300"><?php echo e($invoice->created_at->format('d/m/Y')); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">$<?php echo e(number_format($invoice->total, 2)); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($invoice->status === 'issued' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                        Timbrada
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay facturas recientes.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\facturameorg\resources\views/company/dashboard.blade.php ENDPATH**/ ?>