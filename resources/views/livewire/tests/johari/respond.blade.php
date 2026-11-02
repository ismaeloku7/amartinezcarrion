<div class="space-y-4">
    @if ($error)
        <x-ui.alert type="warning">{{ $error }}</x-ui.alert>
    @elseif ($done)
        <x-ui.card class="py-10 text-center">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-emerald-600"><x-ui.icon name="check" class="h-8 w-8" /></span>
            <p class="mt-5 text-xl font-semibold">{{ __('¡Gracias!') }}</p>
            <p class="mt-1 text-sm text-muted">{{ __('Tu respuesta es anónima y ya se ha registrado.') }}</p>
            <x-ui.button :href="route('register')" variant="soft" block class="mt-6">{{ __('¿Quieres hacer tu propio test?') }}</x-ui.button>
        </x-ui.card>
    @else
        <p class="text-[0.9375rem] leading-relaxed text-stone-700">{{ __(':name te pide que elijas las palabras que, en tu opinión, le describen. Tu respuesta es anónima.', ['name' => $name]) }}</p>
        <x-johari.words :words="$words" :selected="$selected" :n="$n" />
        @error('selected')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        <x-ui.action-bar>
            <x-ui.button wire:click="send" :disabled="count($selected) !== $n" block>{{ __('Enviar') }}</x-ui.button>
        </x-ui.action-bar>
    @endif
</div>
