@props(['title' => null])
<section {{ $attributes->merge(['class' => 'rounded-xl border border-stone-200 bg-white p-4 shadow-sm']) }}>
    @if ($title)
        <h2 class="mb-3 text-lg font-semibold">{{ $title }}</h2>
    @endif
    {{ $slot }}
</section>
