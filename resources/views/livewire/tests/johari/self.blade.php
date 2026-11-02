<div class="space-y-4">
    <div>
        <h2 class="text-lg font-semibold tracking-tight">{{ __('¿Cómo te ves?') }}</h2>
        <p class="mt-1 text-sm text-muted">{{ __('Marca las palabras que mejor te describen. No hay respuestas correctas.') }}</p>
    </div>
    <x-johari.words :words="$words" :selected="$selected" :n="$n" />
    @error('selected')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    <x-ui.action-bar>
        <x-ui.button wire:click="save" :disabled="count($selected) !== $n" block>{{ __('Continuar') }}</x-ui.button>
    </x-ui.action-bar>
</div>
