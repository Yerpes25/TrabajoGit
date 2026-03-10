<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-slate-900 text-3xl font-extrabold tracking-tight flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#62bd19] text-4xl">assignment</span>
                        Mis Partes de Trabajo
                    </h1>
                </div>
                <a href="{{ url('/client') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"> <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Volver al Panel
                </a>
            </div>

            <div class="bg-blue-50 border border-blue-100 text-blue-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3 shadow-sm">
                <span class="material-symbols-outlined">info</span>
                <span class="font-medium text-sm">Nota: Solo se muestran partes finalizados y validados.</span>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Técnico</th>
                                <th class="px-6 py-4">Título</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4">Tiempo (hh:mm:ss)</th>
                                <th class="px-6 py-4">Finalizado</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">

                            {{-- ⚠️ ¡ATENCIÓN! Revisa que tu variable se llame $workReports en el controlador. Si se llama distinto (ej. $reports), cámbialo aquí abajo --}}
                            @forelse($workReports as $report)
                            <tr class="hover:bg-[#62bd19]/5 transition-colors group">
                                <td class="px-6 py-4 font-bold text-slate-700">#{{ $report->id }}</td>

                                {{-- Ajusta estas variables según cómo las llames en tu base de datos --}}
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $report->technician->name ?? 'Técnico' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ $report->title }}</td>

                                <td class="px-6 py-4">
                                    <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider 
                                            @if($report->status === 'finished') bg-blue-100 text-blue-700 border border-blue-200
                                            @elseif($report->status === 'validated') bg-[#62bd19]/10 text-[#62bd19] border border-[#62bd19]/20
                                            @else bg-slate-100 text-slate-600 border border-slate-200
                                            @endif">
                                        {{ $report->status }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-slate-600">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-slate-400 text-sm">timer</span>
                                        {{-- Cambia esto si tienes otra forma de mostrar el tiempo --}}
                                        {{ gmdate('H:i:s', $report->total_seconds) }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{-- Cambia esto según el nombre de tu campo de fecha --}}
                                    {{ $report->finished_at ? \Carbon\Carbon::parse($report->finished_at)->format('d/m/Y H:i') : '-' }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.work-reports.show', $report->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-[#62bd19] hover:text-white hover:border-[#62bd19] transition-all shadow-sm text-sm font-bold">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        Ver Detalle
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-4xl mb-3 text-slate-300">inbox</span>
                                        <p class="font-medium">No hay partes de trabajo disponibles.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>