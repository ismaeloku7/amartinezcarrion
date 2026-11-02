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
            <ol class="my-3 flex gap-1">
                <li class="h-1.5 flex-1 rounded-full bg-[color:var(--app-color)]"></li>
                @if ($hasNegative)<li class="h-1.5 flex-1 rounded-full {{ $wordStep === 'negative' ? 'bg-amber-700' : 'bg-stone-200' }}"></li>@endif
            </ol>
            @if ($wordStep === 'negative')
                <p class="mb-3 text-sm text-stone-600">{{ __('Ahora los rasgos menos cómodos. Responde con sinceridad: es anónimo y le ayudará.') }}</p>
            @endif
            <x-johari.words :words="$words" :selected="$selected" :n="$n" :step="$wordStep" />
            @error('selected')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            <x-johari.steps :step="$wordStep" :has-negative="$hasNegative" :complete="$wordStep === 'negative' ? $this->selectionComplete() : count($selected) === $n" action="send" :action-label="__('Enviar')" />
        </x-ui.card>
    @endif
</div>
