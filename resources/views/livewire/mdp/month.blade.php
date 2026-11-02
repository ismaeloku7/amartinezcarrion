<div class="space-y-4">
    <x-ui.card :title="__('Mes :n', ['n' => $month->index])">
        <p class="text-xs text-stone-500">{{ $month->starts_on->translatedFormat('j M') }} – {{ $month->ends_on->translatedFormat('j M Y') }}</p>
        @if ($month->monthText)
            <p class="mt-2 text-xl font-medium text-green-800">{{ $month->monthText->body }}</p>
        @elseif ($isCurrent)
            <p class="mt-2 text-sm text-stone-600">{{ __('Al terminar las cuatro semanas podrás elegir el texto del mes entre los textos de cada semana.') }}</p>
        @else
            <p class="mt-2 text-sm text-stone-600">{{ __('Mira las cuatro fichas juntas y elige el texto con el que más te identificas ahora.') }}</p>
        @endif
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach ($weeks as $w)
                <div class="rounded-lg border p-3 {{ $month->month_text_id && $month->month_text_id === $w->week_text_id ? 'border-green-600 bg-green-50' : 'border-stone-200 bg-amber-50/40' }}">
                    <div class="text-xs text-stone-500">{{ $w->starts_on->translatedFormat('j M') }}</div>
                    @if ($w->weekText)
                        @if (! $month->month_text_id && ! $isCurrent)
                            <button type="button" wire:click="selectMonthText({{ $w->week_text_id }})" class="mt-1 text-left text-lg leading-snug">{{ $w->weekText->body }}</button>
                        @else
                            <p class="mt-1 text-lg leading-snug">{{ $w->weekText->body }}</p>
                        @endif
                    @else
                        <p class="mt-1 text-sm text-stone-400">{{ __('Sin texto de la semana') }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </x-ui.card>

    @if ($months->count() > 1)
        <x-ui.card :title="__('Meses anteriores')">
            <ul class="divide-y divide-stone-100 text-sm">
                @foreach ($months as $m)
                    <li class="flex items-center justify-between py-2">
                        <a href="{{ route('mdp.month', ['month' => $m->index]) }}" class="underline">{{ __('Mes :n', ['n' => $m->index]) }}</a>
                        <span class="text-green-800">{{ $m->monthText?->body ?? '—' }}</span>
                    </li>
                @endforeach
            </ul>
            <x-ui.button :href="route('mdp.year')" variant="secondary" class="mt-3">{{ __('Galería anual') }}</x-ui.button>
        </x-ui.card>
    @endif
</div>
