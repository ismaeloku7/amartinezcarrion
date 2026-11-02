@props(['result', 'labels'])
@php
    $areas = [
        'open' => [__('Área abierta'), 'bg-emerald-50', 'bg-emerald-400', __('Lo que tú y los demás veis')],
        'blind' => [__('Área ciega'), 'bg-amber-50', 'bg-amber-400', __('Lo que los demás ven y tú no')],
        'hidden' => [__('Área oculta'), 'bg-sky-50', 'bg-sky-400', __('Lo que tú ves y los demás no')],
        'unknown' => [__('Área desconocida'), 'bg-stone-100', 'bg-stone-300', __('Lo que nadie ha señalado')],
    ];
@endphp
{{-- En móvil, una columna (las palabras necesitan ancho); a partir de sm, la ventana 2×2 clásica. --}}
<div class="grid gap-3 sm:grid-cols-2">
    @foreach ($areas as $key => [$name, $bg, $dot, $hint])
        <div class="rounded-3xl p-4 {{ $bg }}">
            <div class="flex items-center justify-between gap-2">
                <span class="flex items-center gap-2 text-sm font-semibold"><span class="h-2.5 w-2.5 rounded-full {{ $dot }}"></span>{{ $name }}</span>
                <span class="rounded-full bg-white/80 px-2 py-0.5 text-xs font-semibold">{{ count($result['areas'][$key]) }}</span>
            </div>
            <div class="mt-0.5 text-xs text-stone-500">{{ $hint }}</div>
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($result['areas'][$key] as $w)
                    <span class="rounded-full bg-white px-2.5 py-1 text-xs {{ $key === 'unknown' ? 'text-stone-400' : 'text-stone-800' }}">{{ $labels[$w['word']] ?? $w['word'] }}@if ($w['count'] > 0 && $key !== 'hidden') <span class="text-stone-400">×{{ $w['count'] }}</span>@endif</span>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
