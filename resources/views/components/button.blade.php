@props(['variant' => 'primary'])

@php
    $classes = match ($variant) {
        'secondary' => 'bg-white text-djidji-green border border-djidji-green hover:bg-djidji-green/5',
        default => 'bg-djidji-green text-white hover:bg-djidji-green-dark',
    };
@endphp

<button {{ $attributes->merge(['class' => "w-full rounded-full px-4 py-2.5 font-semibold transition disabled:opacity-50 $classes"]) }}>
    {{ $slot }}
</button>
