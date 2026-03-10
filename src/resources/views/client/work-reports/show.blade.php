<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/client/work-reports') }}" class="p-2 bg-white border border-slate-200 rounded-full shadow-sm hover:bg-[#62bd19] hover:text-white transition-all group">
                        <span class="material-symbols-outlined block group-hover:scale-110 transition-transform">arrow_back</span>
                    </a>
                    <div>
                        <h1 class="text-slate-900 text-3xl font-extrabold tracking-tight italic">
                            Detalle de Intervención
                        </h1>
                        <p class="text-slate-500 font-medium tracking-tight">{{ $workReport->title }}</p>
                    </div>
                </div>
                <div>
                    <span class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest border 
                        @if($workReport->status === 'finished') bg-[#62bd19]/10 text-[#62bd19] border-[#62bd19]/20
                        @elseif($workReport->status === 'validated') bg-[#62bd19] text-white border-[#62bd19]
                        @else bg-slate-100 text-slate-600 border-slate-200 @endif">
                        {{ $workReport->status }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3 text-slate-900">
                            <span class="material-symbols-outlined text-[#62bd19]">info</span>
                            <h2 class="font-bold uppercase tracking-wider text-sm">Información General</h2>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400">
                                        <span class="material-symbols-outlined">domain</span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Cliente</p>
                                        <p class="font-bold text-slate-800">{{ $workReport->client->name ?? 'Nombre Cliente' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-[#62bd19]/10 rounded-xl flex items-center justify-center text-[#62bd19] font-extrabold">
                                        T
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Técnico Asignado</p>
                                        <p class="font-bold text-slate-800">{{ $workReport->technician->name ?? 'Técnico' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2">Descripción del trabajo</p>
                                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 text-slate-700 leading-relaxed font-medium">
                                    {{ $workReport->description ?? 'Sin descripción disponible.' }}
                                </div>
                            </div>
                            <div class="mt-6 flex justify-between items-center text-xs text-slate-400 font-medium italic">
                                <span>Finalizado el {{ $workReport->finished_at ? \Carbon\Carbon::parse($workReport->finished_at)->format('d/m/Y H:i') : '-' }}</span>
                                @if($workReport->status !== 'validated')
                                    <button class="flex items-center gap-2 px-6 py-3 bg-[#62bd19] text-white rounded-xl font-bold hover:bg-[#52a015] transition-all shadow-lg shadow-[#62bd19]/20">
                                        <span class="material-symbols-outlined text-sm">verified</span>
                                        Validar Parte
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3 text-slate-900">
                            <span class="material-symbols-outlined text-[#62bd19]">history_toggle_off</span>
                            <h2 class="font-bold uppercase tracking-wider text-sm">Actividad y Tiempos</h2>
                        </div>
                        <div class="p-8">
                            <div class="relative border-l-2 border-slate-100 ml-3 space-y-10">
                                <div class="relative pl-8">
                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-slate-300 ring-4 ring-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <h3 class="font-bold text-slate-900 uppercase text-xs tracking-tight">START</h3>
                                        <span class="text-xs text-slate-400 font-bold">09 Mar 2026, 12:20:32</span>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium">Registrado por <span class="font-bold text-slate-700">Técnico</span></p>
                                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-slate-100 rounded-lg text-xs text-slate-500 font-bold">
                                        <span class="material-symbols-outlined text-sm">schedule</span> 0.00 h acumuladas
                                    </div>
                                </div>

                                <div class="relative pl-8">
                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-[#62bd19] ring-4 ring-white shadow-sm shadow-[#62bd19]/50"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <h3 class="font-bold text-[#62bd19] uppercase text-xs tracking-tight">FINISH</h3>
                                        <span class="text-xs text-slate-400 font-bold">09 Mar 2026, 12:21:21</span>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium">Registrado por <span class="font-bold text-slate-700">Técnico</span></p>
                                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-[#62bd19]/10 rounded-lg text-xs text-[#62bd19] font-bold">
                                        <span class="material-symbols-outlined text-sm">schedule</span> 0.01 h acumuladas
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-gradient-to-br from-[#62bd19] to-[#52a015] rounded-3xl p-8 text-white shadow-xl shadow-[#62bd19]/20 relative overflow-hidden group">
                        <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-9xl opacity-10 group-hover:scale-110 transition-transform duration-700">timer</span>
                        <p class="text-xs uppercase tracking-[0.2em] font-bold opacity-90 mb-2">Tiempo Total Dedicado</p>
                        <div class="flex items-baseline gap-2">
                            <h2 class="text-5xl font-black">{{ gmdate('H:i', $workReport->total_seconds) }}</h2>
                            <span class="text-xl font-bold opacity-90 uppercase tracking-tight">horas</span>
                        </div>
                        <p class="mt-4 text-xs font-bold bg-black/10 inline-block px-3 py-1 rounded-full backdrop-blur-sm tracking-tight">
                            {{ $workReport->total_seconds }} segundos en total
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3 text-slate-900">
                                <span class="material-symbols-outlined text-[#62bd19]">folder_open</span>
                                <h2 class="font-bold uppercase tracking-wider text-sm">Evidencias</h2>
                            </div>
                            <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-2 py-1 rounded-md">0</span>
                        </div>
                        <div class="p-8 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 mb-4">
                                <span class="material-symbols-outlined text-4xl">inventory_2</span>
                            </div>
                            <p class="text-sm text-slate-400 font-medium">No hay archivos adjuntos</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>