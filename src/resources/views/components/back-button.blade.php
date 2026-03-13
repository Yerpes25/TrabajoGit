@props(['href'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-bold text-slate-600 hover:bg-[#62bd19] hover:text-white hover:border-[#62bd19] transition-all group']) }}>
    <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
    {{ $slot->isEmpty() ? 'Volver' : $slot }}
</a>