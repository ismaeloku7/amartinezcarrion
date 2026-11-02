<div class="space-y-4">
    @if (! $card)
        <x-ui.alert>{{ __('Todavía no has escogido la ficha de hoy.') }} <a class="underline" href="{{ route('cards.review') }}">{{ __('Escoger') }}</a></x-ui.alert>
    @elseif (! $revealed)
        <x-ui.card class="text-center">
            <p class="text-lg font-medium">{{ __('Antes de mirarla, intenta recordar tu ficha de hoy.') }}</p>
            <p class="mt-2 text-sm text-stone-600">{{ __('Recupérala en la memoria por un instante: ese pequeño esfuerzo hace camino en tu cerebro.') }}</p>
            <x-ui.button wire:click="reveal" class="mt-6">{{ __('Ya la recuerdo, mostrar') }}</x-ui.button>
        </x-ui.card>
    @else
        <x-cards.card :card="$card" />
        <x-cards.card :card="$card" side="back" />
        @if ($card->section_summary)
            <x-ui.card :title="__('Resumen del apartado')"><p class="whitespace-pre-line text-sm text-stone-700">{{ $card->section_summary }}</p></x-ui.card>
        @endif
        @if ($visualization && $card->number !== 31)
            <x-ui.alert>{{ __('Ficha 31, visualización: imagina cómo sería tu día aplicando plenamente esta ficha.') }}</x-ui.alert>
        @endif
        <x-ui.card>
            <div class="flex items-center gap-2">
                <span class="text-sm text-stone-500">{{ __('Repasos') }}: {{ $day->reviews_done }}/3</span>
                @if ($day->reviews_done < 3)<x-ui.button wire:click="markReview" variant="secondary" class="ml-auto">{{ __('He repasado') }}</x-ui.button>@endif
            </div>
        </x-ui.card>
    @endif
</div>
