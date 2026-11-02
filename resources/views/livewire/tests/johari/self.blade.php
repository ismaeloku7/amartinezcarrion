<div class="space-y-4">
    <ol class="flex gap-1">
        <li class="h-1.5 flex-1 rounded-full bg-app"></li>
        @if ($hasNegative)<li class="h-1.5 flex-1 rounded-full {{ $wordStep === 'negative' ? 'bg-amber-700' : 'bg-stone-200' }}"></li>@endif
    </ol>
    <div>
        <h2 class="text-lg font-semibold tracking-tight">{{ $wordStep === 'negative' ? __('¿Qué rasgos incómodos reconoces en ti?') : __('¿Cómo te ves?') }}</h2>
        <p class="mt-1 text-sm text-muted">{{ $wordStep === 'negative' ? __('Todos tenemos rasgos menos cómodos. Reconocerlos también forma parte del autoconocimiento.') : __('Marca las palabras que mejor te describen. No hay respuestas correctas.') }}</p>
    </div>
    <x-johari.words :words="$words" :selected="$selected" :n="$n" :step="$wordStep" />
    @error('selected')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    <x-johari.steps :step="$wordStep" :has-negative="$hasNegative" :complete="$wordStep === 'negative' ? $this->selectionComplete() : count($selected) === $n" action="save" :action-label="__('Continuar')" />
</div>
