<div class="space-y-4">
    <div class="flex items-center justify-between">
        <x-ui.button wire:click="previousWeek" variant="ghost">‹</x-ui.button>
        <div class="text-center text-sm font-medium">{{ __('Semana del :a al :b', ['a' => $week->starts_on->translatedFormat('j'), 'b' => $week->ends_on->translatedFormat('j \d\e F')]) }}</div>
        <x-ui.button wire:click="nextWeek" variant="ghost" :disabled="$offset >= 0">›</x-ui.button>
    </div>

    <x-ui.card class="bg-amber-50/40">
        @if ($dayTexts->isEmpty())
            <p class="text-sm text-stone-500">{{ __('Todavía no hay textos esta semana.') }}</p>
        @endif
        <ol class="space-y-3">
            @foreach ($dayTexts as $i => $t)
                <li class="rounded-lg p-2 {{ $week->week_text_id === $t->id ? 'bg-yellow-200' : '' }}">
                    <div class="text-xs text-stone-500">{{ $t->day->date->translatedFormat('D j') }}</div>
                    <p class="text-lg leading-snug {{ $i % 2 === 0 ? 'text-blue-900' : 'text-stone-900' }}"><x-mdp.text :text="$t" :word-underline="($week->word_underlines[(string) $t->id] ?? null)" /></p>
                </li>
            @endforeach
        </ol>
    </x-ui.card>

    @if ($week->week_text_id)
        <x-ui.card :title="__('Texto de la semana')">
            <p class="text-xl font-medium text-yellow-800">{{ $week->weekText->body }}</p>
            <div class="mt-3 flex items-center gap-2 text-sm">
                <span class="text-stone-500">{{ __('Repasos') }}: {{ $week->reviews_done }}/3</span>
                @if ($week->reviews_done < 3 && $offset === 0)<x-ui.button wire:click="markWeekReview" variant="secondary">{{ __('He repasado') }}</x-ui.button>@endif
            </div>
            <div class="mt-4 border-t border-stone-100 pt-3">
                <div class="text-sm font-medium">{{ __('Memorización (opcional)') }}</div>
                <p class="text-xs text-stone-500">{{ __('La «llave» del texto: sus dos, tres o máximo cuatro primeras palabras.') }}</p>
                <div class="mt-2 flex gap-2">
                    <input wire:model="memorizeKey" wire:change="saveMemorizeKey" class="flex-1 rounded-lg border border-stone-300 px-3 py-2 text-sm" placeholder="{{ __('Llave del texto') }}">
                    <x-ui.button wire:click="toggleMemorized" :variant="$week->memorized ? 'primary' : 'secondary'">{{ $week->memorized ? __('Memorizado') : __('Marcar memorizado') }}</x-ui.button>
                </div>
            </div>
        </x-ui.card>
    @elseif ($dayTexts->count() > 1)
        <x-ui.alert>{{ __('El texto de la semana se elige el día de descanso.') }} <a class="underline" href="{{ route('mdp.rest') }}">{{ __('Ir') }}</a></x-ui.alert>
    @endif
</div>
