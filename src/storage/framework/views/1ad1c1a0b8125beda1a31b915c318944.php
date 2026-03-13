<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Gestión de Clientes')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="<?php echo e(route('admin.clients.create')); ?>" class="px-4 py-2 rounded text-white transition-colors" style="background-color:#62bd19;" onmouseover="this.style.backgroundColor='#498d13';" onmouseout="this.style.backgroundColor='#62bd19';">
                    Crear Cliente
                </a>
            </div>

            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Saldo (horas)</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo e($client->name); ?></td>
                                <td class="px-4 py-2"><?php echo e($client->email ?? '-'); ?></td>
                                <td class="px-4 py-2">
                                    <?php echo e(number_format(($client->profile->balance_seconds ?? 0) / 3600, 2)); ?>h
                                </td>
                                <td class="px-4 py-2">
                                    <a href="<?php echo e(route('admin.clients.show', $client)); ?>" class="text-blue-500">Ver</a>
                                    <a href="<?php echo e(route('admin.clients.edit', $client)); ?>" class="text-blue-500 ml-2">Editar</a>

                                    <form action="<?php echo e(route('admin.users.toggle', $client->user)); ?>" method="POST" class="inline"
                                          onsubmit="return confirm('¿Está seguro de <?php echo e($client->user->is_active ? 'desactivar' : 'activar'); ?> este cliente?');">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="<?php echo e($client->user->is_active ? 'text-red-500' : 'text-green-500'); ?> ml-2">
                                            <?php echo e($client->user->is_active ? 'Desactivar' : 'Activar'); ?>

                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <?php echo e($clients->links()); ?>

                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-start">
                <?php if (isset($component)) { $__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-button','data' => ['href' => ''.e(route('admin.dashboard')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('admin.dashboard')).'']); ?>
                    Volver
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687)): ?>
<?php $attributes = $__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687; ?>
<?php unset($__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687)): ?>
<?php $component = $__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687; ?>
<?php unset($__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687); ?>
<?php endif; ?>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH /var/www/src/resources/views/admin/clients/index.blade.php ENDPATH**/ ?>