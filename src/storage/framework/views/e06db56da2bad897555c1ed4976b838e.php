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
        <h2 class="text-xl font-bold">
            Detalle Parte: <?php echo e($workReport->title ?? 'Sin título'); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="page-container">

        
        <?php if(session('success')): ?>
            <div class="card" style="background:#ecfdf5;border-color:#bbf7d0">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="card" style="background:#fef2f2;border-color:#fecaca">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <br>

        
        <div class="stats-grid">
            <div class="card">
                <div class="stat-title">Cliente</div>
                <div class="stat-value"><?php echo e($workReport->client?->user?->name ?? '-'); ?></div>
            </div>
            <div class="card">
                <div class="stat-title">Estado</div>

                <?php
                    $status = $workReport->status ?? 'desconocido';
                    $labels = [
                        'in_progress' => 'En progreso',
                        'paused' => 'Pausado',
                        'finished' => 'Finalizado',
                        'validated' => 'Validado'
                    ];
                    $colors = [
                        'in_progress' => 'background-color: #3b82f6; color: white;',
                        'paused' => 'background-color: #f59e0b; color: white;',
                        'finished' => 'background-color: #10b981; color: white;',
                        'validated' => 'background-color: #6b7280; color: white;'
                    ];
                    $label = $labels[$status] ?? ucfirst($status);
                    $style = $colors[$status] ?? 'background-color: #d1d5db; color: black;';
                ?>

                <div style="<?php echo e($style); ?> width: 100%; text-align: center; font-weight: 600; padding: 10px 0; border-radius: 8px; font-size: 1rem;">
                    <?php echo e($label); ?>

                </div>
            </div>
            <div class="card">
                <div class="stat-title">Tiempo total</div>
                <div id="cronometro" class="stat-value">
                    <?php echo e(gmdate('H:i:s', $workReport->total_seconds)); ?>

                </div>
            </div>
        </div>

        
        <div class="card section">
            <div style="display:flex;justify-content:space-between;align-items:start">
                <div>
                    <div class="section-title">Información del Parte</div>
                    <p><strong>Título:</strong> <?php echo e($workReport->title ?? '-'); ?></p>
                    <p><strong>Descripción:</strong> <?php echo e($workReport->description ?? '-'); ?></p>
                    <?php if($workReport->finished_at): ?>
                        <p><strong>Finalizado:</strong> <?php echo e($workReport->finished_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                </div>
                <?php if($workReport->status !== 'validated'): ?>
                    <a href="<?php echo e(route('technician.work-reports.edit', $workReport)); ?>" style="color:#197fe6;font-weight:600">
                        Editar
                    </a>
                <?php endif; ?>
            </div>

            
            <div style="display:flex; gap:10px; margin-top:20px;">
                <?php if($workReport->status !== 'finished'): ?>
                    <?php if($workReport->status === 'paused'): ?>
                        <form action="<?php echo e(route('technician.work-reports.start', $workReport)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-blue">Iniciar</button>
                        </form>
                    <?php endif; ?>

                    <?php if($workReport->status === 'in_progress'): ?>
                        <form action="<?php echo e(route('technician.work-reports.pause', $workReport)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-blue">Pausar</button>
                        </form>
                    <?php endif; ?>

                    
                    <?php if($workReport->status !== 'validated'): ?>
                        <form id="finishForm" action="<?php echo e(route('technician.work-reports.finish', $workReport)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button id="finishButton" class="btn btn-blue" disabled style="opacity:0.5; cursor:not-allowed;">Finalizar</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <form action="<?php echo e(route('technician.work-reports.validate', $workReport)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-blue">Validar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($workReport->status !== 'validated'): ?>
            <div class="card section">
                <form action="<?php echo e(route('technician.work-reports.evidences.upload', $workReport)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="section-title">Subir Evidencia</div>
                    <input type="file" name="file" id="fileInput" class="hidden-input" accept="image/*,.pdf">

                    <div class="upload-zone" id="uploadZone" style="cursor:pointer; border: 2px dashed #cbd5e1; padding: 20px; text-align: center;">
                        <span class="material-symbols-outlined upload-icon"> cloud_upload </span>
                        <div class="upload-title"> Arrastra archivos aquí </div>
                        <div class="upload-sub"> o haz click para seleccionar imágenes o PDFs </div>
                        <div id="fileName" style="margin-top:10px;font-size:14px;color:#475569"></div>
                    </div>
                    <br>
                    <button class="btn btn-blue" id="uploadButton" disabled style="opacity:0.5; cursor:not-allowed;">Subir evidencia</button>
                </form>
            </div>
        <?php endif; ?>

        
        <div class="section">
            <div class="section-title">Evidencias</div>
            <?php if($workReport->evidences->count() > 0): ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tamaño</th>
                        <th>Subido por</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $workReport->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evidence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($evidence->original_name); ?></td>
                            <td><?php echo e(number_format($evidence->size_bytes/1024,2)); ?> KB</td>
                            <td><?php echo e($evidence->uploader?->name ?? '-'); ?></td>
                            <td><?php echo e($evidence->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <a href="<?php echo e(route('evidences.download',$evidence)); ?>" style="color:#197fe6;font-weight:600">
                                    Descargar
                                </a>
                                <?php if($workReport->status !== 'validated'): ?>
                                    <form action="<?php echo e(route('technician.evidences.delete',$evidence)); ?>" method="POST" style="display:inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button style="color:red;margin-left:10px" onclick="return confirm('¿Eliminar evidencia?')"> Eliminar </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay evidencias asociadas.</p>
            <?php endif; ?>
        </div>

        
        <div class="mt-6 flex justify-start">
            <a href="<?php echo e(route('technician.dashboard')); ?>"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>

    
    <script>
        const crono = document.getElementById('cronometro');

        let running = "<?php echo e($workReport->status); ?>" === "<?php echo e(\App\Models\WorkReport::STATUS_IN_PROGRESS); ?>";

        const finishButton = document.getElementById('finishButton');
        let totalSeconds = <?php echo e($workReport->total_seconds ?? 0); ?>;
        const status = "<?php echo e($workReport->status); ?>";
        const activeStart = "<?php echo e($workReport->active_started_at); ?>";

        function updateFinishButton(){
            if(finishButton){
                if(totalSeconds > 0){
                    finishButton.disabled = false;
                    finishButton.style.opacity = 1;
                    finishButton.style.cursor = 'pointer';
                } else {
                    finishButton.disabled = true;
                    finishButton.style.opacity = 0.5;
                    finishButton.style.cursor = 'not-allowed';
                }
            }
        }

        updateFinishButton(); // Estado inicial

        // Si está en progreso, iniciar cronómetro dinámico
        if(status === "<?php echo e(\App\Models\WorkReport::STATUS_IN_PROGRESS); ?>" && activeStart){
            let totalSecondsDynamic = totalSeconds + Math.floor((Date.now() - new Date(activeStart)) / 1000);

            setInterval(()=>{
                totalSecondsDynamic++;
                if(crono){
                    let h = Math.floor(totalSecondsDynamic / 3600);
                    let m = Math.floor((totalSecondsDynamic % 3600) / 60);
                    let s = totalSecondsDynamic % 60;
                    crono.innerText = `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
                }
                totalSeconds = totalSecondsDynamic;
                updateFinishButton();
            },1000);
        }
    </script>

    
    <script>
        const fileInput = document.getElementById('fileInput');
        const uploadButton = document.getElementById('uploadButton');
        const fileNameDiv = document.getElementById('fileName');
        const uploadZone = document.getElementById('uploadZone');

        if(fileInput && uploadButton && uploadZone){
            // Click en zona
            uploadZone.addEventListener('click', ()=> fileInput.click());

            // Drag & Drop
            uploadZone.addEventListener('dragover', (e)=> { e.preventDefault(); uploadZone.classList.add('dragover'); });
            uploadZone.addEventListener('dragleave', ()=> { uploadZone.classList.remove('dragover'); });
            uploadZone.addEventListener('drop', (e)=> {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                if(e.dataTransfer.files.length){
                    fileInput.files = e.dataTransfer.files;
                    fileNameDiv.innerText = e.dataTransfer.files[0].name;
                    uploadButton.disabled = false;
                    uploadButton.style.opacity = 1;
                    uploadButton.style.cursor = 'pointer';
                }
            });

            // Cambio manual input
            fileInput.addEventListener('change', ()=>{
                if(fileInput.files.length > 0){
                    fileNameDiv.innerText = fileInput.files[0].name;
                    uploadButton.disabled = false;
                    uploadButton.style.opacity = 1;
                    uploadButton.style.cursor = 'pointer';
                } else {
                    fileNameDiv.innerText = '';
                    uploadButton.disabled = true;
                    uploadButton.style.opacity = 0.5;
                    uploadButton.style.cursor = 'not-allowed';
                }
            });
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH /var/www/src/resources/views/technician/work-reports/show.blade.php ENDPATH**/ ?>