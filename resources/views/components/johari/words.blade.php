@props(['words', 'selected', 'n'])
<div>
    <div class="mb-3 flex items-center justify-between text-sm">
        <span class="text-stone-600">{{ __('Elige exactamente :n palabras', ['n' => $n]) }}</span>
        <span class="font-medium {{ count($selected) === $n ? 'text-emerald-700' : 'text-stone-700' }}">{{ count($selected) }} / {{ $n }}</span>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach ($words as $w)
            @php $on = in_array($w->key, $selected, true); @endphp
            <button type="button" wire:click="toggle('{{ $w->key }}')" title="{{ $w->hint }}" class="rounded-full border px-3 py-1.5 text-sm transition {{ $on ? 'border-[color:var(--app-color)] bg-[color:var(--app-color)] text-white' : 'border-stone-300 bg-white hover:bg-stone-50' }} {{ !$on && count($selected) >= $n ? 'opacity-50' : '' }}">{{ $w->label }}</button>
        @endforeach
    </div>
</div>
