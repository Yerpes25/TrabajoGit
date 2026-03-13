<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Bonos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.bonuses.create') }}" class="px-4 py-2 rounded text-white transition-colors" style="background-color:#62bd19;" onmouseover="this.style.backgroundColor='#498d13';" onmouseout="this.style.backgroundColor='#62bd19';">
                    Crear Bono
                </a>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Nombre</th>
                                <th class="px-4 py-2 text-left">Emisiones</th>
                                <th class="px-4 py-2 text-left">Tiempo (horas)</th>
                                <th class="px-4 py-2 text-left">Estado</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bonuses as $bonus)
                            <tr>
                                <td class="px-4 py-2">{{ $bonus->name }}</td>
                                <td class="px-4 py-2">{{ $bonus->count ?? 0 }}</td>
                                <td class="px-4 py-2">{{ number_format($bonus->seconds_total / 3600, 2) }}h</td>
                                <td class="px-4 py-2">{{ $bonus->is_active ? 'Activo' : 'Archivado' }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('admin.bonuses.show', $bonus) }}" class="text-blue-500">Ver</a>
                                    <a href="{{ route('admin.bonuses.edit', $bonus) }}" class="text-blue-500 ml-2">Editar</a>
                                    <form action="{{ route('admin.bonuses.destroy', $bonus) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este bono?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 ml-2">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $bonuses->links() }}
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
