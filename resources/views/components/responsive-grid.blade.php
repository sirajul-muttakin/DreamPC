@props([
    'cols' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
    'gap' => 'gap-6'
])

<div {{ $attributes->merge(['class' => "grid {$cols} {$gap} w-full"]) }}>
    {{ $slot }}
</div>
