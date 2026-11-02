<div class="space-y-4">
    @if ($error)
        <x-ui.alert type="warning">{{ $error }}</x-ui.alert>
    @elseif ($done)
        <x-ui.card class="text-center">
            <p class="text-lg font-medium">{{ __('¡Gracias!') }}</p>
            <p class="mt-1 text-sm text-stone-600">{{ __('Tu respuesta es anónima y ya se ha registrado.') }}</p>
            <x-ui.button :href="route('register')" variant="secondary" class="mt-4">{{ __('¿Quieres hacer tu propio test?') }}</x-ui.button>
        </x-ui.card>
    @else
        <x-ui.card>
            <p class="text-stone-700">{{ __(':name te pide que elijas las palabras que, en tu opinión, le describen. Tu respuesta es anónima.', ['name' => $name]) }}</p>
            <div class="mt-4"><x-johari.words :words="$words" :selected="$selected" :n="$n" /></div>
            @error('selected')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            <div class="mt-4 flex justify-end"><x-ui.button wire:click="send" :disabled="count($selected) !== $n">{{ __('Enviar') }}</x-ui.button></div>
        </x-ui.card>
    @endif
</div>
