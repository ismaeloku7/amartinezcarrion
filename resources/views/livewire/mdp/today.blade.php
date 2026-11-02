<div class="space-y-4">
    <div class="flex items-center justify-between text-sm text-stone-600">
        <span>{{ $today->translatedFormat('l, j \d\e F') }}</span>
        <span>{{ __('Fase :n', ['n' => $phase]) }} · {{ $enrollment->isFull() ? __('completo') : __('ligero') }} · {{ __(':n días', ['n' => $daysDone]) }}</span>
    </div>

    @if ($pendingMonth)
        <x-ui.alert type="warning" class="flex items-center justify-between">
            <span>{{ __('Han pasado cuatro semanas. Elige el texto del mes.') }}</span>
            <x-ui.button :href="route('mdp.month')" variant="secondary">{{ __('Elegir') }}</x-ui.button>
        </x-ui.alert>
    @endif

    @if ($isRest)
        <x-ui.card :title="__('Hoy es día de descanso')">
            <p class="text-sm text-stone-600">{{ __('Hoy no hay devocional normal. Repasa el día anterior, subraya una palabra de cada texto de la semana y elige el texto de la semana.') }}</p>
            <x-ui.button :href="route('mdp.rest')" class="mt-3">{{ $week->rest_day_done_at ? __('Ver la semana') : __('Empezar') }}</x-ui.button>
        </x-ui.card>
    @elseif (! $day->isCompleted())
        <x-ui.card :title="__('Devocional de hoy')">
            <p class="text-sm text-stone-600">{{ __('Oración, lectura de 15 a 20 minutos, anotación de los textos que resuenan contigo y elección del texto del día.') }}</p>
            <div class="mt-3 flex gap-2">
                <x-ui.button :href="route('mdp.devotional')">{{ $day->started_at ? __('Continuar') : __('Empezar') }}</x-ui.button>
            </div>
        </x-ui.card>
    @else
        <x-ui.card>
            <div class="text-xs uppercase tracking-wide text-stone-500">{{ __('Texto del día') }}</div>
            @if ($dayText)
                <p class="mt-1 text-xl font-medium leading-snug text-red-800">{{ $dayText->body }}</p>
            @endif
            <div class="mt-4">
                <div class="mb-1 text-xs text-stone-500">{{ __('Repasos de hoy') }}: {{ $day->reviews_done }}/3</div>
                <div class="flex items-center gap-2">
                    @for ($i = 1; $i <= 3; $i++)
                        <span class="h-3 w-3 rounded-full {{ $i <= $day->reviews_done ? 'bg-red-700' : 'bg-stone-200' }}"></span>
                    @endfor
                    @if ($day->reviews_done < 3)
                        <x-ui.button wire:click="markReview" variant="secondary" class="ml-auto">{{ __('He repasado') }}</x-ui.button>
                    @endif
                </div>
                <p class="mt-2 text-xs text-stone-500">{{ __('Léelo de nuevo, mejor en voz baja, y recupera la sensación que te produjo al elegirlo.') }}</p>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-ui.button :href="route('mdp.devotional')" variant="ghost">{{ __('Ver la página de hoy') }}</x-ui.button>
                <x-ui.button :href="route('mdp.week')" variant="ghost">{{ __('Ficha de la semana') }}</x-ui.button>
            </div>
        </x-ui.card>
    @endif

    @if ($previous && ! $isRest)
        <x-ui.card>
            <div class="flex items-center justify-between">
                <div class="text-xs uppercase tracking-wide text-stone-500">{{ __('Repaso del día anterior') }} · {{ $previous->date->translatedFormat('j M') }}</div>
                @if ($day->previous_reviewed)<span class="text-xs text-emerald-700">{{ __('Hecho') }}</span>@endif
            </div>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($previous->texts as $t)
                    <li class="{{ $t->is_day_text ? 'font-medium text-red-800' : 'text-stone-700' }}">
                        <x-mdp.text :text="$t" />
                    </li>
                @endforeach
            </ul>
            @if ($previous->prayer_underline)
                <p class="mt-2 border-l-2 border-stone-300 pl-2 text-sm italic text-stone-600">{{ $previous->prayer_underline }}</p>
            @endif
            @unless ($day->previous_reviewed)
                <x-ui.button wire:click="markPreviousReviewed" variant="secondary" class="mt-3">{{ __('Repasado') }}</x-ui.button>
            @endunless
        </x-ui.card>
    @endif
</div>
