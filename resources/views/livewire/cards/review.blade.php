<div class="space-y-4">
    @if ($phase === 'review' && $current)
        <div class="text-center text-xs text-stone-500">{{ $index + 1 }} / {{ $total }}</div>
        <button type="button" wire:click="flip" class="block w-full text-left">
            <x-cards.card :card="$current" :side="$flipped ? 'back' : 'front'" />
        </button>
        <p class="text-center text-xs text-stone-500">{{ __('Toca la ficha para ver el reverso.') }}</p>
        <div class="flex items-center justify-between">
            <x-ui.button wire:click="previousCard" variant="ghost" :disabled="$index === 0">{{ __('Anterior') }}</x-ui.button>
            <x-ui.button wire:click="nextCard">{{ $index + 1 < $total ? __('Siguiente') : __('Terminar repaso') }}</x-ui.button>
        </div>
    @else
        <x-ui.card :title="__('Escoge la ficha de hoy')">
            <p class="text-sm text-stone-600">{{ __('Míralas todas extendidas y elige la que sientas que te puede ir bien hoy. No importa repetir.') }}</p>
            @error('choose')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($cards as $c)
                    @if ($c->selectable)
                        <button type="button" wire:click="choose({{ $c->id }})" class="text-left transition hover:scale-[1.02]"><x-cards.card :card="$c" small /></button>
                    @else
                        <div class="relative opacity-60"><x-cards.card :card="$c" small /><span class="absolute inset-x-0 bottom-0 bg-stone-800/70 px-2 py-1 text-center text-[10px] text-white">{{ __('Solo se repasa') }}</span></div>
                    @endif
                @endforeach
            </div>
        </x-ui.card>
    @endif
</div>
