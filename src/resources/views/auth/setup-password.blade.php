<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Hola :name. Estás a un paso de acceder a tu cuenta. Por favor, introduce la contraseña que deseas utilizar.', ['name' => $user->name]) }}
    </div>

    <form action="{{ route('client.password.store', ['user' => $user->id, 'expires' => request()->query('expires'), 'signature' => request()->query('signature')]) }}" method="POST">
        @csrf

        <div class="mt-4">
            <x-input-label for="password" :value="__('Nueva Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Guardar Contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>