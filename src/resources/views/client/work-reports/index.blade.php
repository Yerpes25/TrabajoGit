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
            <div class="mt-6 flex justify-start">
                <a href="{{ route('dashboard') }}"
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