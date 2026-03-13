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
        <div class="flex justify-between items-center max-w-7xl mx-auto">

            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Panel de Técnico')); ?>

            </h2>

            <div class="flex gap-3">
                <a href="<?php echo e(route('technician.work-reports.create')); ?>"
                   class="btn-primary px-8 py-2 rounded-lg shadow transition flex items-center justify-center whitespace-nowrap text-center"
                   style="background-color:#1453A1; color:white;"
                   onmouseover="this.style.backgroundColor='#1962BD';"
                   onmouseout="this.style.backgroundColor='#1453A1';">
                    Crear Nuevo Parte
                </a>

                <a href="<?php echo e(route('technician.work-reports.index')); ?>"
                   class="btn-secondary px-6 py-2 rounded-lg shadow hover:bg-gray-300 transition flex items-center justify-center whitespace-nowrap text-center">
                    Ver Todos
                </a>
            </div>

        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            
            <?php if(session('error')): ?>
                <div class="card" style="background:#fef2f2;border-color:#fecaca">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <br>

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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <?php $__empty_1 = true; $__currentLoopData = $availableClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    
                    <?php if (isset($component)) { $__componentOriginal1bc4471a70461aeef7c7062cb424a93d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1bc4471a70461aeef7c7062cb424a93d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.work-report-card','data' => ['client' => $client]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('work-report-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['client' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($client)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1bc4471a70461aeef7c7062cb424a93d)): ?>
<?php $attributes = $__attributesOriginal1bc4471a70461aeef7c7062cb424a93d; ?>
<?php unset($__attributesOriginal1bc4471a70461aeef7c7062cb424a93d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1bc4471a70461aeef7c7062cb424a93d)): ?>
<?php $component = $__componentOriginal1bc4471a70461aeef7c7062cb424a93d; ?>
<?php unset($__componentOriginal1bc4471a70461aeef7c7062cb424a93d); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-full p-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                        No hay clientes con saldo disponible en este momento.
                    </div>
                <?php endif; ?>
            </div>
            <br>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Clientes con Bonos</h3>
                    <?php if(isset($clientsWithBonuses) && $clientsWithBonuses->count() > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Cliente</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Teléfono</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Total Bonos</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Acciones</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                <?php $__currentLoopData = $clientsWithBonuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap"><?php echo e($client->user->name); ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap"><?php echo e($client->user->email ?? '-'); ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap"><?php echo e($client->phone ?? '-'); ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap font-bold" style="color:#1962BD;"><?php echo e($client->bonus_issues_count); ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <a href="<?php echo e(route('technician.work-reports.create', ['client_id' => $client->id])); ?>"
                                               class="px-3 py-1 rounded text-sm font-semibold transition-colors"
                                               style="background-color:#0F4585; color:white;"
                                               onmouseover="this.style.backgroundColor='#1962BD';"
                                               onmouseout="this.style.backgroundColor='#0F4585';">
                                                Crear Parte
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No hay clientes con bonos.</p>
                    <?php endif; ?>
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
                                        <td class="px-4 py-2"><?php echo e($report->client->user->name); ?></td>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const cronos = document.querySelectorAll('[id^="crono-"]');

        cronos.forEach(cronoEl => {

            if(cronoEl.dataset.running !== "1") return;

            let totalSeconds = parseInt(cronoEl.dataset.totalSeconds);

            const startedAt = new Date(cronoEl.dataset.startedAt);
            const diffSeconds = Math.floor((Date.now() - startedAt.getTime()) / 1000);
            totalSeconds += diffSeconds;

            const updateCrono = () => {

                let segundos = totalSeconds;
                let horas = Math.floor(segundos/3600);
                segundos %= 3600;
                let minutos = Math.floor(segundos/60);
                segundos %= 60;

                cronoEl.innerText =
                    horas.toString().padStart(2,'0') + ':' +
                    minutos.toString().padStart(2,'0') + ':' +
                    segundos.toString().padStart(2,'0');

                totalSeconds++;
            };

            updateCrono();
            setInterval(updateCrono, 1000);

        });

    });
</script>
<?php /**PATH /var/www/src/resources/views/dashboard/technician.blade.php ENDPATH**/ ?>