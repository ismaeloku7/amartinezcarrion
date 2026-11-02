<div class="space-y-4">
    <x-cards.card :card="$card" />
    <x-cards.card :card="$card" side="back" />
    @if ($card->section_summary)
        <x-ui.card :title="__('Resumen del apartado')"><p class="whitespace-pre-line text-sm leading-relaxed text-stone-700">{{ $card->section_summary }}</p></x-ui.card>
    @endif
    @unless ($card->selectable)
        <x-ui.alert>{{ __('Esta ficha se repasa con su grupo pero no se escoge como ficha del día.') }}</x-ui.alert>
    @endunless
</div>
