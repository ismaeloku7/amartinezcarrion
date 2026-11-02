<div class="space-y-5">
    <x-ui.card>
        <div class="flex items-baseline justify-between gap-2">
            <h2 class="text-[1.0625rem] font-semibold tracking-tight">{{ __('Mes :n', ['n' => $month->index]) }}</h2>
            <span class="text-xs text-muted">{{ $month->starts_on->translatedFormat('j M') }} – {{ $month->ends_on->translatedFormat('j M Y') }}</span>
        </div>
        @if ($month->monthText)
            <p class="mt-3 font-serif text-[1.375rem] font-medium leading-snug text-green-800">{{ $month->monthText->body }}</p>
        @elseif ($isCurrent)
            <p class="mt-2 text-sm text-muted">{{ __('Al terminar las cuatro semanas podrás elegir el texto del mes entre los textos de cada semana.') }}</p>
        @else
            <p class="mt-2 text-sm text-muted">{{ __('Mira las cuatro fichas juntas y elige el texto con el que más te identificas ahora.') }}</p>
        @endif
        <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
            @foreach ($weeks as $w)
                @php $chosen = $month->month_text_id && $month->month_text_id === $w->week_text_id; @endphp
                <div class="rounded-2xl p-4 {{ $chosen ? 'bg-green-50 ring-2 ring-green-600' : 'bg-amber-50/70' }}">
                    <div class="text-xs font-medium text-stone-500">{{ $w->starts_on->translatedFormat('j M') }}</div>
                    @if ($w->weekText)
                        @if (! $month->month_text_id && ! $isCurrent)
                            <button type="button" wire:click="selectMonthText({{ $w->week_text_id }})" class="mt-1 w-full text-left font-serif text-lg leading-snug active:opacity-70">{{ $w->weekText->body }}</button>
                        @else
                            <p class="mt-1 font-serif text-lg leading-snug">{{ $w->weekText->body }}</p>
                        @endif
                    @else
                        <p class="mt-1 text-sm text-stone-400">{{ __('Sin texto de la semana') }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </x-ui.card>

    @if ($months->count() > 1)
        <section>
            <h2 class="mb-3 text-[1.0625rem] font-semibold tracking-tight">{{ __('Meses anteriores') }}</h2>
            <div class="space-y-2.5">
                @foreach ($months as $m)
                    <a href="{{ route('mdp.month', ['month' => $m->index]) }}" class="flex items-center gap-3 rounded-2xl bg-white p-3.5 shadow-soft active:scale-[0.99]">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-50 text-sm font-semibold text-green-800">{{ $m->index }}</span>
                        <span class="min-w-0 flex-1 truncate font-serif text-green-900">{{ $m->monthText?->body ?? '—' }}</span>
                        <x-ui.icon name="chevron-right" class="h-5 w-5 text-stone-300" />
                    </a>
                @endforeach
            </div>
            <x-ui.button :href="route('mdp.year')" variant="soft" block class="mt-4">{{ __('Galería anual') }}</x-ui.button>
        </section>
    @endif
</div>
