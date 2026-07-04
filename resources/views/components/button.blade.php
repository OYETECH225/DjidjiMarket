@props(['variant' => 'primary'])

@php
    // Design system: primary CTAs are orange-filled, secondary actions
    // green-filled — always white text on color, never an outline.
    $classes = match ($variant) {
        'secondary' => 'bg-djidji-green text-white hover:bg-djidji-green-dark',
        default => 'bg-djidji-orange text-white hover:brightness-95',
    };
@endphp

<button {{ $attributes->merge(['class' => "w-full rounded-full px-4 py-2.5 font-semibold transition disabled:opacity-50 $classes"]) }}>
    {{ $slot }}
</button>
