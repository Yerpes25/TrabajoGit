<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(!$client)
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                    No hay un cliente asociado a tu cuenta. Por favor, contacta al administrador.
                </div>
            @else
                <!-- Resumen por estado -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-500">Finalizados</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $finished }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-500">Validados</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $validated }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-500">Saldo Disponible</h3>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ number_format($balanceSeconds / 3600, 2) }} horas
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ gmdate('H:i:s', $balanceSeconds) }}</p>
                    </div>
                </div>

                <!-- Partes recientes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Partes Recientes</h3>
                            <a href="{{ route('client.work-reports.index') }}" class="text-blue-500">Ver Todos</a>
                        </div>
                        @if($recentWorkReports->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Técnico</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Título</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Estado</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tiempo</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($recentWorkReports as $report)
                                            <tr>
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
                                                    <a href="{{ route('client.work-reports.show', $report) }}" class="text-blue-500">Ver</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500">No hay partes recientes.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
