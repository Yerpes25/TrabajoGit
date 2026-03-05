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
                    <p><strong>Técnico:</strong> <?php echo e($workReport->technician->name); ?></p>
                    <p><strong>Título:</strong> <?php echo e($workReport->title ?? '-'); ?></p>
                    <p><strong>Descripción:</strong> <?php echo e($workReport->description ?? '-'); ?></p>
                    <?php if($workReport->summary): ?>
                        <p><strong>Resumen:</strong> <?php echo e($workReport->summary); ?></p>
                    <?php endif; ?>
                    <p><strong>Estado:</strong> 
                        <span class="px-2 py-1 rounded text-xs
                            <?php if($workReport->status === 'finished'): ?> bg-blue-100 text-blue-800
                            <?php else: ?> bg-gray-100 text-gray-800
                            <?php endif; ?>">
                            <?php echo e($workReport->status); ?>

                        </span>
                    </p>
                    <p><strong>Tiempo total:</strong> <?php echo e(gmdate('H:i:s', $workReport->total_seconds)); ?> (<?php echo e($workReport->total_seconds); ?> segundos)</p>
                    <?php if($workReport->finished_at): ?>
                        <p><strong>Finalizado:</strong> <?php echo e($workReport->finished_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                    <?php if($workReport->validated_at): ?>
                        <p><strong>Validado por:</strong> <?php echo e($workReport->validator->name ?? '-'); ?> el <?php echo e($workReport->validated_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Eventos del cronómetro -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Eventos del Cronómetro</h3>
                    <?php if($workReport->events->count() > 0): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Tipo</th>
                                    <th class="px-4 py-2 text-left">Fecha/Hora</th>
                                    <th class="px-4 py-2 text-left">Tiempo Acumulado</th>
                                    <th class="px-4 py-2 text-left">Creado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $workReport->events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2"><?php echo e($event->type); ?></td>
                                        <td class="px-4 py-2"><?php echo e($event->occurred_at->format('d/m/Y H:i:s')); ?></td>
                                        <td class="px-4 py-2"><?php echo e(gmdate('H:i:s', $event->elapsed_seconds_after)); ?></td>
                                        <td class="px-4 py-2"><?php echo e($event->creator->name ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-gray-500">No hay eventos registrados.</p>
                    <?php endif; ?>
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
                                            <a href="<?php echo e(route('evidences.download', $evidence)); ?>" class="text-indigo-600 hover:text-indigo-900">Descargar</a>
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
<?php /**PATH /var/www/src/resources/views/client/work-reports/show.blade.php ENDPATH**/ ?>