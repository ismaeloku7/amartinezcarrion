<div class="space-y-4">
    <x-ui.card :title="$wordStep === 'negative' ? __('¿Qué rasgos incómodos reconoces en ti?') : __('¿Cómo te ves?')">
        <ol class="mb-3 flex gap-1">
            <li class="h-1.5 flex-1 rounded-full bg-[color:var(--app-color)]"></li>
            @if ($hasNegative)<li class="h-1.5 flex-1 rounded-full {{ $wordStep === 'negative' ? 'bg-amber-700' : 'bg-stone-200' }}"></li>@endif
        </ol>
        <p class="mb-3 text-sm text-stone-600">{{ $wordStep === 'negative' ? __('Todos tenemos rasgos menos cómodos. Reconocerlos también forma parte del autoconocimiento.') : __('Marca las palabras que mejor te describen. No hay respuestas correctas.') }}</p>
        <x-johari.words :words="$words" :selected="$selected" :n="$n" :step="$wordStep" />
        @error('selected')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        <x-johari.steps :step="$wordStep" :has-negative="$hasNegative" :complete="$wordStep === 'negative' ? $this->selectionComplete() : count($selected) === $n" action="save" :action-label="__('Continuar')" />
    </x-ui.card>
</div>
