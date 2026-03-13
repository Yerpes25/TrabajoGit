<x-app-layout>
    <div class="pt-6 pb-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                {{-- Lado Izquierdo: Icono y Títulos --}}
                <div class="flex items-center gap-3 sm:gap-4">
                    <span class="material-symbols-outlined text-[#62bd19] text-3xl sm:text-4xl shrink-0">assignment</span>
                    <div class="min-w-0">
                        <h1 class="text-slate-900 text-2xl sm:text-3xl font-extrabold tracking-tight truncate">
                            Detalle de Intervención
                        </h1>
                        <p class="text-slate-500 font-medium tracking-tight truncate">{{ $workReport->title }}</p>
                    </div>
                </div>

                {{-- Lado Derecho: Estado y Botón alineados --}}
                <div class="flex items-center gap-2 sm:gap-3 ml-11 md:ml-0">
                    {{-- Estado Estático (Siempre Validado) --}}
                    <span class="px-4 py-2 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-widest bg-[#62bd19] text-white border border-[#62bd19] shadow-sm whitespace-nowrap">
                    Validado
                    </span>
                </div>
            </div>

            {{-- CONTENIDO PRINCIPAL --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

                {{-- COLUMNA IZQUIERDA (Info + Timeline) --}}
                <div class="lg:col-span-2 space-y-6 lg:space-y-8">

                    {{-- Tarjeta: Información General --}}
                    <div class="bg-white rounded-2xl lg:rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3 text-slate-900">
                            <span class="material-symbols-outlined text-[#62bd19]">info</span>
                            <h2 class="font-bold uppercase tracking-wider text-sm">Información General</h2>
                        </div>
                        <div class="p-4 sm:p-6 lg:p-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 shrink-0">
                                        <span class="material-symbols-outlined">domain</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Cliente</p>
                                        <p class="font-bold text-slate-800 truncate">{{ $workReport->client?->user?->name ?? 'Nombre Cliente' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-[#62bd19]/10 rounded-xl flex items-center justify-center text-[#62bd19] font-extrabold shrink-0">
                                        T
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Técnico Asignado</p>
                                        <p class="font-bold text-slate-800 truncate">{{ $workReport->technician->name ?? 'Técnico' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2">Descripción del trabajo</p>
                                <div class="bg-slate-50 rounded-2xl p-4 sm:p-6 border border-slate-100 text-slate-700 leading-relaxed font-medium text-sm sm:text-base">
                                    {{ $workReport->description ?? 'Sin descripción disponible.' }}
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end text-xs text-slate-400 font-medium italic">
                                <span>Finalizado el {{ $workReport->finished_at ? \Carbon\Carbon::parse($workReport->finished_at)->format('d/m/Y H:i') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA (Tiempos + Archivos) --}}
                <div class="space-y-6 lg:space-y-8">

                    {{-- Widget: Tiempo Total --}}
                    <div class="bg-gradient-to-br from-[#62bd19] to-[#52a015] rounded-2xl lg:rounded-3xl p-6 lg:p-8 text-white shadow-xl shadow-[#62bd19]/20 relative overflow-hidden group">
                        <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-8xl lg:text-9xl opacity-10 group-hover:scale-110 transition-transform duration-700">timer</span>
                        <p class="text-[10px] lg:text-xs uppercase tracking-[0.2em] font-bold opacity-90 mb-2">Tiempo Total</p>
                        <div class="flex items-baseline gap-2">
                            <h2 class="text-4xl lg:text-5xl font-black">{{ gmdate('H:i', $workReport->total_seconds) }}</h2>
                            <span class="text-lg lg:text-xl font-bold opacity-90 uppercase tracking-tight">horas</span>
                        </div>
                        <p class="mt-4 text-[10px] lg:text-xs font-bold bg-black/10 inline-block px-3 py-1 rounded-full backdrop-blur-sm tracking-tight">
                            {{ \Carbon\CarbonInterval::seconds($workReport->total_seconds)->cascade()->forHumans(['short' => true]) }}
                        </p>
                    </div>

                    {{-- Tarjeta: Evidencias --}}
                    <div class="bg-white rounded-2xl lg:rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3 text-slate-900">
                                <span class="material-symbols-outlined text-[#62bd19]">folder_open</span>
                                <h2 class="font-bold uppercase tracking-wider text-sm">Evidencias</h2>
                            </div>
                            <span class="bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-1 rounded-md">
                                {{ $workReport->evidences->count() }}
                            </span>
                        </div>

                        @if($workReport->evidences->count() > 0)
                            <div class="divide-y divide-slate-100">
                                @foreach($workReport->evidences as $evidence)
                                    <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                        <div class="flex items-center gap-3 overflow-hidden min-w-0">
                                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 shrink-0">
                                                <span class="material-symbols-outlined">description</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-slate-700 truncate" title="{{ $evidence->original_name }}">
                                                    {{ $evidence->original_name }}
                                                </p>
                                                <p class="text-xs text-slate-400 truncate">
                                                    {{ number_format($evidence->size_bytes / 1024, 2) }} KB • {{ $evidence->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ route('evidences.download', $evidence) }}" class="ml-3 sm:ml-4 p-2 text-[#62bd19] bg-[#62bd19]/10 rounded-xl hover:bg-[#62bd19] hover:text-white transition-colors shrink-0">
                                            <span class="material-symbols-outlined text-sm block">download</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 sm:p-8 flex flex-col items-center justify-center text-center">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 mb-3 sm:mb-4">
                                    <span class="material-symbols-outlined text-3xl sm:text-4xl">inventory_2</span>
                                </div>
                                <p class="text-sm text-slate-400 font-medium">No hay archivos adjuntos</p>
                            </div>
                        @endif
                    </div>
                    {{-- Tarjeta: Actividad y Tiempos --}}
                    <div class="bg-white rounded-2xl lg:rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3 text-slate-900">
                            <span class="material-symbols-outlined text-[#62bd19]">history_toggle_off</span>
                            <h2 class="font-bold uppercase tracking-wider text-sm">Actividad y Tiempos</h2>
                        </div>

                        <div class="p-4 sm:p-6 lg:p-8">
                            <div class="pl-2 sm:pl-4">
                                @forelse($workReport->events as $event)
                                    <div class="relative flex gap-4 sm:gap-6 @if(!$loop->last) pb-8 @endif">

                                        @if(!$loop->last)
                                            <div class="absolute left-[7px] top-5 bottom-0 w-[2px] bg-slate-200"></div>
                                        @endif

                                        <div class="relative w-4 h-4 mt-0.5 rounded-full ring-4 ring-white shadow-sm z-10 shrink-0
                                        @if(in_array($event->type, ['start', 'started', 'resume', 'resumed'])) bg-red-500 shadow-red-500/50
                                        @elseif(in_array($event->type, ['pause', 'paused'])) bg-orange-500 shadow-orange-500/50
                                        @elseif(in_array($event->type, ['finish', 'finished'])) bg-blue-500 shadow-blue-500/50
                                        @elseif(in_array($event->type, ['validate', 'validated'])) bg-[#62bd19] shadow-[#62bd19]/50
                                        @else bg-slate-400 @endif">
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-1 sm:mb-2 gap-1">
                                                <h3 class="font-bold uppercase text-xs tracking-tight
                                                @if(in_array($event->type, ['start', 'started', 'resume', 'resumed'])) text-red-500
                                                @elseif(in_array($event->type, ['pause', 'paused'])) text-orange-500
                                                @elseif(in_array($event->type, ['finish', 'finished'])) text-blue-500
                                                @elseif(in_array($event->type, ['validate', 'validated'])) text-[#62bd19]
                                                @else text-slate-700 @endif">

                                                    {{ strtoupper(match(true) {
                                                        in_array($event->type, ['start', 'started']) => 'Iniciado',
                                                        in_array($event->type, ['pause', 'paused']) => 'Pausado',
                                                        in_array($event->type, ['finish', 'finished']) => 'Finalizado',
                                                        in_array($event->type, ['validate', 'validated']) => 'Validado',
                                                        in_array($event->type, ['resume', 'resumed']) => $loop->first ? 'Empezado' : 'Reanudado',
                                                        default => $event->type
                                                    }) }}
                                                </h3>
                                                <span class="text-[10px] sm:text-xs text-slate-400 font-bold">{{ \Carbon\Carbon::parse($event->occurred_at)->format('d M Y, H:i:s') }}</span>
                                            </div>

                                            <p class="text-xs sm:text-sm text-slate-500 font-medium mb-3 sm:mb-5 truncate">
                                                Registrado por <span class="font-bold text-slate-700">{{ $event->creator->name ?? 'Técnico' }}</span>
                                            </p>

                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-xs text-slate-500 font-bold">
                                                <span class="material-symbols-outlined text-sm">schedule</span>
                                                {{ number_format($event->elapsed_seconds_after / 3600, 2) }} h acumuladas
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex items-center gap-3 text-slate-400 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                        <span class="material-symbols-outlined">history</span>
                                        <p class="text-sm font-medium">No hay actividad registrada en este parte.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
