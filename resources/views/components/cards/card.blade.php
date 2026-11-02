@props(['card', 'side' => 'front', 'small' => false])
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border-2 bg-white shadow-sm']) }} style="border-color: {{ $card->color() }}">
    @if ($side === 'front' && $card->imageUrl())
        {{-- The printed card itself: header, title and illustration are part of the image. --}}
        <img src="{{ $card->imageUrl() }}" alt="{{ __('Ficha :n', ['n' => $card->number]) }}: {{ $card->title }}" class="block w-full">
        @if (app()->getLocale() !== 'es')
            <div class="border-t px-3 py-2 {{ $small ? 'text-xs' : 'text-sm' }}" style="border-color: {{ $card->color() }}">
                <div class="font-semibold uppercase leading-tight">{{ $card->title }}</div>
                @if ($card->front_text)<p class="mt-1 text-stone-700">{{ $card->front_text }}</p>@endif
            </div>
        @endif
    @else
        <div class="px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white" style="background: {{ $card->color() }}">{{ __('Ficha :n', ['n' => $card->number]) }}</div>
        @if ($side === 'front')
            <div class="p-3">
                <div class="font-semibold uppercase leading-tight {{ $small ? 'text-xs' : 'text-sm' }}">{{ $card->title }}</div>
                @if ($card->front_text)<p class="mt-2 text-sm text-stone-700">{{ $card->front_text }}</p>@endif
            </div>
        @else
            <div class="p-4 text-sm leading-relaxed text-stone-800">
                <p>{{ $card->back_text }}</p>
                <p class="mt-3 text-right text-[10px] text-stone-400">© Modelo Psicoestructural de Terapia</p>
            </div>
        @endif
    @endif
</div>
