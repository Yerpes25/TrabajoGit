<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.clients.create') }}" class="px-4 py-2 rounded text-white transition-colors" style="background-color:#62bd19;" onmouseover="this.style.backgroundColor='#498d13';" onmouseout="this.style.backgroundColor='#62bd19';">
                    Crear Cliente
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
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Saldo (horas)</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td class="px-4 py-2">{{ $client->name }}</td>
                                <td class="px-4 py-2">{{ $client->email ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    {{ number_format(($client->profile->balance_seconds ?? 0) / 3600, 2) }}h
                                </td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="text-blue-500">Ver</a>
                                    <a href="{{ route('admin.clients.edit', $client) }}" class="text-blue-500 ml-2">Editar</a>

                                    <form action="{{ route('admin.users.toggle', $client->user) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Está seguro de {{ $client->user->is_active ? 'desactivar' : 'activar' }} este cliente?');">
                                        @csrf
                                        <button type="submit" class="{{ $client->user->is_active ? 'text-red-500' : 'text-green-500' }} ml-2">
                                            {{ $client->user->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $clients->links() }}
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
