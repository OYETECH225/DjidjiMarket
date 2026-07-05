@props(['listing'])

@if ($listing->isOnFlashSale())
    <div {{ $attributes->merge(['class' => '']) }}>
        <span class="inline-block rounded-full bg-djidji-orange px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
            Vente flash
        </span>
        <div class="mt-1 flex items-baseline gap-2">
            <span class="font-semibold text-djidji-orange">{{ number_format($listing->sale_price, 0, ',', ' ') }} {{ $listing->currency }}</span>
            <span class="text-sm text-djidji-text/40 line-through">{{ number_format($listing->price, 0, ',', ' ') }}</span>
        </div>
    </div>
@else
    <span {{ $attributes->merge(['class' => 'font-semibold text-djidji-orange']) }}>
        {{ number_format($listing->price, 0, ',', ' ') }} {{ $listing->currency }}
    </span>
@endif
