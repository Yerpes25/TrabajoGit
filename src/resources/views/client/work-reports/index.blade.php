<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between mb-8">
                <h1 class="text-slate-900 text-3xl font-extrabold tracking-tight flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#62bd19] text-4xl">assignment</span>
                    Mis Partes de Trabajo
                </h1>

                <div class="mt-6 flex justify-start">
                    <x-back-button href="{{ route('client.dashboard') }}">
                        Volver
                    </x-back-button>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

                {{-- TABLA DESKTOP --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">

                        <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Técnico</th>
                            <th class="px-6 py-4">Título</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Tiempo</th>
                            <th class="px-6 py-4">Finalizado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                        @forelse($workReports as $report)

                            <tr class="hover:bg-[#62bd19]/5 transition-colors group">

                                <td class="px-6 py-4 font-bold text-slate-700">
                                    #{{ $report->id }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                    {{ $report->technician->name ?? 'Técnico' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                    {{ $report->title }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border bg-[#62bd19] text-white border-[#62bd19]">
                                        Validado
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-slate-600">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-slate-400 text-sm">timer</span>
                                        {{ gmdate('H:i:s', $report->total_seconds) }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $report->finished_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.work-reports.show',$report) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-[#62bd19] hover:text-white hover:border-[#62bd19] transition-all shadow-sm text-sm font-bold">

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


                {{-- TARJETAS MÓVIL --}}
                <div class="md:hidden px-4 sm:px-6 py-6 space-y-4 text-sm leading-tight">

                    @forelse($workReports as $report)

                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">

                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-xs text-slate-400 font-bold">Parte #{{ $report->id }}</p>
                                    <p class="font-bold text-slate-800">{{ $report->title }}</p>
                                </div>

                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-[#62bd19] text-white">
                                    Validado
                                </span>
                            </div>

                            <div class="space-y-2">

                                <div class="flex justify-between">
                                    <span class="text-slate-400">Técnico</span>
                                    <span class="font-medium text-slate-700">
                                        {{ $report->technician->name }}
                                    </span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-400">Tiempo</span>
                                    <span class="font-medium text-slate-700">
                                        {{ gmdate('H:i:s', $report->total_seconds) }}
                                    </span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-400">Finalizado</span>
                                    <span class="font-medium text-slate-700">
                                        {{ $report->finished_at?->format('d/m/Y H:i') ?? '-' }}
                                    </span>
                                </div>

                            </div>

                            <a href="{{ route('client.work-reports.show',$report) }}"
                               class="mt-4 flex items-center justify-center gap-2 w-full py-2 text-sm font-bold text-[#62bd19] border border-[#62bd19] rounded-lg hover:bg-[#62bd19] hover:text-white transition">

                                <span class="material-symbols-outlined text-sm">visibility</span>
                                Ver detalle
                            </a>

                        </div>

                    @empty

                        <div class="text-center py-10 text-slate-400">
                            No hay partes disponibles
                        </div>

                    @endforelse

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
