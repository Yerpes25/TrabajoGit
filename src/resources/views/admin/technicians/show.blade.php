<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Técnico') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.technicians.index') }}" class="text-gray-600">← Volver a Técnicos</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Información del Técnico</h3>
                    <div class="space-y-2">
                        <p><strong>Nombre:</strong> {{ $technician->name }}</p>
                        <p><strong>Email:</strong> {{ $technician->email }}</p>
                        <p><strong>Estado:</strong> {{ $technician->is_active ? 'Activo' : 'Inactivo' }}</p>
                        <p><strong>Creado:</strong> {{ $technician->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.technicians.edit', $technician) }}" class="bg-blue-500 text-white px-4 py-2 rounded">Editar</a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Partes de Trabajo</h3>
                    @if($workReports->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Cliente</th>
                                    <th class="px-4 py-2 text-left">Título</th>
                                    <th class="px-4 py-2 text-left">Estado</th>
                                    <th class="px-4 py-2 text-left">Tiempo (horas)</th>
                                    <th class="px-4 py-2 text-left">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workReports as $report)
                                    <tr>
                                        <td class="px-4 py-2">{{ $report->client->name ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $report->title ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $report->status }}</td>
                                        <td class="px-4 py-2">{{ number_format($report->total_seconds / 3600, 2) }}h</td>
                                        <td class="px-4 py-2">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $workReports->links() }}
                        </div>
                    @else
                        <p>No hay partes de trabajo asociados.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
