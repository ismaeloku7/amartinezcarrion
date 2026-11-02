@props(['result', 'labels'])
@php
    $areas = [
        'open' => [__('Área abierta'), 'bg-emerald-50 border-emerald-300', __('Lo que tú y los demás veis')],
        'blind' => [__('Área ciega'), 'bg-amber-50 border-amber-300', __('Lo que los demás ven y tú no')],
        'hidden' => [__('Área oculta'), 'bg-sky-50 border-sky-300', __('Lo que tú ves y los demás no')],
        'unknown' => [__('Área desconocida'), 'bg-stone-100 border-stone-300', __('Lo que nadie ha señalado')],
    ];
@endphp
<div class="grid grid-cols-2 gap-2">
    @foreach ($areas as $key => [$name, $cls, $hint])
        <div class="rounded-lg border p-3 {{ $cls }}">
            <div class="text-xs font-semibold uppercase tracking-wide">{{ $name }} <span class="font-normal text-stone-500">({{ count($result['areas'][$key]) }})</span></div>
            <div class="text-[11px] text-stone-500">{{ $hint }}</div>
            <div class="mt-2 flex flex-wrap gap-1">
                @foreach ($result['areas'][$key] as $w)
                    <span class="rounded-full bg-white/80 px-2 py-0.5 text-xs {{ $key === 'unknown' ? 'text-stone-400' : '' }}">{{ $labels[$w['word']] ?? $w['word'] }}@if ($w['count'] > 0 && $key !== 'hidden') <span class="text-stone-400">×{{ $w['count'] }}</span>@endif</span>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
