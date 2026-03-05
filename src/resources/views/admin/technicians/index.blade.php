<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Técnicos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.technicians.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Crear Técnico</a>
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
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Activo</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($technicians as $technician)
                                <tr>
                                    <td class="px-4 py-2">{{ $technician->name }}</td>
                                    <td class="px-4 py-2">{{ $technician->email }}</td>
                                    <td class="px-4 py-2">{{ $technician->is_active ? 'Sí' : 'No' }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('admin.technicians.show', $technician) }}" class="text-blue-500">Ver</a>
                                        <a href="{{ route('admin.technicians.edit', $technician) }}" class="text-blue-500 ml-2">Editar</a>
                                        <form action="{{ route('admin.technicians.destroy', $technician) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este técnico?');">
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
                        {{ $technicians->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
