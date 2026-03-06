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
            <?php echo e(__('Panel de Técnico')); ?>

        </h2>
     <?php $__env->endSlot(); ?>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Clientes con Bonos</h3>
                    <?php if(isset($clientsWithBonuses) && $clientsWithBonuses->count() > 0): ?>
                    <div class="flex flex-wrap gap-4">
                        <?php $__currentLoopData = $clientsWithBonuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-blue-500 text-white px-4 py-3 rounded shadow-sm min-w-[180px] flex flex-col">
                            <span class="font-semibold text-base"><?php echo e($client->name); ?></span>
                            <span class="text-sm text-blue-100 mt-1">Bonos: <span class="font-bold text-white"><?php echo e($client->bonus_issues_count); ?></span></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500">No hay clientes con bonos.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Resumen por estado -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">En Progreso</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($inProgress); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Pausados</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($paused); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Finalizados</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($finished); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Validados</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($validated); ?></p>
                </div>
            </div>

            <!-- Partes recientes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Partes Recientes</h3>
                        <a href="<?php echo e(route('technician.work-reports.index')); ?>" class="text-blue-500">Ver Todos</a>
                    </div>
                    <?php if($recentWorkReports->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cliente</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Título</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Estado</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tiempo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php $__currentLoopData = $recentWorkReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-2"><?php echo e($report->client->name); ?></td>
                                    <td class="px-4 py-2"><?php echo e($report->title ?? '-'); ?></td>
                                    <td class="px-4 py-2"><?php echo e($report->status); ?></td>
                                    <td class="px-4 py-2"><?php echo e(gmdate('H:i:s', $report->total_seconds)); ?></td>
                                    <td class="px-4 py-2">
                                        <a href="<?php echo e(route('technician.work-reports.show', $report)); ?>" class="text-blue-500">Ver</a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500">No hay partes recientes.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Acciones Rápidas</h3>
                    <div class="flex gap-4">
                        <a href="<?php echo e(route('technician.work-reports.create')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded">
                            Crear Nuevo Parte
                        </a>
                        <a href="<?php echo e(route('technician.work-reports.index')); ?>" class="bg-gray-500 text-white px-4 py-2 rounded">
                            Ver Todos los Partes
                        </a>
                    </div>
                </div>
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
<?php endif; ?><?php /**PATH /var/www/src/resources/views/dashboard/technician.blade.php ENDPATH**/ ?>