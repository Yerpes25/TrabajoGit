<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Parte') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        <em>Nota: Solo se pueden editar campos básicos (título, descripción, resumen). Los tiempos solo se modifican mediante el cronómetro.</em>
                    </p>

                    <form action="{{ route('technician.work-reports.update', $workReport) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="title" value="Título" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $workReport->title)" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" value="Descripción" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md" rows="4">{{ old('description', $workReport->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="summary" value="Resumen (opcional)" />
                            <textarea id="summary" name="summary" class="mt-1 block w-full border-gray-300 rounded-md" rows="4">{{ old('summary', $workReport->summary) }}</textarea>
                            <x-input-error :messages="$errors->get('summary')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Actualizar Parte</x-primary-button>
                            <a href="{{ route('technician.work-reports.show', $workReport) }}" class="text-gray-600">Cancelar</a>
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