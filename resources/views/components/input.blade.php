@props(['label' => null, 'error' => null])

<div>
    @if ($label)
        <label {{ $attributes->only('id')->merge(['class' => 'mb-1 block text-sm font-medium text-djidji-text']) }}>
            {{ $label }}
        </label>
    @endif

    <input {{ $attributes->except(['label', 'error'])->merge([
        'class' => 'w-full rounded-xl border px-3 py-2 text-djidji-text focus:outline-none focus:ring-2 focus:ring-djidji-green '
            . ($error ? 'border-djidji-error' : 'border-djidji-outline'),
    ]) }}>

    @if ($error)
        <p class="mt-1 text-sm text-djidji-error">{{ $error }}</p>
    @endif
</div>
