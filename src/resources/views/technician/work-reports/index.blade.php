<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Partes de Trabajo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('technician.work-reports.create') }}" class="btn-primary">
                    Crear Nuevo Parte
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif


            <div class="cards-grid">

                @foreach($workReports as $report)

                    <div class="card">

                        <div>

                            <div class="card-header">

                                <div>
                                    <div class="card-title">
                                        {{ $report->title ?? 'Parte #'.$report->id }}
                                    </div>

                                    <div class="card-client">
                                        {{ $report->client->user->name }}
                                    </div>
                                </div>

                                <span class="card-status
@if($report->status === 'in_progress') status-progress
@elseif($report->status === 'paused') status-paused
@elseif($report->status === 'finished') status-finished
@endif">

{{ $report->status }}

</span>

                            </div>


                            <div class="card-time">
                                <span>Tiempo trabajado</span>
                                <strong id="crono-{{ $report->id }}"
                                        data-total-seconds="{{ $report->total_seconds }}"
                                        @if($report->status === 'in_progress')
                                            data-running="1"
                                        data-started-at="{{ $report->active_started_at }}"
                                        @else
                                            data-running="0"
                                    @endif
                                >
                                    {{ gmdate('H:i:s', $report->total_seconds) }}
                                </strong>
                            </div>


                            <div class="card-date">
                                Creado: {{ $report->created_at->format('d/m/Y H:i') }}
                            </div>

                        </div>


                        <div class="card-actions">

                            @if($report->status === 'paused')
                                <form action="{{ route('technician.work-reports.start', $report) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-primary">Iniciar</button>
                                </form>
                            @endif

                                @if($report->status === 'in_progress')
                                    <form action="{{ route('technician.work-reports.pause', $report) }}" method="POST" class="pararCrono">
                                        @csrf
                                        <button type="submit" class="btn-primary">En ejecución (Pausar)</button>
                                    </form>
                                @endif

                            <a href="{{ route('technician.work-reports.show', $report) }}" class="btn-secondary">
                                Ver
                            </a>


                            @if($report->status !== 'validated')
                                <a href="{{ route('technician.work-reports.edit', $report) }}" class="btn-secondary">
                                    Editar
                                </a>
                            @endif

                        </div>

                    </div>

                @endforeach


                <a href="{{ route('technician.work-reports.create') }}" class="card-create">
                    <div class="create-icon">+</div>
                    Crear nuevo parte
                </a>

            </div>


            <div class="mt-6">
                {{ $workReports->links() }}
            </div>


            <div class="mt-6">
                <a href="{{ route('technician.dashboard') }}" class="btn-back">
                    ← Volver
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const cronos = document.querySelectorAll('[id^="crono-"]');

        cronos.forEach(cronoEl => {

            if(cronoEl.dataset.running !== "1") return;

            let totalSeconds = parseInt(cronoEl.dataset.totalSeconds);

            // Diferencia entre inicio y ahora
            const startedAt = new Date(cronoEl.dataset.startedAt);
            const diffSeconds = Math.floor((Date.now() - startedAt.getTime()) / 1000);
            totalSeconds += diffSeconds;

            const updateCrono = () => {
                let segundos = totalSeconds;
                let horas = Math.floor(segundos/3600);
                segundos %= 3600;
                let minutos = Math.floor(segundos/60);
                segundos %= 60;

                cronoEl.innerText =
                    horas.toString().padStart(2,'0') + ':' +
                    minutos.toString().padStart(2,'0') + ':' +
                    segundos.toString().padStart(2,'0');

                totalSeconds++;
            };

            updateCrono();
            setInterval(updateCrono, 1000);

        });

    });
</script>
