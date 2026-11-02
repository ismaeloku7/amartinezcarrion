@props(['done' => 0, 'total' => 3])
{{-- Contador de repasos: tres puntos que se rellenan con el color de la app. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }} role="img" aria-label="{{ $done }}/{{ $total }}">
    @for ($i = 1; $i <= $total; $i++)
        <span @class(['h-2.5 rounded-full transition-all', 'w-6 bg-app' => $i <= $done, 'w-2.5 bg-stone-200' => $i > $done])></span>
    @endfor
</div>
