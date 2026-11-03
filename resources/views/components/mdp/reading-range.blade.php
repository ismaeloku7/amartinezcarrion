{{-- Where today's reading starts and ends: the header of each notebook page. --}}
<div {{ $attributes->merge(['class' => 'mt-4']) }}>
    <p class="mb-2 text-xs text-stone-500">{{ __('Anota en qué texto empiezas y en cuál terminas. Es el encabezamiento de la página de hoy y te servirá para localizar cualquier texto más adelante.') }}</p>
    <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="field-label">{{ __('Empiezo en') }}</span><input wire:model.blur="readingFrom" placeholder="Jn 1:1" class="field" autocomplete="off"></label>
        <label class="block"><span class="field-label">{{ __('Termino en') }}</span><input wire:model.blur="readingTo" placeholder="Jn 1:18" class="field" autocomplete="off"></label>
    </div>
</div>
