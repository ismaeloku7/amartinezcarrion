<div class="space-y-4">
    @if ($phase === 'review' && $current)
        <div class="flex items-center gap-3">
            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-stone-200"><div class="h-full rounded-full bg-app transition-all" style="width: {{ round(($index + 1) / max(1, $total) * 100) }}%"></div></div>
            <span class="text-xs font-medium tabular-nums text-muted">{{ $index + 1 }} / {{ $total }}</span>
        </div>
        <button type="button" wire:click="flip" class="block w-full text-left active:scale-[0.99]">
            <x-cards.card :card="$current" :side="$flipped ? 'back' : 'front'" />
        </button>
        <p class="text-center text-xs text-muted">{{ __('Toca la ficha para ver el reverso.') }}</p>
        <x-ui.action-bar>
            <x-ui.button wire:click="previousCard" variant="secondary" :disabled="$index === 0" class="w-12 shrink-0 px-0!" aria-label="{{ __('Anterior') }}"><x-ui.icon name="chevron-left" class="h-5 w-5" /></x-ui.button>
            <x-ui.button wire:click="nextCard" class="flex-1" size="lg">{{ $index + 1 < $total ? __('Siguiente') : __('Terminar repaso') }}<x-ui.icon name="arrow-right" class="h-4 w-4" /></x-ui.button>
        </x-ui.action-bar>
    @else
        <div>
            <h2 class="text-lg font-semibold tracking-tight">{{ __('Escoge la ficha de hoy') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('Míralas todas extendidas y elige la que sientas que te puede ir bien hoy. No importa repetir.') }}</p>
        </div>
        @error('choose')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($cards as $c)
                @if ($c->selectable)
                    <button type="button" wire:click="choose({{ $c->id }})" class="text-left transition active:scale-[0.97]"><x-cards.card :card="$c" small /></button>
                @else
                    <div class="relative opacity-60"><x-cards.card :card="$c" small /><span class="absolute inset-x-0 bottom-0 rounded-b-2xl bg-stone-800/70 px-2 py-1 text-center text-[10px] text-white">{{ __('Solo se repasa') }}</span></div>
                @endif
            @endforeach
        </div>
    @endif
</div>
