<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['client']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['client']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $activeReport = $client->workReports->first();
    $hasActive = !is_null($activeReport);
?>

<div class="card">

    <div>

        <div class="card-header">

            <div>
                <div class="card-title">
                    <?php echo e($client->user->name); ?>

                </div>

                <div class="card-client">
                    <?php if($hasActive): ?>
                        <?php echo e($activeReport->title ?? 'Parte #'.$activeReport->id); ?>

                    <?php else: ?>
                        Nuevo Servicio
                    <?php endif; ?>
                </div>
            </div>

            <span class="flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full
                <?php if(!$hasActive): ?> bg-blue-50 text-blue-700
                <?php elseif($activeReport->status === 'in_progress'): ?> bg-green-100
                <?php elseif($activeReport->status === 'paused'): ?> bg-yellow-100
                <?php elseif($activeReport->status === 'finished'): ?> bg-blue-100
                <?php elseif($activeReport->status === 'validated'): ?> bg-purple-100
                <?php endif; ?>
            ">

                <span class="material-symbols-outlined text-base">
                    <?php if(!$hasActive): ?> person
                    <?php elseif($activeReport->status === 'in_progress'): ?> play_arrow
                    <?php elseif($activeReport->status === 'paused'): ?> pause
                    <?php elseif($activeReport->status === 'finished'): ?> check_circle
                    <?php elseif($activeReport->status === 'validated'): ?> verified
                    <?php endif; ?>
                </span>

                <?php if(!$hasActive): ?>
                    Disponible
                <?php elseif($activeReport->status === 'in_progress'): ?>
                    En progreso
                <?php elseif($activeReport->status === 'paused'): ?>
                    Pausado
                <?php elseif($activeReport->status === 'finished'): ?>
                    Finalizado
                <?php elseif($activeReport->status === 'validated'): ?>
                    Validado
                <?php endif; ?>

            </span>

        </div>


        
        <div class="card-time">

            <?php if(!$hasActive): ?>
                <span>Saldo disponible</span>
                <strong class="text-green-600">
                    <?php echo e(floor($client->profile->balance_seconds / 3600)); ?>h
                    <?php echo e(floor(($client->profile->balance_seconds % 3600) / 60)); ?>m
                </strong>
            <?php else: ?>
                <span>Tiempo trabajado</span>

                <strong
                    id="crono-<?php echo e($activeReport->id); ?>"
                    data-total-seconds="<?php echo e($activeReport->total_seconds); ?>"
                    <?php if($activeReport->status === 'in_progress'): ?>
                        data-running="1"
                    data-started-at="<?php echo e($activeReport->active_started_at); ?>"
                    <?php else: ?>
                        data-running="0"
                    <?php endif; ?>
                >
                    <?php echo e(gmdate('H:i:s', $activeReport->total_seconds)); ?>

                </strong>
            <?php endif; ?>

        </div>


        
        <div class="card-date">
            <?php if($hasActive): ?>
                Creado: <?php echo e($activeReport->created_at->format('d/m/Y H:i')); ?>

            <?php else: ?>
                Listo para comenzar a trabajar
            <?php endif; ?>
        </div>

    </div>


    
    <div class="card-actions">

        <?php if(!$hasActive): ?>

            
            <form action="<?php echo e(route('technician.work-reports.store-and-start')); ?>" method="POST" class="w-full">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="client_id" value="<?php echo e($client->id); ?>">
                <input type="hidden" name="title" value="Servicio técnico - <?php echo e(now()->format('d/m/Y')); ?>">

                <button type="submit" class="btn-primary w-full flex justify-center items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined">play_circle</span>
                    Iniciar Parte
                </button>
            </form>

        <?php else: ?>

            <?php if($activeReport->status === 'in_progress'): ?>

                <form action="<?php echo e(route('technician.work-reports.pause', $activeReport)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-secondary">
                        Pausar
                    </button>
                </form>

            <?php elseif($activeReport->status === 'paused'): ?>

                <form action="<?php echo e(route('technician.work-reports.resume', $activeReport)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-primary">
                        Reanudar
                    </button>
                </form>

                <form action="<?php echo e(route('technician.work-reports.finish', $activeReport)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-secondary">
                        Finalizar
                    </button>
                </form>

            <?php elseif($activeReport->status === 'finished'): ?>

                <a href="<?php echo e(route('technician.work-reports.show', $activeReport)); ?>"
                   class="btn-secondary">
                    Ver detalles
                </a>

            <?php elseif($activeReport->status === 'validated'): ?>

                <span class="text-sm text-gray-500">
                    Trabajo validado
                </span>

            <?php endif; ?>

        <?php endif; ?>

    </div>

</div>
<?php /**PATH /var/www/src/resources/views/components/work-report-card.blade.php ENDPATH**/ ?>