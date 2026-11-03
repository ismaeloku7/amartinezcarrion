<div class="space-y-4">
    @if (! $card)
        <x-ui.alert class="flex items-center justify-between gap-3">
            <span>{{ __('Todavía no has escogido la ficha de hoy.') }}</span>
            <x-ui.button :href="route('cards.review')" variant="secondary" size="sm">{{ __('Escoger') }}</x-ui.button>
        </x-ui.alert>
    @elseif (! $revealed)
        <x-ui.card class="px-6 py-10 text-center">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-app/10 text-app"><x-ui.icon name="sparkles" class="h-8 w-8" /></span>
            <p class="mt-5 text-lg font-semibold leading-snug">{{ __('Antes de mirarla, intenta recordar tu ficha de hoy.') }}</p>
            <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('Recupérala en la memoria por un instante: ese pequeño esfuerzo hace camino en tu cerebro.') }}</p>
            <x-ui.button wire:click="reveal" block class="mt-7">{{ __('Ya la recuerdo, mostrar') }}</x-ui.button>
        </x-ui.card>
    @else
        <x-cards.card :card="$card" />
        <x-cards.card :card="$card" side="back" />
        @if ($card->section_summary)
            <x-ui.card :title="__('Resumen del apartado')"><p class="whitespace-pre-line text-sm leading-relaxed text-stone-700">{{ $card->section_summary }}</p></x-ui.card>
        @endif
        @if ($visualization && $card->number !== 31)
            <x-ui.alert>{{ __('Ficha 31, visualización: imagina cómo sería tu día aplicando plenamente esta ficha.') }}</x-ui.alert>
        @endif
        <x-ui.action-bar>
            <div class="flex w-full items-center justify-between gap-3 rounded-full bg-white py-1.5 pl-5 pr-1.5 shadow-float">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium text-stone-500">{{ __('Repasos') }}</span>
                    <x-ui.dots :done="$day->reviews_done" />
                    @if ($day->reviews_done > 0)
                        <button type="button" wire:click="unmarkReview" wire:confirm="{{ __('¿Quitar el último repaso marcado?') }}" class="text-xs text-stone-500 underline underline-offset-2" title="{{ __('Marcado por error') }}">{{ __('Deshacer') }}</button>
                    @endif
                </div>
                @if ($reviewDue)
                    <x-ui.button wire:click="markReview"><x-ui.icon name="check" class="h-4 w-4" />{{ __('He repasado') }}</x-ui.button>
                @elseif ($nextReviewAt)
                    <span class="pr-3 text-xs text-muted">{{ __('Repaso :n a las :h', ['n' => $day->reviews_done + 1, 'h' => $nextReviewAt->format('H:i')]) }}</span>
                @else
                    <span class="pr-3 text-xs font-medium text-emerald-700">{{ __('Repasos hechos') }}</span>
                @endif
            </div>
        </x-ui.action-bar>
    @endif
</div>
