<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['href']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['href']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-bold text-slate-600 hover:bg-[#62bd19] hover:text-white hover:border-[#62bd19] transition-all group'])); ?>>
    <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
    <?php echo e($slot->isEmpty() ? 'Volver' : $slot); ?>

</a><?php /**PATH /var/www/src/resources/views/components/back-button.blade.php ENDPATH**/ ?>