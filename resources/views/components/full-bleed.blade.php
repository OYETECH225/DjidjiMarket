{{-- Breaks a section out of the page's max-w-[1280px] container to span the full
     viewport width, while keeping its content aligned with the rest of the
     page via an inner max-w-[1280px] wrapper. --}}
<section {{ $attributes->only('id')->merge(['class' => 'relative left-1/2 right-1/2 -mx-[50vw] w-screen']) }}>
    <div {{ $attributes->except('id')->merge(['class' => 'mx-auto max-w-[1280px] px-4 md:px-8']) }}>
        {{ $slot }}
    </div>
</section>
