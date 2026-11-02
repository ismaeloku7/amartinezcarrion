@props(['title' => null, 'eyebrow' => null])
<section {{ $attributes->merge(['class' => 'rounded-3xl bg-white p-5 shadow-soft']) }}>
    @if ($eyebrow)
        <div class="mb-1 text-xs font-semibold uppercase tracking-wider text-muted">{{ $eyebrow }}</div>
    @endif
    @if ($title)
        <h2 class="mb-3 text-[1.0625rem] font-semibold tracking-tight">{{ $title }}</h2>
    @endif
    {{ $slot }}
</section>
