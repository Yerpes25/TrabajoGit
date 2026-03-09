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

                    @if($errors->has('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ $errors->first('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.work-reports.update', $workReport) }}" method="POST">
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
                            <a href="{{ route('admin.work-reports.show', $workReport) }}" class="text-gray-600">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
