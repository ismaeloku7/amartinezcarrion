@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'disabled' => false, 'block' => false, 'size' => 'md'])
@php
    $base = 'inline-flex select-none items-center justify-center gap-2 rounded-full font-semibold transition active:scale-[0.98] focus:outline-none focus-visible:ring-4 disabled:pointer-events-none disabled:opacity-40';
    $sizes = [
        'sm' => 'h-9 px-4 text-sm',
        'md' => 'h-11 px-5 text-[0.9375rem]',
        'lg' => 'h-13 px-6 text-base',
    ];
    $styles = [
        'primary' => 'bg-app text-white shadow-[0_8px_20px_-8px_var(--app-color)] hover:brightness-110 focus-visible:ring-app/25',
        'soft' => 'bg-app/10 text-app hover:bg-app/15 focus-visible:ring-app/20',
        'secondary' => 'bg-white text-ink ring-1 ring-stone-200 hover:bg-stone-50 focus-visible:ring-stone-300',
        'ghost' => 'text-stone-600 hover:bg-stone-900/5 focus-visible:ring-stone-200',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-200',
    ];
    $classes = implode(' ', [$base, $sizes[$block && $size === 'md' ? 'lg' : $size], $styles[$variant], $block ? 'w-full' : '']);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
