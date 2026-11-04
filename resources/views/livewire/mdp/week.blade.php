<div class="space-y-5">
    <div class="flex items-center justify-between gap-2 rounded-full bg-white p-1.5 shadow-soft">
        <button type="button" wire:click="previousWeek" class="grid h-10 w-10 place-items-center rounded-full text-stone-600 active:bg-fill" aria-label="{{ __('Semana anterior') }}"><x-ui.icon name="chevron-left" class="h-5 w-5" /></button>
        <div class="text-center text-sm font-semibold">{{ __('Semana del :a al :b', ['a' => $week->starts_on->translatedFormat('j'), 'b' => $week->ends_on->translatedFormat('j \d\e F')]) }}</div>
        <button type="button" wire:click="nextWeek" @disabled($offset >= 0) class="grid h-10 w-10 place-items-center rounded-full text-stone-600 active:bg-fill disabled:opacity-30" aria-label="{{ __('Semana siguiente') }}"><x-ui.icon name="chevron-right" class="h-5 w-5" /></button>
    </div>

    <x-ui.card class="bg-amber-50/60">
        @if ($dayTexts->isEmpty())
            <p class="text-sm text-muted">{{ __('Todavía no hay textos esta semana.') }}</p>
        @endif
        <ol class="relative space-y-1">
            @foreach ($dayTexts as $i => $t)
                <li class="flex gap-3 rounded-2xl p-2.5 {{ $week->week_text_id === $t->id ? 'bg-yellow-200/80' : '' }}">
                    <span class="w-9 shrink-0 pt-1 text-center text-[0.6875rem] font-semibold uppercase leading-tight text-stone-500">{{ $t->day->date->translatedFormat('D') }}<span class="block text-base text-stone-700">{{ $t->day->date->format('j') }}</span></span>
                    <p class="font-serif text-lg leading-snug {{ $i % 2 === 0 ? 'text-blue-900' : 'text-stone-900' }}"><x-mdp.text :text="$t" :word-underline="($week->word_underlines[(string) $t->id] ?? null)" /></p>
                </li>
            @endforeach
        </ol>
    </x-ui.card>

    @if ($week->week_text_id)
        <x-ui.card :eyebrow="__('Texto de la semana')">
            <p class="font-serif text-[1.375rem] font-medium leading-snug text-yellow-800">{{ $week->weekText->body }}</p>
            <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl bg-fill p-3.5">
                <div>
                    <div class="text-xs font-medium text-stone-500">{{ __('Repasos') }}</div>
                    <x-ui.dots :done="$week->reviews_done" class="mt-2" />
                </div>
                @if ($week->reviews_done < 3 && $offset === 0)<x-ui.button wire:click="markWeekReview" size="sm"><x-ui.icon name="check" class="h-4 w-4" />{{ __('He repasado') }}</x-ui.button>@endif
            </div>
        </x-ui.card>
    @elseif ($isRestDay && $dayTexts->isNotEmpty())
        <x-ui.alert class="flex items-center justify-between gap-3">
            <span>{{ __('Hoy es tu día de descanso: toca elegir el texto de la semana.') }}</span>
            <x-ui.button :href="route('mdp.rest')" variant="secondary" size="sm">{{ __('Elegir') }}</x-ui.button>
        </x-ui.alert>
    @elseif ($offset === 0 && $dayTexts->isNotEmpty())
        <x-ui.alert>{{ __('El texto de la semana se elige el día de descanso, el :d.', ['d' => $week->ends_on->translatedFormat('l j')]) }}</x-ui.alert>
    @endif

    <div class="grid grid-cols-2 gap-2">
        <x-ui.button :href="route('mdp.month')" variant="soft" size="sm">{{ __('Texto del mes') }}</x-ui.button>
        <x-ui.button :href="route('mdp.year')" variant="soft" size="sm">{{ __('Galería anual') }}</x-ui.button>
    </div>
</div>
