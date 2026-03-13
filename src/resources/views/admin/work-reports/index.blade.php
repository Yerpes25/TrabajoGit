<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Partes de Trabajo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.work-reports.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <x-input-label for="client_id" value="Cliente" />
                            <select id="client_id" name="client_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Todos</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="technician_id" value="Técnico" />
                            <select id="technician_id" name="technician_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Todos</option>
                                @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" {{ request('technician_id') == $technician->id ? 'selected' : '' }}>
                                    {{ $technician->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="status" value="Estado" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Todos</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Pausado</option>
                                <option value="finished" {{ request('status') == 'finished' ? 'selected' : '' }}>Finalizado</option>
                                <option value="validated" {{ request('status') == 'validated' ? 'selected' : '' }}>Validado</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="date_from" value="Desde" />
                            <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="request('date_from')" />
                        </div>
                        <div>
                            <x-input-label for="date_to" value="Hasta" />
                            <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="request('date_to')" />
                        </div>
                        <div class="md:col-span-5">
                            <x-primary-button style="background-color:#62bd19; color:white;" onmouseover="this.style.backgroundColor='#498d13';" onmouseout="this.style.backgroundColor='#62bd19';">
                                Filtrar
                            </x-primary-button>
                            <a href="{{ route('admin.work-reports.index') }}" class="text-gray-600 ml-2">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Cliente</th>
                                <th class="px-4 py-2 text-left">Técnico</th>
                                <th class="px-4 py-2 text-left">Título</th>
                                <th class="px-4 py-2 text-left">Estado</th>
                                <th class="px-4 py-2 text-left">Tiempo (horas)</th>
                                <th class="px-4 py-2 text-left">Fecha</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workReports as $report)
                            <tr>
                                <td class="px-4 py-2">{{ $report->client->user->name }}</td>
                                <td class="px-4 py-2">{{ $report->technician->name }}</td>
                                <td class="px-4 py-2">{{ $report->title ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $report->status }}</td>
                                <td class="px-4 py-2">{{ number_format($report->total_seconds / 3600, 2) }}h</td>
                                <td class="px-4 py-2">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('admin.work-reports.show', $report) }}" class="transition-colors" style="color:#62bd19;" onmouseover="this.style.color='#498d13';" onmouseout="this.style.color='#62bd19';">
                                        Ver Detalle
                                    </a>
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
                <x-back-button href="{{ route('admin.dashboard') }}">
                    Volver
                </x-back-button>
            </div>
        </div>
    </div>
</x-app-layout>
