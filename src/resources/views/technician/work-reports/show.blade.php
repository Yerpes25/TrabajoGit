<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">
            Detalle Parte: {{ $workReport->title ?? 'Sin título' }}
        </h2>
    </x-slot>

    <div class="page-container">

        {{-- MENSAJES --}}
        @if(session('success'))
            <div class="card" style="background:#ecfdf5;border-color:#bbf7d0">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="card" style="background:#fef2f2;border-color:#fecaca">
                {{ session('error') }}
            </div>
        @endif

        <br>

        {{-- CARDS SUPERIORES --}}
        <div class="stats-grid">
            <div class="card">
                <div class="stat-title">Cliente</div>
                <div class="stat-value">{{ $workReport->client?->user?->name ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="stat-title">Estado</div>

                @php
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
                @endphp

                <div style="{{ $style }} width: 100%; text-align: center; font-weight: 600; padding: 10px 0; border-radius: 8px; font-size: 1rem;">
                    {{ $label }}
                </div>
            </div>
            <div class="card">
                <div class="stat-title">Tiempo total</div>
                <div id="cronometro" class="stat-value">
                    {{ gmdate('H:i:s', $workReport->total_seconds) }}
                </div>
            </div>
        </div>

        {{-- INFORMACIÓN DEL PARTE --}}
        <div class="card section">
            <div style="display:flex;justify-content:space-between;align-items:start">
                <div>
                    <div class="section-title">Información del Parte</div>
                    <p><strong>Título:</strong> {{ $workReport->title ?? '-' }}</p>
                    <p><strong>Descripción:</strong> {{ $workReport->description ?? '-' }}</p>
                    @if($workReport->finished_at)
                        <p><strong>Finalizado:</strong> {{ $workReport->finished_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                @if($workReport->status !== 'validated')
                    <a href="{{ route('technician.work-reports.edit', $workReport) }}" style="color:#197fe6;font-weight:600">
                        Editar
                    </a>
                @endif
            </div>

            {{-- ACCIONES DEL PARTE --}}
            <div style="display:flex; gap:10px; margin-top:20px;">
                @if($workReport->status !== 'finished')
                    @if($workReport->status === 'paused')
                        <form action="{{ route('technician.work-reports.start', $workReport) }}" method="POST">
                            @csrf
                            <button class="btn btn-blue">Iniciar</button>
                        </form>
                    @endif

                    @if($workReport->status === 'in_progress')
                        <form action="{{ route('technician.work-reports.pause', $workReport) }}" method="POST">
                            @csrf
                            <button class="btn btn-blue">Pausar</button>
                        </form>
                    @endif

                    {{-- Botón Finalizar, deshabilitado hasta que total_seconds > 0 --}}
                    @if($workReport->status !== 'validated')
                        <form id="finishForm" action="{{ route('technician.work-reports.finish', $workReport) }}" method="POST">
                            @csrf
                            <button id="finishButton" class="btn btn-blue" disabled style="opacity:0.5; cursor:not-allowed;">Finalizar</button>
                        </form>
                    @endif
                @else
                    <form action="{{ route('technician.work-reports.validate', $workReport) }}" method="POST">
                        @csrf
                        <button class="btn btn-blue">Validar</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- FORMULARIO SUBIR EVIDENCIA --}}
        @if($workReport->status !== 'validated')
            <div class="card section">
                <form action="{{ route('technician.work-reports.evidences.upload', $workReport) }}" method="POST" enctype="multipart/form-data">
                    @csrf

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
        @endif

        {{-- LISTA DE EVIDENCIAS --}}
        <div class="section">
            <div class="section-title">Evidencias</div>
            @if($workReport->evidences->count() > 0)
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
                    @foreach($workReport->evidences as $evidence)
                        <tr>
                            <td>{{ $evidence->original_name }}</td>
                            <td>{{ number_format($evidence->size_bytes/1024,2) }} KB</td>
                            <td>{{ $evidence->uploader?->name ?? '-' }}</td>
                            <td>{{ $evidence->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('evidences.download',$evidence) }}" style="color:#197fe6;font-weight:600">
                                    Descargar
                                </a>
                                @if($workReport->status !== 'validated')
                                    <form action="{{ route('technician.evidences.delete',$evidence) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button style="color:red;margin-left:10px" onclick="return confirm('¿Eliminar evidencia?')"> Eliminar </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p>No hay evidencias asociadas.</p>
            @endif
        </div>

        {{-- BOTÓN VOLVER --}}
        <div class="mt-6 flex justify-start">
            <a href="{{ route('technician.dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>

    {{-- SCRIPT CRONÓMETRO Y FINALIZAR --}}
    <script>
        const crono = document.getElementById('cronometro');

        let running = "{{ $workReport->status }}" === "{{ \App\Models\WorkReport::STATUS_IN_PROGRESS }}";

        const finishButton = document.getElementById('finishButton');
        let totalSeconds = {{ $workReport->total_seconds ?? 0 }};
        const status = "{{ $workReport->status }}";
        const activeStart = "{{ $workReport->active_started_at }}";

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
        if(status === "{{ \App\Models\WorkReport::STATUS_IN_PROGRESS }}" && activeStart){
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

    {{-- SCRIPT SUBIR EVIDENCIA DRAG & DROP --}}
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
</x-app-layout>