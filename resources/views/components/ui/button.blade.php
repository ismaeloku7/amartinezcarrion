@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'disabled' => false])
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50';
    $styles = [
        'primary' => 'bg-[color:var(--app-color)] text-white hover:opacity-90 focus:ring-[color:var(--app-color)]',
        'secondary' => 'bg-white text-stone-800 border border-stone-300 hover:bg-stone-100 focus:ring-stone-400',
        'ghost' => 'text-stone-700 hover:bg-stone-100',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    ];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$styles[$variant]]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $base.' '.$styles[$variant]]) }}>{{ $slot }}</button>
@endif
