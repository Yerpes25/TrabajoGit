<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.clients.update', $client) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-4">Datos del Usuario</h3>

                        <div class="mb-4">
                            <x-input-label for="name" value="Nombre del Usuario" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $client->user->name ?? '')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="email" value="Email del Usuario" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $client->user->email ?? '')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password" value="Nueva Contraseña (opcional)" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password_confirmation" value="Confirmar Nueva Contraseña" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ ($client->user->is_active ?? true) ? 'checked' : '' }} class="rounded">
                                <span class="ml-2">Usuario activo</span>
                            </label>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 mt-6">Datos del Cliente</h3>

                        <div class="mb-4">
                            <x-input-label for="client_name" value="Nombre del Cliente" />
                            <x-text-input id="client_name" name="client_name" type="text" class="mt-1 block w-full" :value="old('client_name', $client->name)" required />
                            <x-input-error :messages="$errors->get('client_name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="legal_name" value="Razón Social (opcional)" />
                            <x-text-input id="legal_name" name="legal_name" type="text" class="mt-1 block w-full" :value="old('legal_name', $client->legal_name)" />
                            <x-input-error :messages="$errors->get('legal_name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="tax_id" value="NIF/CIF (opcional)" />
                            <x-text-input id="tax_id" name="tax_id" type="text" class="mt-1 block w-full" :value="old('tax_id', $client->tax_id)" />
                            <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="client_email" value="Email del Cliente (opcional)" />
                            <x-text-input id="client_email" name="client_email" type="email" class="mt-1 block w-full" :value="old('client_email', $client->email)" />
                            <x-input-error :messages="$errors->get('client_email')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="phone" value="Teléfono (opcional)" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $client->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="address" value="Dirección (opcional)" />
                            <textarea id="address" name="address" class="mt-1 block w-full border-gray-300 rounded-md" rows="3">{{ old('address', $client->address) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="notes" value="Notas (opcional)" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 rounded-md" rows="3">{{ old('notes', $client->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Actualizar Cliente</x-primary-button>
                            <a href="{{ route('admin.clients.index') }}" class="text-gray-600">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
