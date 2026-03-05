<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Bono') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.bonuses.index') }}" class="text-gray-600">← Volver a Bonos</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Información del Bono</h3>
                    <div class="space-y-2">
                        <p><strong>Nombre:</strong> {{ $bonus->name }}</p>
                        <p><strong>Descripción:</strong> {{ $bonus->description ?? '-' }}</p>
                        <p><strong>Tiempo:</strong> {{ number_format($bonus->seconds_total / 3600, 2) }} horas ({{ $bonus->seconds_total }} segundos)</p>
                        <p><strong>Estado:</strong> {{ $bonus->is_active ? 'Activo' : 'Archivado' }}</p>
                        <p><strong>Creado:</strong> {{ $bonus->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.bonuses.edit', $bonus) }}" class="bg-blue-500 text-white px-4 py-2 rounded">Editar</a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Emisiones del Bono</h3>
                    @if($bonusIssues->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Cliente</th>
                                    <th class="px-4 py-2 text-left">Tiempo (horas)</th>
                                    <th class="px-4 py-2 text-left">Nota</th>
                                    <th class="px-4 py-2 text-left">Emitido por</th>
                                    <th class="px-4 py-2 text-left">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bonusIssues as $issue)
                                    <tr>
                                        <td class="px-4 py-2">{{ $issue->client->name }}</td>
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
                        <p>No hay emisiones de este bono.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
