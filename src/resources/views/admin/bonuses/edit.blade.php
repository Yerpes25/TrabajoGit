<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Bono') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.bonuses.update', $bonus) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" value="Nombre" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $bonus->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" value="Descripción (opcional)" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md" rows="3">{{ old('description', $bonus->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="seconds_total" value="Tiempo (segundos)" />
                            <x-text-input id="seconds_total" name="seconds_total" type="number" min="1" class="mt-1 block w-full" :value="old('seconds_total', $bonus->seconds_total)" required />
                            <p class="mt-1 text-sm text-gray-500">Ejemplo: 3600 = 1 hora, 7200 = 2 horas</p>
                            <x-input-error :messages="$errors->get('seconds_total')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ $bonus->is_active ? 'checked' : '' }} class="rounded">
                                <span class="ml-2">Bono activo</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Actualizar Bono</x-primary-button>
                            <a href="{{ route('admin.bonuses.index') }}" class="text-gray-600">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
