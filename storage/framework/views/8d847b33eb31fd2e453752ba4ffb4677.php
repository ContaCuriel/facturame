<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title><?php echo e(config('app.name', 'Laravel')); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: window.innerWidth > 768 ? (localStorage.getItem('sidebarOpen') === 'true' || localStorage.getItem('sidebarOpen') === null) : false }" 
             x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))"
             class="min-h-screen bg-gray-100 dark:bg-gray-900">
            
            <aside class="bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300 w-64 fixed inset-y-0 left-0 z-30 shadow-lg transform transition-transform duration-300" 
                   :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
                
                <div class="p-4 border-b dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200"><?php echo e($company->name); ?></h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($company->rfc); ?></p>
                </div>

                <nav class="mt-4">
                    <a href="<?php echo e(route('companies.show', $company)); ?>" class="flex items-center px-4 py-2 mt-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 <?php echo e(request()->routeIs('companies.show') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : ''); ?>">
                        Dashboard
                    </a>
                    <a href="<?php echo e(route('invoices.index', ['company_id' => $company->id])); ?>" class="flex items-center px-4 py-2 mt-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 <?php echo e(request()->routeIs('invoices.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : ''); ?>">
                        Facturación
                    </a>
                    <a href="<?php echo e(route('clients.index', ['company_id' => $company->id])); ?>" class="flex items-center px-4 py-2 mt-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 <?php echo e(request()->routeIs('clients.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : ''); ?>">
                        Clientes
                    </a>
                    <a href="<?php echo e(route('products.index', ['company_id' => $company->id])); ?>" class="flex items-center px-4 py-2 mt-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 <?php echo e(request()->routeIs('products.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : ''); ?>">
                        Productos y Servicios
                    </a>
                    <a href="<?php echo e(route('students.index', ['company_id' => $company->id])); ?>" class="flex items-center px-4 py-2 mt-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 <?php echo e(request()->routeIs('students.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : ''); ?>">
                        Alumnos (IEDU)
                    </a>
                    <a href="<?php echo e(route('companies.csd.form', $company)); ?>" class="flex items-center px-4 py-2 mt-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 <?php echo e(request()->routeIs('companies.csd.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : ''); ?>">
                        Sellos Digitales (CSD)
                    </a>
                    <a href="<?php echo e(route('companies.logo.form', $company)); ?>" class="flex items-center px-4 py-2 mt-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 <?php echo e(request()->routeIs('companies.logo.*') ? 'bg-gray-200 dark:bg-gray-700 font-semibold' : ''); ?>">
                        Logo y Apariencia
                    </a>
                    
                    <a href="<?php echo e(route('companies.index')); ?>" class="flex items-center px-4 py-2 mt-8 text-sm hover:bg-gray-200 dark:hover:bg-gray-700 border-t dark:border-gray-700">
                        Ver todas las empresas
                    </a>
                </nav>
            </aside>

            <div class="flex-1 flex flex-col transition-all duration-300" :class="{'md:ml-64': sidebarOpen}">
                <header class="bg-white dark:bg-gray-800 shadow-md p-4 flex items-center justify-between">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 dark:text-gray-300 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <div>
                       <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48']); ?>
                             <?php $__env->slot('trigger', null, []); ?> 
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">
                                    <div><?php echo e(Auth::user()->name); ?></div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                             <?php $__env->endSlot(); ?>
                             <?php $__env->slot('content', null, []); ?> 
                                <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('profile.edit')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit'))]); ?><?php echo e(__('Profile')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                <form method="POST" action="<?php echo e(route('logout')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('logout'),'onclick' => 'event.preventDefault(); this.closest(\'form\').submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('logout')),'onclick' => 'event.preventDefault(); this.closest(\'form\').submit();']); ?><?php echo e(__('Log Out')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                </form>
                             <?php $__env->endSlot(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                    </div>
                </header>

                <main class="flex-1 p-6">
                    <?php echo e($slot); ?>

                </main>
            </div>
            
            <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black opacity-50 z-20 md:hidden" style="display: none;"></div>
        </div>
    </body>
</html><?php /**PATH C:\xampp\htdocs\facturameorg\resources\views/components/company-panel-layout.blade.php ENDPATH**/ ?>