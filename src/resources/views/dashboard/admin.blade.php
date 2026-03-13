<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- KPIs básicos -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Clientes</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalClients }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Técnicos</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalTechnicians }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Usuarios Activos</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $activeUsers }} / {{ $totalUsers }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Partes Validados</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $workReportsValidated }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-5 gap-4">
                <a href="{{ route('admin.clients.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Clientes</h4>
                    <p class="text-sm text-gray-500">Gestionar clientes y saldo</p>
                </a>
                <a href="{{ route('admin.technicians.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Técnicos</h4>
                    <p class="text-sm text-gray-500">Gestionar técnicos</p>
                </a>
                <a href="{{ route('admin.work-reports.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Partes</h4>
                    <p class="text-sm text-gray-500">Ver todos los partes</p>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Auditoría</h4>
                    <p class="text-sm text-gray-500">Consultar logs</p>
                </a>
                <a href="{{ route('admin.bonuses.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hover:bg-gray-50">
                    <h4 class="font-semibold">Bonos</h4>
                    <p class="text-sm text-gray-500">Crear bonos</p>
                </a>
            </div>

            <br>

            <!-- Partes por estado -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Partes por Estado</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">En Progreso:</span>
                            <span class="text-lg font-semibold ml-2">{{ $workReportsInProgress }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Finalizados:</span>
                            <span class="text-lg font-semibold ml-2">{{ $workReportsFinished }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Validados:</span>
                            <span class="text-lg font-semibold ml-2">{{ $workReportsValidated }}</span>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Partes recientes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Partes Recientes</h3>
                    @if($recentWorkReports->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cliente</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Técnico</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Estado</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tiempo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recentWorkReports as $report)
                                        <tr>
                                            <td class="px-4 py-2">{{ $report->client->user->name }}</td>
                                            <td class="px-4 py-2">{{ $report->technician->name }}</td>
                                            <td class="px-4 py-2">{{ $report->status }}</td>
                                            <td class="px-4 py-2">{{ number_format($report->total_seconds / 3600, 2) }}h</td>
                                            <td class="px-4 py-2">{{ $report->created_at->format('d/m/Y H:i') }}</td>
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
        </div>
    </div>
</x-app-layout>
