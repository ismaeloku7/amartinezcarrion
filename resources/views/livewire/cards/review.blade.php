<div class="space-y-4">
    @if ($previewCard)
        {{-- A closer look at one card, both sides, before choosing it. --}}
        <button type="button" wire:click="flip" class="block w-full text-left active:scale-[0.99]">
            <x-cards.card :card="$previewCard" :side="$flipped ? 'back' : 'front'" />
        </button>
        <p class="text-center text-xs text-muted">{{ __('Toca la ficha para ver el reverso.') }}</p>
        @error('choose')<p class="text-center text-xs text-red-600">{{ $message }}</p>@enderror
        <x-ui.action-bar>
            <x-ui.button wire:click="close" variant="secondary" class="w-12 shrink-0 px-0!" aria-label="{{ __('Volver') }}"><x-ui.icon name="chevron-left" class="h-5 w-5" /></x-ui.button>
            @if ($previewCard->selectable)
                <x-ui.button wire:click="choose({{ $previewCard->id }})" class="flex-1" size="lg"><x-ui.icon name="check" class="h-4 w-4" />{{ __('Escoger esta ficha') }}</x-ui.button>
            @else
                <p class="flex-1 text-center text-xs text-muted">{{ $previewCard->number === 31 ? __('La 31 no se escoge: se aplica junto a la ficha que elijas.') : __('Esta ficha no se escoge.') }}</p>
            @endif
        </x-ui.action-bar>
    @else
        <div>
            <h2 class="text-lg font-semibold tracking-tight">{{ __('Escoge la ficha de hoy') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('Mira las fichas de este grupo y elige una: la que sientas que te puede ir bien hoy. Es la que repasarás tres veces. No importa repetir.') }}</p>
        </div>
        @error('choose')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($cards as $c)
                <button type="button" wire:click="open({{ $c->id }})" @class(['relative text-left transition active:scale-[0.97]', 'opacity-60' => ! $c->selectable])>
                    <x-cards.card :card="$c" small />
                    @if ($day->chosen_card_id === $c->id)
                        <span class="absolute right-2 top-2 grid h-6 w-6 place-items-center rounded-full bg-app text-white shadow"><x-ui.icon name="check" class="h-4 w-4" /></span>
                    @endif
                    @unless ($c->selectable)
                        <span class="absolute inset-x-0 bottom-0 rounded-b-2xl bg-stone-800/70 px-2 py-1 text-center text-[10px] text-white">{{ __('No se escoge') }}</span>
                    @endunless
                </button>
            @endforeach
        </div>
        <p class="text-center text-xs text-muted">{{ __('Toca una ficha para verla por las dos caras y escogerla.') }}</p>
    @endif
</div>
