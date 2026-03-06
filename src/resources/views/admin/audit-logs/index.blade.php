<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Auditoría') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <x-input-label for="event" value="Evento" />
                            <select id="event" name="event" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Todos</option>
                                @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ $event }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="actor_id" value="Actor" />
                            <select id="actor_id" name="actor_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Todos</option>
                                @foreach($actors as $actor)
                                <option value="{{ $actor->id }}" {{ request('actor_id') == $actor->id ? 'selected' : '' }}>
                                    {{ $actor->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="entity_type" value="Tipo Entidad" />
                            <select id="entity_type" name="entity_type" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Todos</option>
                                @foreach($entityTypes as $type)
                                <option value="{{ $type }}" {{ request('entity_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="date_from" value="Desde" />
                            <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="request('date_from')" />
                        </div>
                        <div>
                            <x-input-label for="date_to" value="Hasta" />
                            <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="request('date_to')" />
                        </div>
                        <div class="md:col-span-5">
                            <x-primary-button>Filtrar</x-primary-button>
                            <a href="{{ route('admin.audit-logs.index') }}" class="text-gray-600 ml-2">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Fecha</th>
                                <th class="px-4 py-2 text-left">Evento</th>
                                <th class="px-4 py-2 text-left">Actor</th>
                                <th class="px-4 py-2 text-left">Entidad</th>
                                <th class="px-4 py-2 text-left">Payload</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $log)
                            <tr>
                                <td class="px-4 py-2">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-2">{{ $log->event }}</td>
                                <td class="px-4 py-2">{{ $log->actor->name ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    @if($log->entity_type && $log->entity_id)
                                    {{ $log->entity_type }} #{{ $log->entity_id }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @if($log->payload)
                                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'payload-{{ $log->id }}')" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1 rounded-md text-sm font-semibold transition-colors">
                                        Ver 
                                    </button>

                                    <x-modal name="payload-{{ $log->id }}" focusable>
                                        <div class="p-6">
                                            <h2 class="text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-3">
                                                Detalles del Evento
                                            </h2>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                                @foreach($log->payload as $key => $value)
                                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 shadow-sm">
                                                    <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                                        {{ str_replace('_', ' ', $key) }}
                                                    </span>
                                                    <span class="block text-sm text-gray-800 break-words">
                                                        @if(is_array($value))
                                                        {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                                        @else
                                                        {{ $value ?: '-' }}
                                                        @endif
                                                    </span>
                                                </div>
                                                @endforeach
                                            </div>

                                            <div class="mt-6 flex justify-end">
                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                    Cerrar
                                                </x-secondary-button>
                                            </div>
                                        </div>
                                    </x-modal>
                                    @else
                                    <span class="text-gray-400 italic text-sm">Sin detalles</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $auditLogs->links() }}
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-start">
                <a href="{{ route('admin.dashboard') }}"
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