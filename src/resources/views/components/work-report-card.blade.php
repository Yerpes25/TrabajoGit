@props(['client'])

@php
    $activeReport = $client->workReports->first();
    $hasActive = !is_null($activeReport);
@endphp

<div class="card">

    <div>

        <div class="card-header">

            <div>
                <div class="card-title">
                    {{ $client->user->name }}
                </div>

                <div class="card-client">
                    @if($hasActive)
                        {{ $activeReport->title ?? 'Parte #'.$activeReport->id }}
                    @else
                        Nuevo Servicio
                    @endif
                </div>
            </div>

            <span class="flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full
                @if(!$hasActive) bg-blue-50 text-blue-700
                @elseif($activeReport->status === 'in_progress') bg-green-100
                @elseif($activeReport->status === 'paused') bg-yellow-100
                @elseif($activeReport->status === 'finished') bg-blue-100
                @elseif($activeReport->status === 'validated') bg-purple-100
                @endif
            ">

                <span class="material-symbols-outlined text-base">
                    @if(!$hasActive) person
                    @elseif($activeReport->status === 'in_progress') play_arrow
                    @elseif($activeReport->status === 'paused') pause
                    @elseif($activeReport->status === 'finished') check_circle
                    @elseif($activeReport->status === 'validated') verified
                    @endif
                </span>

                @if(!$hasActive)
                    Disponible
                @elseif($activeReport->status === 'in_progress')
                    En progreso
                @elseif($activeReport->status === 'paused')
                    Pausado
                @elseif($activeReport->status === 'finished')
                    Finalizado
                @elseif($activeReport->status === 'validated')
                    Validado
                @endif

            </span>

        </div>


        {{-- CRONÓMETRO O SALDO --}}
        <div class="card-time">

            @if(!$hasActive)
                <span>Saldo disponible</span>
                <strong class="text-green-600">
                    {{ floor($client->profile->balance_seconds / 3600) }}h
                    {{ floor(($client->profile->balance_seconds % 3600) / 60) }}m
                </strong>
            @else
                <span>Tiempo trabajado</span>

                <strong
                    id="crono-{{ $activeReport->id }}"
                    data-total-seconds="{{ $activeReport->total_seconds }}"
                    @if($activeReport->status === 'in_progress')
                        data-running="1"
                    data-started-at="{{ $activeReport->active_started_at }}"
                    @else
                        data-running="0"
                    @endif
                >
                    {{ gmdate('H:i:s', $activeReport->total_seconds) }}
                </strong>
            @endif

        </div>


        {{-- FECHA --}}
        <div class="card-date">
            @if($hasActive)
                Creado: {{ $activeReport->created_at->format('d/m/Y H:i') }}
            @else
                Listo para comenzar a trabajar
            @endif
        </div>

    </div>


    {{-- ACCIONES --}}
    <div class="card-actions">

        @if(!$hasActive)

            {{-- INICIAR TRABAJO AUTOMÁTICAMENTE --}}
            <form action="{{ route('technician.work-reports.store-and-start') }}" method="POST" class="w-full">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="title" value="Servicio técnico - {{ now()->format('d/m/Y') }}">

                <button type="submit" class="btn-primary w-full flex justify-center items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined">play_circle</span>
                    Iniciar Parte
                </button>
            </form>

        @else

            @if($activeReport->status === 'in_progress')

                <form action="{{ route('technician.work-reports.pause', $activeReport) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        Pausar
                    </button>
                </form>

            @elseif($activeReport->status === 'paused')

                <form action="{{ route('technician.work-reports.resume', $activeReport) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">
                        Reanudar
                    </button>
                </form>

                <form action="{{ route('technician.work-reports.finish', $activeReport) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        Finalizar
                    </button>
                </form>

            @elseif($activeReport->status === 'finished')

                <a href="{{ route('technician.work-reports.show', $activeReport) }}"
                   class="btn-secondary">
                    Ver detalles
                </a>

            @elseif($activeReport->status === 'validated')

                <span class="text-sm text-gray-500">
                    Trabajo validado
                </span>

            @endif

        @endif

    </div>

</div>
