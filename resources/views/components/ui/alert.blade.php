@props(['type' => 'info'])
@php $c = ['info' => 'bg-sky-50 text-sky-900', 'success' => 'bg-emerald-50 text-emerald-900', 'warning' => 'bg-amber-50 text-amber-900', 'error' => 'bg-red-50 text-red-900'][$type]; @endphp
<div {{ $attributes->merge(['class' => "rounded-2xl px-4 py-3.5 text-sm leading-relaxed $c"]) }}>{{ $slot }}</div>
