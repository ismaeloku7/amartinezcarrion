<div class="space-y-4">
    <x-ui.card :title="__('¿Cómo te ves?')">
        <p class="mb-3 text-sm text-stone-600">{{ __('Marca las palabras que mejor te describen. No hay respuestas correctas.') }}</p>
        <x-johari.words :words="$words" :selected="$selected" :n="$n" />
        @error('selected')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        <div class="mt-4 flex justify-end"><x-ui.button wire:click="save" :disabled="count($selected) !== $n">{{ __('Continuar') }}</x-ui.button></div>
    </x-ui.card>
</div>
