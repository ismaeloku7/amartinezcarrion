@props(['type' => 'info'])
@php $c = ['info' => 'border-sky-200 bg-sky-50 text-sky-900', 'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900', 'warning' => 'border-amber-200 bg-amber-50 text-amber-900', 'error' => 'border-red-200 bg-red-50 text-red-900'][$type]; @endphp
<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm $c"]) }}>{{ $slot }}</div>
