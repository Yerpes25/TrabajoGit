<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle Cliente: ') . $client->name }}
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

            <!-- Información del cliente -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Información del Cliente</h3>
                    <p><strong>Nombre:</strong> {{ $client->name }}</p>
                    <p><strong>Email:</strong> {{ $client->email ?? '-' }}</p>
                    <p><strong>Teléfono:</strong> {{ $client->phone ?? '-' }}</p>
                    <p><strong>Saldo actual:</strong> {{ number_format($balanceSeconds / 3600, 2) }} horas ({{ $balanceSeconds }} segundos)</p>
                </div>
            </div>

            <!-- Asignar saldo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Asignar Saldo</h3>
                    <form action="{{ route('admin.clients.credit', $client) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="hours" value="Horas" />
                            <x-text-input id="hours" name="hours" type="number" step="0.01" min="0.01" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('hours')" class="mt-2" />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="reason" value="Motivo (opcional)" />
                            <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" value="admin_credit" />
                        </div>
                        <x-primary-button>Asignar Saldo</x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Movimientos de saldo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Movimientos de Saldo</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Fecha</th>
                                <th class="px-4 py-2 text-left">Tipo</th>
                                <th class="px-4 py-2 text-left">Cantidad (horas)</th>
                                <th class="px-4 py-2 text-left">Motivo</th>
                                <th class="px-4 py-2 text-left">Creado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($balanceMovements as $movement)
                                <tr>
                                    <td class="px-4 py-2">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2">{{ $movement->type }}</td>
                                    <td class="px-4 py-2">{{ number_format(abs($movement->amount_seconds) / 3600, 2) }}h</td>
                                    <td class="px-4 py-2">{{ $movement->reason }}</td>
                                    <td class="px-4 py-2">{{ $movement->creator->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $balanceMovements->links() }}
                    </div>
                </div>
            </div>

            <!-- Emitir bono -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Emitir Bono</h3>
                    <form action="{{ route('admin.clients.bonuses.issue', $client) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="bonus_id" value="Bono" />
                            <select id="bonus_id" name="bonus_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                                <option value="">Seleccionar bono</option>
                                @foreach($activeBonuses as $bonus)
                                    <option value="{{ $bonus->id }}">{{ $bonus->name }} ({{ number_format($bonus->seconds_total / 3600, 2) }}h)</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('bonus_id')" class="mt-2" />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="note" value="Nota (opcional)" />
                            <textarea id="note" name="note" class="mt-1 block w-full border-gray-300 rounded-md" rows="2">{{ old('note') }}</textarea>
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>
                        <x-primary-button>Emitir Bono</x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Bonos emitidos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Bonos Emitidos</h3>
                    @if($bonusIssues->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Bono</th>
                                    <th class="px-4 py-2 text-left">Tiempo (horas)</th>
                                    <th class="px-4 py-2 text-left">Nota</th>
                                    <th class="px-4 py-2 text-left">Emitido por</th>
                                    <th class="px-4 py-2 text-left">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bonusIssues as $issue)
                                    <tr>
                                        <td class="px-4 py-2">{{ $issue->bonus->name }}</td>
                                        <td class="px-4 py-2">{{ number_format($issue->seconds_total / 3600, 2) }}h</td>
                                        <td class="px-4 py-2">{{ $issue->note ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $issue->issuer->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $issue->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $bonusIssues->links() }}
                        </div>
                    @else
                        <p>No hay bonos emitidos para este cliente.</p>
                    @endif
                </div>
            </div>

            <!-- Partes del cliente -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Partes de Trabajo</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Título</th>
                                <th class="px-4 py-2 text-left">Técnico</th>
                                <th class="px-4 py-2 text-left">Estado</th>
                                <th class="px-4 py-2 text-left">Tiempo (horas)</th>
                                <th class="px-4 py-2 text-left">Fecha</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workReports as $report)
                                <tr>
                                    <td class="px-4 py-2">{{ $report->title ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $report->technician->name }}</td>
                                    <td class="px-4 py-2">{{ $report->status }}</td>
                                    <td class="px-4 py-2">{{ number_format($report->total_seconds / 3600, 2) }}h</td>
                                    <td class="px-4 py-2">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('admin.work-reports.show', $report) }}" class="text-blue-500">Ver</a>
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
