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

                
                <div class="col-span-1 md:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 relative overflow-hidden group hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-[#62bd19]/10 rounded-full blur-3xl group-hover:bg-[#62bd19]/20 transition-colors duration-500"></div>

                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8 h-full">

                        <div class="flex-1 w-full flex flex-col items-center justify-center md:border-r border-slate-100 md:pr-8">

                            <div class="mb-6 bg-slate-50 border border-slate-200 shadow-sm rounded-2xl px-8 py-3 flex flex-col items-center justify-center">
                                <h3 class="text-slate-900 font-bold text-lg leading-tight">Saldo Actual</h3>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#62bd19] animate-pulse"></span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Bono Activo
                                    </span>
                                </div>
                            </div>

                            <?php
                            $horas = floor($balanceSeconds / 3600);
                            $minutos = floor(($balanceSeconds % 3600) / 60);
                            ?>

                            <div class="flex flex-col items-center gap-1.5">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-6xl font-black text-slate-900 tracking-tighter leading-none"><?php echo e($horas); ?></span>
                                    <span class="text-2xl text-slate-400 font-bold mr-2">h</span>

                                    <span class="text-6xl font-black text-slate-900 tracking-tighter leading-none"><?php echo e($minutos); ?></span>
                                    <span class="text-2xl text-slate-400 font-bold mb-4">m</span>
                                </div>

                                <span class="bg-slate-50 text-slate-600 text-[11px] font-bold px-3 py-1.5 rounded-xl border border-slate-200 inline-flex items-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-[14px] text-slate-400 ">timer</span>
                                    Formato exacto: <?php echo e(gmdate('H:i:s', $balanceSeconds)); ?>

                                </span>
                            </div>

                        </div>

                        <div class="flex-1 w-full flex flex-col justify-center items-center h-full md:pl-4">

                            <div class="w-full max-w-sm bg-gradient-to-br from-[#62bd19]/5 to-transparent border border-[#62bd19]/20 p-6 rounded-2xl flex items-center gap-6 transition-colors hover:border-[#62bd19]/40">
                                <div class="w-14 h-14 bg-white rounded-2xl shadow-sm flex items-center justify-center text-[#62bd19] shrink-0">
                                    <span class="material-symbols-outlined text-2xl">verified</span>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Partes Validados</p>
                                    <span class="text-4xl font-black text-[#62bd19] block leading-none"><?php echo e($validated); ?></span>
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

            
            <div x-data="{ showAll: false }" class="mt-16">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 2rem;">Comprar Nuevos Bonos</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php $__currentLoopData = $activeBonuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $bonus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $horas = floor($bonus->seconds_total / 3600);
                        $isPopular = ($index === 0);
                        $isMaxHours = ($bonus->id === $maxHoursId);
                        $name = strtolower($bonus->name);

                        // 1. Prioridad: Popularidad y Tamaño
                        if ($isPopular) {
                            $icon = 'crown';
                        } elseif ($isMaxHours) {
                            $icon = 'diamond';
                        }
                        // 2. Búsqueda por temática en el nombre
                        elseif (str_contains($name, 'web')) { $icon = 'language'; }
                        elseif (str_contains($name, 'tienda') || str_contains($name, 'online')) { $icon = 'shopping_cart'; }
                        elseif (str_contains($name, 'mantenimiento')) { $icon = 'settings'; }
                        elseif (str_contains($name, 'desarrollo')) { $icon = 'terminal'; }
                        elseif (str_contains($name, 'optimización')) { $icon = 'speed'; }
                        elseif (str_contains($name, 'básico') || $horas < 10) { $icon='bolt' ; }
                        // 3. Fallback: Rotación de iconos variados para que no se repita el "cubo"
                        else {
                            $variedIcons=['rocket_launch', 'auto_awesome' , 'verified' , 'auto_graph' , 'extension' ];
                            $icon=$variedIcons[$index % count($variedIcons)];
                        }
                    ?>

                    <div
                        x-show="showAll || <?php echo e($index); ?> < 3"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        
                        class="bg-white rounded-[2rem] border <?php echo e($isPopular ? 'border-[#62bd19]' : 'border-slate-100'); ?> p-10 flex flex-col items-center text-center relative group hover:shadow-xl transition-shadow duration-200"
                    >
                        <?php if($isPopular): ?>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#62bd19] text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-lg">
                            Más Popular
                        </div>
                        <?php endif; ?>

                        
                        <div class="w-20 h-20 <?php echo e($isPopular ? 'bg-[#f4faed] text-[#62bd19]' : 'bg-slate-50 text-slate-400'); ?> rounded-3xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-4xl"><?php echo e($icon); ?></span>
                        </div>

                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2"><?php echo e($bonus->name); ?></span>
                        <h4 class="text-3xl font-black text-slate-900 mb-8"><?php echo e($horas); ?> <span class="text-lg text-slate-400 font-medium">Horas</span></h4>

                        <button class="w-full py-4 px-6 rounded-2xl font-black transition-colors duration-200 <?php echo e($isPopular ? 'bg-[#62bd19] text-white hover:bg-[#52a115] shadow-lg shadow-green-100' : 'bg-white text-slate-900 border-2 border-slate-900 hover:bg-slate-900 hover:text-white'); ?>">
                            Seleccionar
                        </button>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <?php if($activeBonuses->count() > 3): ?>
                <div class="flex justify-center mt-12">
                    <button
                        @click="showAll = !showAll"
                        class="flex items-center gap-2 text-[11px] font-black text-[#62bd19] hover:text-[#4d9413] transition-colors tracking-[0.2em] outline-none border-none bg-transparent no-underline">
                        <span x-text="showAll ? 'MOSTRAR MENOS' : 'VER TODOS LOS BONOS'"></span>
                        <span class="material-symbols-outlined transition-transform duration-300" :class="showAll ? 'rotate-180' : ''">
                            expand_more
                        </span>
                    </button>
                </div>
                <?php endif; ?>
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
<?php endif; ?><?php /**PATH /var/www/src/resources/views/dashboard/client.blade.php ENDPATH**/ ?>