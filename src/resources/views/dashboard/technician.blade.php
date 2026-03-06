<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Técnico') }}
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Clientes con Bonos</h3>
                    @if(isset($clientsWithBonuses) && $clientsWithBonuses->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Cliente</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Teléfono</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Total Bonos</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($clientsWithBonuses as $client)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $client->name }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $client->email ?? '-' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $client->phone ?? '-' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap font-bold text-blue-600">{{ $client->bonus_issues_count }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <a href="{{ route('technician.work-reports.create', ['client_id' => $client->id]) }}" class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm font-semibold hover:bg-blue-200 transition-colors">
                                            Crear Parte
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500">No hay clientes con bonos.</p>
                    @endif
                </div>
            </div>
            <!-- Resumen por estado -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">En Progreso</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $inProgress }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Pausados</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $paused }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Finalizados</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $finished }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Validados</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $validated }}</p>
                </div>
            </div>

            <!-- Partes recientes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Partes Recientes</h3>
                        <a href="{{ route('technician.work-reports.index') }}" class="text-blue-500">Ver Todos</a>
                    </div>
                    @if($recentWorkReports->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cliente</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Título</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Estado</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tiempo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentWorkReports as $report)
                                <tr>
                                    <td class="px-4 py-2">{{ $report->client->name }}</td>
                                    <td class="px-4 py-2">{{ $report->title ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $report->status }}</td>
                                    <td class="px-4 py-2">{{ gmdate('H:i:s', $report->total_seconds) }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('technician.work-reports.show', $report) }}" class="text-blue-500">Ver</a>
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

            <!-- Acciones rápidas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Acciones Rápidas</h3>
                    <div class="flex gap-4">
                        <a href="{{ route('technician.work-reports.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
                            Crear Nuevo Parte
                        </a>
                        <a href="{{ route('technician.work-reports.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">
                            Ver Todos los Partes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>