<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center max-w-7xl mx-auto">

            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Panel de Técnico') }}
            </h2>

            <div class="flex gap-3">
                <a href="{{ route('technician.work-reports.create') }}"
                   class="btn-primary px-8 py-2 rounded-lg shadow transition flex items-center justify-center whitespace-nowrap text-center"
                   style="background-color:#1453A1; color:white;"
                   onmouseover="this.style.backgroundColor='#1962BD';"
                   onmouseout="this.style.backgroundColor='#1453A1';">
                    Crear Nuevo Parte
                </a>

                <a href="{{ route('technician.work-reports.index') }}"
                   class="btn-secondary px-6 py-2 rounded-lg shadow hover:bg-gray-300 transition flex items-center justify-center whitespace-nowrap text-center">
                    Ver Todos
                </a>
            </div>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- MENSAJES --}}
            @if(session('error'))
                <div class="card" style="background:#fef2f2;border-color:#fecaca">
                    {{ session('error') }}
                </div>
            @endif

            <br>

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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                @forelse($availableClients as $client)
                    {{-- Aquí le pasamos la variable $client al componente --}}
                    <x-work-report-card :client="$client" />
                @empty
                    <div class="col-span-full p-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                        No hay clientes con saldo disponible en este momento.
                    </div>
                @endforelse
            </div>
            <br>
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
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $client->user->name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $client->user->email ?? '-' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $client->phone ?? '-' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap font-bold" style="color:#1962BD;">{{ $client->bonus_issues_count }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <a href="{{ route('technician.work-reports.create', ['client_id' => $client->id]) }}"
                                               class="px-3 py-1 rounded text-sm font-semibold transition-colors"
                                               style="background-color:#0F4585; color:white;"
                                               onmouseover="this.style.backgroundColor='#1962BD';"
                                               onmouseout="this.style.backgroundColor='#0F4585';">
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
                                        <td class="px-4 py-2">{{ $report->client->user->name }}</td>
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

        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const cronos = document.querySelectorAll('[id^="crono-"]');

        cronos.forEach(cronoEl => {

            if(cronoEl.dataset.running !== "1") return;

            let totalSeconds = parseInt(cronoEl.dataset.totalSeconds);

            const startedAt = new Date(cronoEl.dataset.startedAt);
            const diffSeconds = Math.floor((Date.now() - startedAt.getTime()) / 1000);
            totalSeconds += diffSeconds;

            const updateCrono = () => {

                let segundos = totalSeconds;
                let horas = Math.floor(segundos/3600);
                segundos %= 3600;
                let minutos = Math.floor(segundos/60);
                segundos %= 60;

                cronoEl.innerText =
                    horas.toString().padStart(2,'0') + ':' +
                    minutos.toString().padStart(2,'0') + ':' +
                    segundos.toString().padStart(2,'0');

                totalSeconds++;
            };

            updateCrono();
            setInterval(updateCrono, 1000);

        });

    });
</script>
