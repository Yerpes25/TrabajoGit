<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle Parte: ') . ($workReport->title ?? 'Sin título') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Información del parte -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Información del Parte</h3>
                            <p><strong>Cliente:</strong> {{ $workReport->client->name }}</p>
                            <p><strong>Título:</strong> {{ $workReport->title ?? '-' }}</p>
                            <p><strong>Descripción:</strong> {{ $workReport->description ?? '-' }}</p>
                            <p><strong>Estado:</strong>
                                <span class="px-2 py-1 rounded text-xs
                                    @if($workReport->status === 'in_progress') bg-green-100 text-green-800
                                    @elseif($workReport->status === 'paused') bg-yellow-100 text-yellow-800
                                    @elseif($workReport->status === 'finished') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $workReport->status }}
                                </span>
                            </p>
                            <p><strong>Tiempo total:</strong> {{ gmdate('H:i:s', $workReport->total_seconds) }} ({{ $workReport->total_seconds }} segundos)</p>
                            @if($workReport->finished_at)
                                <p><strong>Finalizado:</strong> {{ $workReport->finished_at->format('d/m/Y H:i') }}</p>
                                <br>
                                <form action="{{ route('technician.work-reports.validate', $workReport) }}" method="POST" class="inline">
                                    @csrf
                                    <x-primary-button type="submit">Validar</x-primary-button>
                                </form>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('technician.work-reports.edit', $workReport) }}" class="text-blue-500">Editar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones del cronómetro -->
            @if($workReport->status !== 'validated' && $workReport->status !== 'finished')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Acciones del Cronómetro</h3>
                        <div class="flex gap-4">
                            @if($workReport->status === 'paused')
                                <form action="{{ route('technician.work-reports.start', $workReport) }}" method="POST" class="inline">
                                    @csrf
                                    <x-primary-button type="submit">Iniciar</x-primary-button>
                                </form>
                                <form action="{{ route('technician.work-reports.resume', $workReport) }}" method="POST" class="inline">
                                    @csrf
                                    <x-primary-button type="submit">Reanudar</x-primary-button>
                                </form>
                                <form action="{{ route('technician.work-reports.finish', $workReport) }}" method="POST" class="inline">
                                    @csrf
                                    <x-primary-button type="submit">Finalizar</x-primary-button>
                                </form>
                            @elseif($workReport->status === 'in_progress')
                                <form action="{{ route('technician.work-reports.pause', $workReport) }}" method="POST" class="inline">
                                    @csrf
                                    <x-primary-button type="submit">Pausar</x-primary-button>
                                </form>
                                <form action="{{ route('technician.work-reports.finish', $workReport) }}" method="POST" class="inline">
                                    @csrf
                                    <x-primary-button type="submit">Finalizar</x-primary-button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Eventos del cronómetro -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Eventos del Cronómetro</h3>
                    @if($workReport->events->count() > 0)
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
                                @foreach($workReport->events as $event)
                                    <tr>
                                        <td class="px-4 py-2">{{ $event->type }}</td>
                                        <td class="px-4 py-2">{{ $event->occurred_at->format('d/m/Y H:i:s') }}</td>
                                        <td class="px-4 py-2">{{ gmdate('H:i:s', $event->elapsed_seconds_after) }}</td>
                                        <td class="px-4 py-2">{{ $event->creator->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No hay eventos registrados.</p>
                    @endif
                </div>
            </div>

            <!-- Subir evidencia -->
            @if($workReport->status !== 'validated')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Subir Evidencia</h3>
                        <form action="{{ route('technician.work-reports.evidences.upload', $workReport) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="file" value="Archivo" />
                                <input id="file" name="file" type="file" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            </div>
                            <x-primary-button>Subir Evidencia</x-primary-button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Evidencias -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Evidencias</h3>
                    @if($workReport->evidences->count() > 0)
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
                                @foreach($workReport->evidences as $evidence)
                                    <tr>
                                        <td class="px-4 py-2">{{ $evidence->original_name }}</td>
                                        <td class="px-4 py-2">{{ number_format($evidence->size_bytes / 1024, 2) }} KB</td>
                                        <td class="px-4 py-2">{{ $evidence->uploader->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $evidence->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('evidences.download', $evidence) }}" class="text-blue-500 hover:text-blue-700">Descargar</a>
                                            @if($workReport->status !== 'validated')
                                                <form action="{{ route('technician.evidences.delete', $evidence) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 ml-2" onclick="return confirm('¿Está seguro de eliminar esta evidencia?')">Eliminar</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No hay evidencias asociadas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
