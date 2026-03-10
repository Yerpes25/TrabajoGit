<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle Parte: ') . ($workReport->title ?? 'Sin título') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensajes de sesión -->
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
                    <h3 class="text-lg font-semibold mb-4">Información del Parte</h3>
                    <p><strong>Cliente:</strong> {{ $workReport->client->user->name }}</p>
                    <p><strong>Técnico:</strong> {{ $workReport->technician->name }}</p>
                    <p><strong>Título:</strong> {{ $workReport->title ?? '-' }}</p>
                    <p><strong>Descripción:</strong> {{ $workReport->description ?? '-' }}</p>
                    <p><strong>Estado:</strong> {{ $workReport->status }}</p>
                    <p><strong>Tiempo total:</strong> {{ gmdate('H\h i\m', $workReport->total_seconds) }}</p>
                    @if($workReport->finished_at && !$workReport->validated_at)
                        <p><strong>Finalizado:</strong> {{ $workReport->finished_at->format('d/m/Y H:i') }}</p>
                        <br>
                        <form action="{{ route('technician.work-reports.validate', $workReport) }}" method="POST" class="inline">
                            @csrf
                            <x-primary-button type="submit">Validar</x-primary-button>
                        </form>
                    @endif

                    @if($workReport->validated_at)
                        <br>
                        <p><strong>Finalizado:</strong> {{ $workReport->finished_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Validado por:</strong> {{ $workReport->validator->name ?? '-' }} el {{ $workReport->validated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

            <!-- Eventos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Eventos del Cronómetro</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Tipo</th>
                            <th class="px-4 py-2 text-left">Fecha/Hora</th>
                            <th class="px-4 py-2 text-left">Tiempo Acumulado (horas)</th>
                            <th class="px-4 py-2 text-left">Creado por</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($workReport->events as $event)
                            <tr>
                                <td class="px-4 py-2">{{ $event->type }}</td>
                                <td class="px-4 py-2">{{ $event->occurred_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-2">{{ number_format($event->elapsed_seconds_after / 3600, 2) }}h</td>
                                <td class="px-4 py-2">{{ $event->creator->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Evidencias -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Evidencias</h3>
                    @if($workReport->evidences->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
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
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
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
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-gray-500">No hay evidencias asociadas.</p>
                    @endif
                </div>
            </div> @php
            $urlAnterior = url()->previous();
            // Si la ruta anterior no existe o es la misma, mandamos al listado general por defecto
            $rutaVolver = ($urlAnterior == url()->current()) ? route('admin.work-reports.index') : $urlAnterior;
            @endphp
            <div class="mt-6 flex justify-start">
                <a href="{{ $rutaVolver }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
