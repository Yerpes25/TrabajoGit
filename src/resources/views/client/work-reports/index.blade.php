<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Partes de Trabajo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        <em>Nota: Solo se muestran partes finalizados y validados.</em>
                    </p>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Técnico</th>
                                <th class="px-4 py-2 text-left">Título</th>
                                <th class="px-4 py-2 text-left">Estado</th>
                                <th class="px-4 py-2 text-left">Tiempo (hh:mm:ss)</th>
                                <th class="px-4 py-2 text-left">Finalizado</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workReports as $report)
                                <tr>
                                    <td class="px-4 py-2">#{{ $report->id }}</td>
                                    <td class="px-4 py-2">{{ $report->technician->name }}</td>
                                    <td class="px-4 py-2">{{ $report->title ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded text-xs
                                            @if($report->status === 'finished') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $report->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ gmdate('H:i:s', $report->total_seconds) }}</td>
                                    <td class="px-4 py-2">
                                        @if($report->finished_at)
                                            {{ $report->finished_at->format('d/m/Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('client.work-reports.show', $report) }}" class="text-blue-500">Ver Detalle</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $workReports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
