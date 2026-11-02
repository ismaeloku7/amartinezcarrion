@props(['words', 'selected', 'n', 'step' => 'positive'])
<div>
    <div class="sticky top-[calc(3.5rem+env(safe-area-inset-top))] z-10 -mx-4 mb-3 flex items-center justify-between bg-canvas/90 px-4 py-2 text-sm backdrop-blur">
        <span class="text-stone-600">{{ $step === 'negative' ? __('Elige exactamente :n palabras incómodas', ['n' => $n]) : __('Elige exactamente :n palabras positivas', ['n' => $n]) }}</span>
        <span class="rounded-full px-2.5 py-0.5 font-semibold {{ count($selected) === $n ? 'bg-emerald-100 text-emerald-700' : ($step === 'negative' ? 'bg-amber-100 text-amber-800' : 'bg-app/10 text-app') }}">{{ count($selected) }} / {{ $n }}</span>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach ($words as $w)
            @php $on = in_array($w->key, $selected, true); @endphp
            <button type="button" wire:click="toggle('{{ $w->key }}')" title="{{ $w->hint }}" aria-pressed="{{ $on ? 'true' : 'false' }}" class="min-h-10 rounded-full px-4 py-2 text-sm font-medium transition active:scale-95 {{ $on ? ($step === 'negative' ? 'bg-amber-700 text-white shadow-[0_6px_14px_-6px_var(--color-amber-700)]' : 'bg-app text-white shadow-[0_6px_14px_-6px_var(--app-color)]') : 'bg-white text-stone-700 shadow-soft' }} {{ !$on && count($selected) >= $n ? 'opacity-40' : '' }}">{{ $w->label }}</button>
        @endforeach
    </div>
</div>
