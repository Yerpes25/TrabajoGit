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
            <?php echo e(__('Mis Partes de Trabajo')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="<?php echo e(route('technician.work-reports.create')); ?>" class="btn-primary">
                    Crear Nuevo Parte
                </a>
            </div>

            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>


            <div class="cards-grid">

                <?php $__currentLoopData = $workReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <div class="card">

                        <div>

                            <div class="card-header">

                                <div>
                                    <div class="card-title">
                                        <?php echo e($report->title ?? 'Parte #'.$report->id); ?>

                                    </div>

                                    <div class="card-client">
                                        <?php echo e($report->client->user->name); ?>

                                    </div>
                                </div>

                                <span class="card-status
<?php if($report->status === 'in_progress'): ?> status-progress
<?php elseif($report->status === 'paused'): ?> status-paused
<?php elseif($report->status === 'finished'): ?> status-finished
<?php endif; ?>">

<?php echo e($report->status); ?>


</span>

                            </div>


                            <div class="card-time">
                                <span>Tiempo trabajado</span>
                                <strong id="crono-<?php echo e($report->id); ?>"
                                        data-total-seconds="<?php echo e($report->total_seconds); ?>"
                                        <?php if($report->status === 'in_progress'): ?>
                                            data-running="1"
                                        data-started-at="<?php echo e($report->active_started_at); ?>"
                                        <?php else: ?>
                                            data-running="0"
                                    <?php endif; ?>
                                >
                                    <?php echo e(gmdate('H:i:s', $report->total_seconds)); ?>

                                </strong>
                            </div>


                            <div class="card-date">
                                Creado: <?php echo e($report->created_at->format('d/m/Y H:i')); ?>

                            </div>

                        </div>


                        <div class="card-actions">

                            <?php if($report->status === 'paused'): ?>
                                <form action="<?php echo e(route('technician.work-reports.start', $report)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-primary">Iniciar</button>
                                </form>
                            <?php endif; ?>

                                <?php if($report->status === 'in_progress'): ?>
                                    <form action="<?php echo e(route('technician.work-reports.pause', $report)); ?>" method="POST" class="pararCrono">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn-primary">En ejecución (Pausar)</button>
                                    </form>
                                <?php endif; ?>

                            <a href="<?php echo e(route('technician.work-reports.show', $report)); ?>" class="btn-secondary">
                                Ver
                            </a>


                            <?php if($report->status !== 'validated'): ?>
                                <a href="<?php echo e(route('technician.work-reports.edit', $report)); ?>" class="btn-secondary">
                                    Editar
                                </a>
                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                <a href="<?php echo e(route('technician.work-reports.create')); ?>" class="card-create">
                    <div class="create-icon">+</div>
                    Crear nuevo parte
                </a>

            </div>


            <div class="mt-6">
                <?php echo e($workReports->links()); ?>

            </div>


            <div class="mt-6">
                <a href="<?php echo e(route('technician.dashboard')); ?>" class="btn-back">
                    ← Volver
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
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const cronos = document.querySelectorAll('[id^="crono-"]');

        cronos.forEach(cronoEl => {

            if(cronoEl.dataset.running !== "1") return;

            let totalSeconds = parseInt(cronoEl.dataset.totalSeconds);

            // Diferencia entre inicio y ahora
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
<?php /**PATH /var/www/src/resources/views/technician/work-reports/index.blade.php ENDPATH**/ ?>