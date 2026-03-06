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
            <?php echo e(__('Detalle Parte: ') . ($workReport->title ?? 'Sin título')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del parte -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Información del Parte</h3>
                    <p><strong>Cliente:</strong> <?php echo e($workReport->client->name); ?></p>
                    <p><strong>Técnico:</strong> <?php echo e($workReport->technician->name); ?></p>
                    <p><strong>Título:</strong> <?php echo e($workReport->title ?? '-'); ?></p>
                    <p><strong>Descripción:</strong> <?php echo e($workReport->description ?? '-'); ?></p>
                    <p><strong>Estado:</strong> <?php echo e($workReport->status); ?></p>
                    <p><strong>Tiempo total:</strong> <?php echo e(number_format($workReport->total_seconds / 3600, 2)); ?> horas (<?php echo e($workReport->total_seconds); ?> segundos)</p>
                    <?php if($workReport->validated_at): ?>
                        <p><strong>Validado por:</strong> <?php echo e($workReport->validator->name ?? '-'); ?> el <?php echo e($workReport->validated_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Eventos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Eventos del Cronómetro</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Tipo</th>
                                <th class="px-4 py-2 text-left">Fecha/Hora</th>
                                <th class="px-4 py-2 text-left">Tiempo Acumulado (horas)</th>
                                <th class="px-4 py-2 text-left">Creado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $workReport->events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-2"><?php echo e($event->type); ?></td>
                                    <td class="px-4 py-2"><?php echo e($event->occurred_at->format('d/m/Y H:i:s')); ?></td>
                                    <td class="px-4 py-2"><?php echo e(number_format($event->elapsed_seconds_after / 3600, 2)); ?>h</td>
                                    <td class="px-4 py-2"><?php echo e($event->creator->name ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Evidencias -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Evidencias</h3>
                    <?php if($workReport->evidences->count() > 0): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Nombre</th>
                                    <th class="px-4 py-2 text-left">Tamaño</th>
                                    <th class="px-4 py-2 text-left">Subido por</th>
                                    <th class="px-4 py-2 text-left">Fecha</th>
                                    <th class="px-4 py-2 text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $workReport->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evidence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2"><?php echo e($evidence->original_name); ?></td>
                                        <td class="px-4 py-2"><?php echo e(number_format($evidence->size_bytes / 1024, 2)); ?> KB</td>
                                        <td class="px-4 py-2"><?php echo e($evidence->uploader->name ?? '-'); ?></td>
                                        <td class="px-4 py-2"><?php echo e($evidence->created_at->format('d/m/Y H:i')); ?></td>
                                        <td class="px-4 py-2">
                                            <a href="<?php echo e(route('evidences.download', $evidence)); ?>" class="text-blue-500 hover:text-blue-700">Descargar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-gray-500">No hay evidencias asociadas.</p>
                    <?php endif; ?>
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
<?php endif; ?>
<?php /**PATH /var/www/src/resources/views/admin/work-reports/show.blade.php ENDPATH**/ ?>