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
            <?php echo e(__('Panel de Administración')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- KPIs básicos -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Clientes</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($totalClients); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Técnicos</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($totalTechnicians); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Usuarios Activos</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($activeUsers); ?> / <?php echo e($totalUsers); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Partes Validados</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($workReportsValidated); ?></p>
                </div>
            </div>

            <!-- Partes por estado -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Partes por Estado</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">En Progreso:</span>
                            <span class="text-lg font-semibold ml-2"><?php echo e($workReportsInProgress); ?></span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Finalizados:</span>
                            <span class="text-lg font-semibold ml-2"><?php echo e($workReportsFinished); ?></span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Validados:</span>
                            <span class="text-lg font-semibold ml-2"><?php echo e($workReportsValidated); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partes recientes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Partes Recientes</h3>
                    <?php if($recentWorkReports->count() > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cliente</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Técnico</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Estado</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tiempo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php $__currentLoopData = $recentWorkReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-4 py-2"><?php echo e($report->client->name); ?></td>
                                            <td class="px-4 py-2"><?php echo e($report->technician->name); ?></td>
                                            <td class="px-4 py-2"><?php echo e($report->status); ?></td>
                                            <td class="px-4 py-2"><?php echo e(number_format($report->total_seconds / 3600, 2)); ?>h</td>
                                            <td class="px-4 py-2"><?php echo e($report->created_at->format('d/m/Y H:i')); ?></td>
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

            <!-- Enlaces rápidos -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="<?php echo e(route('admin.users.index')); ?>" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Usuarios</h4>
                    <p class="text-sm text-gray-500">Gestionar usuarios</p>
                </a>
                <a href="<?php echo e(route('admin.clients.index')); ?>" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Clientes</h4>
                    <p class="text-sm text-gray-500">Gestionar clientes y saldo</p>
                </a>
                <a href="<?php echo e(route('admin.work-reports.index')); ?>" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Partes</h4>
                    <p class="text-sm text-gray-500">Ver todos los partes</p>
                </a>
                <a href="<?php echo e(route('admin.audit-logs.index')); ?>" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Auditoría</h4>
                    <p class="text-sm text-gray-500">Consultar logs</p>
                </a>
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
<?php /**PATH /var/www/src/resources/views/dashboard/admin.blade.php ENDPATH**/ ?>