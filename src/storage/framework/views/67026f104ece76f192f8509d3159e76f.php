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
    <div style="background-color: #f8fafc; min-height: 100vh; padding-bottom: 3rem; font-family: sans-serif;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 1.5rem;">

            
            <header style="padding: 2.5rem 0;">
                <h1 style="font-size: 1.875rem; font-weight: 800; color: #0f172a; margin: 0;">Bienvenido, <?php echo e(auth()->user()->name); ?></h1>
                <p style="color: #64748b; margin-top: 0.5rem; font-size: 1.125rem;">Gestiona tus bonos de servicio y sigue tus horas de soporte.</p>
            </header>

            <?php if(!$client): ?>
            <div style="background-color: #fef9c3; border-left: 4px solid #eab308; padding: 1rem; color: #854d0e; border-radius: 0.5rem;">
                No hay un cliente asociado a tu cuenta. Contacta al administrador.
            </div>
            <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">

                
                <div style="grid-column: span 2; background: white; border-radius: 1rem; padding: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                        <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">Saldo Actual</h3>
                        <span style="background: #eaffdb; color: #489211; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px;">ACTIVO</span>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 3rem;">
                        
                        <div style="position: relative; width: 180px; height: 180px;">
                            <svg width="180" height="180" viewBox="0 0 180 180" style="transform: rotate(-90deg);">
                                <circle cx="90" cy="90" r="80" fill="transparent" stroke="#f1f5f9" stroke-width="12"></circle>
                                <circle cx="90" cy="90" r="80" fill="transparent" stroke="#62bd19" stroke-width="12"
                                    stroke-dasharray="502.6"
                                    stroke-dashoffset="<?php echo e(502.6 * (1 - min(($balanceSeconds / 3600) / 40, 1))); ?>"
                                    stroke-linecap="round"></circle>
                            </svg>
                            <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <span style="font-size: 1.5rem; font-weight: 900; color: #0f172a;"><?php echo e(number_format($balanceSeconds / 3600, 1)); ?>h</span>
                                <span style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Disponibles</span>
                            </div>
                        </div>

                        <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; min-width: 250px;">
                            <div style="background: #f4faed; padding: 1rem; border-radius: 0.75rem; border: 1px solid #e1f2cf;">
                                <p style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0;">Validados</p>
                                <span style="font-size: 1.5rem; font-weight: 800; color: #489211;"><?php echo e($validated); ?></span>
                            </div>
                            <div style="background: #f8fafc; padding: 1rem; border-radius: 0.75rem; border: 1px solid #f1f5f9;">
                                <p style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0;">Finalizados</p>
                                <span style="font-size: 1.5rem; font-weight: 800; color: #0f172a;"><?php echo e($finished); ?></span>
                            </div>
                            <div style="grid-column: span 2; background: #f8fafc; padding: 1rem; border-radius: 0.75rem; border: 1px solid #f1f5f9;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    <span style="color: #64748b;">Tiempo Real:</span>
                                    <span style="font-weight: 700; color: #0f172a;"><?php echo e(gmdate('H:i:s', $balanceSeconds)); ?></span>
                                </div>
                                <div style="width: 100%; background: #e2e8f0; height: 8px; border-radius: 999px; overflow: hidden;">
                                    <div style="background: #62bd19; height: 100%; width: <?php echo e(min(($balanceSeconds / 3600) / 40 * 100, 100)); ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div style="background: white; border-radius: 1rem; padding: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem;">Última Intervención</h3>
                    <?php if($recentWorkReports->count() > 0): ?>
                    <?php $last = $recentWorkReports->first(); ?>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="background: #f4faed; color: #62bd19; padding: 0.5rem; border-radius: 0.5rem; display: flex;">
                                <span class="material-symbols-outlined text-[#62bd19] bg-[#62bd19]/10 ...">person</span>
                            </div>
                            <div>
                                <p style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 0;">Técnico</p>
                                <p style="font-weight: 700; color: #0f172a; margin: 0;"><?php echo e($last->technician->name); ?></p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="background: #f4faed; color: #62bd19; padding: 0.5rem; border-radius: 0.5rem; display: flex;">
                                <span class="material-symbols-outlined">description</span>
                            </div>
                            <div>
                                <p style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 0;">Título</p>
                                <p style="font-weight: 700; color: #0f172a; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 150px;"><?php echo e($last->title ?? 'Intervención'); ?></p>
                            </div>
                        </div>
                        <a href="<?php echo e(route('client.work-reports.index')); ?>" class="mt-4 block w-full text-center bg-slate-900 text-white font-bold py-2.5 rounded-lg hover:bg-[#62bd19] transition-colors shadow-sm">
                            Ver Todos
                        </a>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 2rem 0; color: #94a3b8;">
                        <span class="material-symbols-outlined" style="font-size: 3rem; margin-bottom: 0.5rem;">history</span>
                        <p style="font-style: italic; font-size: 0.875rem;">Sin registros</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 2rem;">Comprar Nuevos Bonos</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <?php $__currentLoopData = [['5 Horas', 'bolt', 'ESENCIAL'], ['12 Horas', 'rocket_launch', 'POPULAR'], ['20 Horas', 'diamond', 'ENTERPRISE']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; text-align: center; transition: transform 0.2s;">
                    <div style="background: #f8fafc; color: #64748b; padding: 0.75rem; border-radius: 0.75rem; width: fit-content; margin: 0 auto 1rem;">
                        <span class="material-symbols-outlined"><?php echo e($plan[1]); ?></span>
                    </div>
                    <span style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em;"><?php echo e($plan[2]); ?></span>
                    <h4 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0.5rem 0 1.5rem;"><?php echo e($plan[0]); ?></h4>
                    <button style="width: 100%; background: transparent; border: 2px solid #62bd19; color: #62bd19; padding: 0.6rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer;">Seleccionar</button>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
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
<?php /**PATH /var/www/src/resources/views/dashboard/client.blade.php ENDPATH**/ ?>