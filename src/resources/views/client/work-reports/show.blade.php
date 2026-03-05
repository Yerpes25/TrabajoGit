<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle Parte: ') . ($workReport->title ?? 'Sin título') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del parte -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Información del Parte</h3>
                    <p><strong>Técnico:</strong> {{ $workReport->technician->name }}</p>
                    <p><strong>Título:</strong> {{ $workReport->title ?? '-' }}</p>
                    <p><strong>Descripción:</strong> {{ $workReport->description ?? '-' }}</p>
                    @if($workReport->summary)
                        <p><strong>Resumen:</strong> {{ $workReport->summary }}</p>
                    @endif
                    <p><strong>Estado:</strong> 
                        <span class="px-2 py-1 rounded text-xs
                            @if($workReport->status === 'finished') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $workReport->status }}
                        </span>
                    </p>
                    <p><strong>Tiempo total:</strong> {{ gmdate('H:i:s', $workReport->total_seconds) }} ({{ $workReport->total_seconds }} segundos)</p>
                    @if($workReport->finished_at)
                        <p><strong>Finalizado:</strong> {{ $workReport->finished_at->format('d/m/Y H:i') }}</p>
                    @endif
                    @if($workReport->validated_at)
                        <p><strong>Validado por:</strong> {{ $workReport->validator->name ?? '-' }} el {{ $workReport->validated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

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
                                            <a href="{{ route('evidences.download', $evidence) }}" class="text-indigo-600 hover:text-indigo-900">Descargar</a>
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
