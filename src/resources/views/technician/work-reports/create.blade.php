<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Parte') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('technician.work-reports.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="client_id" value="Cliente" />
                            <select id="client_id" name="client_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                                <option value="">Seleccione un cliente</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="title" value="Título (opcional)" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" value="Descripción (opcional)" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md" rows="4">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Crear Parte</x-primary-button>
                            <a href="{{ route('technician.work-reports.index') }}" class="text-gray-600">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
            @php
            $urlAnterior = url()->previous();
            // Evitamos bucles si la página anterior era otra de crear o editar
            $vieneDeFormulario = str_contains($urlAnterior, 'create') || str_contains($urlAnterior, 'edit');
            $rutaVolver = $vieneDeFormulario ? route('technician.dashboard') : $urlAnterior;
            @endphp
            <div class="mt-6 flex justify-start">
                <a href="{{ $rutaVolver }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>
</x-app-layout>